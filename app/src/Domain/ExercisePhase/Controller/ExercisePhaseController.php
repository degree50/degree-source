<?php

namespace App\Domain\ExercisePhase\Controller;

use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhase\Form\MaterialPhaseFormType;
use App\Domain\ExercisePhase\Form\ReflexionPhaseFormType;
use App\Domain\ExercisePhase\Form\VideoAnalysisPhaseFormFormType;
use App\Domain\ExercisePhase\Form\VideoCutPhaseFormFormType;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Model\MaterialPhase;
use App\Domain\ExercisePhase\Model\ReflexionPhase;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder;
use App\Domain\Solution\Model\Solution;
use App\Domain\Solution\Service\SolutionService;
use App\Domain\User\Model\User;
use App\LiveSync\LiveSyncService;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\ExercisePhaseTeamVoter;
use App\Security\Voter\ExercisePhaseVoter;
use App\Security\Voter\ExerciseVoter;
use App\Security\Voter\SolutionVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class ExercisePhaseController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface         $translator,
        private readonly LiveSyncService             $liveSyncService,
        private readonly RouterInterface             $router,
        private readonly ExercisePhaseRepository     $exercisePhaseRepository,
        private readonly ExercisePhaseService        $exercisePhaseService,
        private readonly ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private readonly SolutionService             $solutionService,
        private readonly ExerciseService             $exerciseService,
        private readonly EntityManagerInterface      $entityManager
    )
    {
    }

    #[IsGranted(ExercisePhaseTeamVoter::SHOW_SOLUTION, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/show/{id}/{team_id}", name: "exercise-phase__show")]
    public function show(
        ExercisePhase     $exercisePhase = null,
        #[MapEntity(id: "team_id")]
        ExercisePhaseTeam $exercisePhaseTeam = null,
    ): Response
    {
        if (!($exercisePhase && $exercisePhaseTeam)) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->exercisePhaseService->openPhase($exercisePhaseTeam);

        if ($exercisePhase->getType() === ExercisePhaseType::REFLEXION) {
            return $this->reflectInPhase($exercisePhase, $exercisePhaseTeam);
        } else {
            return $this->workOnPhase($exercisePhase, $exercisePhaseTeam);
        }
    }

    #[IsGranted(ExercisePhaseVoter::TEST, subject: "exercisePhase")]
    #[Route("/exercise-phase/test/{id}/{team_id}", name: "exercise-phase__test")]
    public function test(
        ExercisePhase     $exercisePhase = null,
        #[MapEntity(id: "team_id")]
        ExercisePhaseTeam $exercisePhaseTeam = null,
    ): Response
    {
        if (!$exercisePhase || !$exercisePhaseTeam) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->exercisePhaseService->openPhase($exercisePhaseTeam);

        if ($exercisePhase->getType() === ExercisePhaseType::REFLEXION) {
            return $this->reflectInPhase($exercisePhase, $exercisePhaseTeam, true);
        } else {
            return $this->workOnPhase($exercisePhase, $exercisePhaseTeam, true);
        }
    }

    #[IsGranted(ExercisePhaseVoter::TEST, subject: "exercisePhase")]
    #[Route("/exercise-phase/test/{id}/{team_id}/reset", name: "exercise-phase__reset-test")]
    public function resetTest(
        ExercisePhase     $exercisePhase = null,
        #[MapEntity(id: "team_id")]
        ExercisePhaseTeam $exercisePhaseTeam = null,
    ): Response
    {
        if (!$exercisePhase || !$exercisePhaseTeam) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($exercisePhaseTeam);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('exercisePhase.resetTest.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute(
            'exercise__show-test-phase',
            [
                'id' => $exercisePhase->getBelongsToExercise()->getId(),
                'phaseId' => $exercisePhase->getId()
            ]
        );
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit/phase/new", name: "exercise-phase__new")]
    public function new(Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $types = [];

        foreach (ExercisePhaseType::getPossibleValues() as $type) {
            $types[] = [
                'id' => $type,
                'iconClass' => $this->translator->trans('exercisePhase.types.' . $type . '.iconClass', [], 'DegreeBase'),
                'label' => $this->translator->trans('exercisePhase.types.' . $type . '.label', [], 'DegreeBase')
            ];
        }

        return $this->render('ExercisePhase/ChooseType.html.twig', [
            'types' => $types,
            'exercise' => $exercise,
        ]);
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit/phase/type", name: "exercise-phase__set-type")]
    public function initializePhaseByType(Request $request, Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $type = $request->query->get('type');

        // Initialize phase by type (mandatory)
        $exercisePhase = match (ExercisePhaseType::tryFrom($type)) {
            ExercisePhaseType::VIDEO_ANALYSIS => new VideoAnalysisPhase(),
            ExercisePhaseType::VIDEO_CUT => new VideoCutPhase(),
            ExercisePhaseType::REFLEXION => new ReflexionPhase(),
            ExercisePhaseType::MATERIAL => new MaterialPhase(),
            default => throw new InvalidArgumentException(
                "ExercisePhaseType has to be one of ["
                . implode(', ', ExercisePhaseType::getPossibleValues()) .
                "]! '$type' given."
            ),
        };

        $exercisePhase->setBelongsToExercise($exercise);

        return $this->persistPhaseAndRedirectToEdit($exercise, $exercisePhase);
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit/phase/{phase_id}/edit", name: "exercise-phase__edit")]
    public function edit(
        Request       $request,
        Exercise      $exercise = null,
        #[MapEntity(id: "phase_id")]
        ExercisePhase $exercisePhase = null,
    ): Response
    {
        if (!$exercise || !$exercisePhase) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $form = $this->getPhaseForm($exercisePhase);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handlePhaseEditFormSubmit($form, $exercise);
        }

        return $this->render('ExercisePhase/Edit.html.twig', [
            'exercise' => $exercise,
            'exercisePhase' => $exercisePhase,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(SolutionVoter::REVIEW_SOLUTION, subject: "solution")]
    #[Route("/exercise-phase-solution/finish-review/{id}", name: "exercise-phase-solution__finish-review", methods: ["POST"])]
    public function finishReview(Solution $solution = null): Response
    {
        if (!$solution) {
            return new Response('not allowed', 403);
        }

        try {
            $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findBySolution($solution);
            $this->exercisePhaseService->finishReview($exercisePhaseTeam);

            return new Response('Successfully finished review', 200);
        } catch (Exception $error) {
            return new Response($error, 500);
        }
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit/phase/{phase_id}/change-sorting", name: "exercise-phase__change-sorting")]
    public function changeSorting(
        Request       $request,
        Exercise      $exercise = null,
        #[MapEntity(id: "phase_id")]
        ExercisePhase $exercisePhase = null,
    ): Response
    {
        if (!$exercise || !$exercisePhase) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $sortUp = $request->query->get('sortUp', false);
        $currentSortIndex = $exercisePhase->getSorting();

        if ($sortUp) {
            $exercisePhaseAtNewSortIndex = $this->exercisePhaseRepository->findExercisePhasesLesserThen($currentSortIndex, $exercise);
        } else {
            $exercisePhaseAtNewSortIndex = $this->exercisePhaseRepository->findExercisePhasesLargerThen($currentSortIndex, $exercise);
        }

        if ($exercisePhaseAtNewSortIndex === $exercisePhase->getDependsOnExercisePhase()) {
            // NOTE:
            // Technically this case should never occur, because we check this condition inside the
            // Twig template as well and disable the sort button, if it is true
            throw new Exception("A phase can't be sorted before the phase it depends on!");
        }

        $exercisePhase->setSorting($exercisePhaseAtNewSortIndex->getSorting());
        $exercisePhaseAtNewSortIndex->setSorting($currentSortIndex);

        $this->entityManager->persist($exercisePhase);
        $this->entityManager->persist($exercisePhaseAtNewSortIndex);
        $this->entityManager->flush();

        return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
    }

    #[IsGranted(ExercisePhaseVoter::DELETE, subject: "exercisePhase")]
    #[Route("/exercise/{id}/edit/phase/{phase_id}/delete", name: "exercise-phase__delete")]
    public function delete(
        Exercise      $exercise = null,
        #[MapEntity(id: "phase_id")]
        ExercisePhase $exercisePhase = null,
    ): Response
    {
        if (!$exercise || !$exercisePhase) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->exercisePhaseService->deleteExercisePhase($exercisePhase);

        // Update sorting
        /** @var ExercisePhase[] $remainingPhases */
        $remainingPhases = $this->exercisePhaseRepository->findAllSortedBySorting($exercise);

        foreach ($remainingPhases as $index => $phase) {
            $phase->setSorting($index);
            $this->entityManager->persist($phase);
        }

        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('exercisePhase.delete.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
    }

    #[IsGranted(ExercisePhaseTeamVoter::SHOW_SOLUTION, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/show-others/{id}/{team_id}", name: "exercise-phase__show-other-solution")]
    public function showOtherStudentsSolution(
        ExercisePhase     $exercisePhase = null,
        #[MapEntity(id: "team_id")]
        ExercisePhaseTeam $exercisePhaseTeam = null,
    ): Response
    {
        if (!$exercisePhase || !$exercisePhaseTeam) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = $this->getUser();

        // config for the ui to render the react components
        $config = $this->getConfigWithSolutionApiEndpoints($exercisePhase, $exercisePhaseTeam);

        $response = new Response();
        $response->headers->setCookie($this->liveSyncService->getSubscriberJwtCookie($user, $exercisePhaseTeam));

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilder(
            $clientSideSolutionDataBuilder,
            $exercisePhaseTeam,
            $exercisePhase
        );

        $exercise = $exercisePhase->getBelongsToExercise();

        return $this->render('ExercisePhase/ShowSolution.html.twig', [
            'config' => $config,
            'data' => $clientSideSolutionDataBuilder,
            'liveSyncConfig' => $this->liveSyncService->getClientSideLiveSyncConfig($exercisePhaseTeam),
            'exercisePhase' => $exercisePhase,
            'exercise' => $exercise,
            'phases' => $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user),
            'exercisePhaseTeam' => $exercisePhaseTeam,
            'currentEditor' => null
        ], $response);
    }

    private function reflectInPhase(
        ExercisePhase     $reflexionPhase,
        ExercisePhaseTeam $exercisePhaseTeam,
        bool              $testMode = false
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $phaseReflexionDependsOn = $reflexionPhase->getDependsOnExercisePhase();
        $exercise = $reflexionPhase->getBelongsToExercise();

        $teams = [];
        if ($testMode && $exercisePhaseTeam->getSolution() !== null) {
            $teams[] = $exercisePhaseTeam;
        } else {
            $teams = $this->exercisePhaseTeamRepository->findAllByPhaseExcludingTests($phaseReflexionDependsOn);
            // HOTFIX: It's possible to create a team without solutions right now, e.g. when a user creates a team in a
            // group phase and does not start the phase for this group at least once.
            // This leads to an 500 Error down the line, when the cut video of the solution is accessed. (can't get a
            // cut video from a solution that does not exist).
            // This is a quick fix and should be addressed with more insight later.
            $teams = array_filter($teams, fn($team) => $team->getSolution() !== null);
        }

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilderForSolutionView(
            $clientSideSolutionDataBuilder,
            $teams
        );

        $configOfDependingPhase = $this->exercisePhaseService->getConfig($phaseReflexionDependsOn, $user);

        return $this->render('ExercisePhase/Show.html.twig', [
            'config' => array_merge($this->exercisePhaseService->getConfig($reflexionPhase, $user), [
                'type' => $configOfDependingPhase['type'],
                'videos' => $configOfDependingPhase['videos'],
            ]),
            'data' => $clientSideSolutionDataBuilder,
            'exercise' => $exercise,
            'phases' => $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user),
            'exercisePhaseTeam' => $exercisePhaseTeam,
            'exercisePhase' => $reflexionPhase,
            'testMode' => $testMode,
        ]);
    }

    private function workOnPhase(
        ExercisePhase     $exercisePhase,
        ExercisePhaseTeam $exercisePhaseTeam,
        bool              $testMode = false
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // config for the ui to render the react components
        $config = $this->getConfigWithSolutionApiEndpoints($exercisePhase, $exercisePhaseTeam);

        $this->initiateExercisePhaseTeamWithSolution($exercisePhase, $exercisePhaseTeam, $user);

        $response = new Response();
        $response->headers->setCookie($this->liveSyncService->getSubscriberJwtCookie($user, $exercisePhaseTeam));

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilder(
            $clientSideSolutionDataBuilder,
            $exercisePhaseTeam,
            $exercisePhase
        );

        $exercise = $exercisePhase->getBelongsToExercise();

        return $this->render('ExercisePhase/Show.html.twig', [
            'config' => $config,
            'data' => $clientSideSolutionDataBuilder,
            'liveSyncConfig' => $this->liveSyncService->getClientSideLiveSyncConfig($exercisePhaseTeam),
            'exercisePhase' => $exercisePhase,
            'exercise' => $exercise,
            'phases' => $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user),
            'exercisePhaseTeam' => $exercisePhaseTeam,
            'currentEditor' => $exercisePhaseTeam->getCurrentEditor()->getId(),
            'testMode' => $testMode,
        ], $response);
    }

    private function getConfigWithSolutionApiEndpoints(
        ExercisePhase     $exercisePhase,
        ExercisePhaseTeam $exercisePhaseTeam,
        ?bool             $readOnly = false
    ): array
    {
        /** @var User $user */
        $user = $this->getUser();
        $config = $this->exercisePhaseService->getConfig($exercisePhase, $user, $readOnly);
        $config['apiEndpoints'] = [
            'updateSolution' => $this->router->generate('exercise-phase-team__update-solution', [
                'id' => $exercisePhaseTeam->getId()
            ]),
            'updateCurrentEditor' => $this->router->generate('exercise-phase-team__update-current-editor', [
                'id' => $exercisePhaseTeam->getId()
            ]),
        ];

        return $config;
    }

    private function initiateExercisePhaseTeamWithSolution(
        ExercisePhase     $exercisePhase,
        ExercisePhaseTeam $exercisePhaseTeam,
        User              $user
    ): void
    {
        if (!$exercisePhaseTeam->getCurrentEditor()) {
            $exercisePhaseTeam->setCurrentEditor($user);
        }

        if (!$exercisePhaseTeam->getSolution()) {
            $this->initNewSolution($exercisePhase, $exercisePhaseTeam);
        }

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    private function initNewSolution(ExercisePhase $exercisePhase, ExercisePhaseTeam $exercisePhaseTeam): void
    {
        // NOTE:
        // It would be preferable to use a match expression here.
        // However we would then not be able to use the @var comment with the type
        // assertion...
        if ($exercisePhase->getType() == ExercisePhaseType::MATERIAL) {
            /**
             * @var MaterialPhase $exercisePhase
             **/
            $newSolution = new Solution(
                $exercisePhaseTeam,
                null,
                $exercisePhase->getMaterial()
            );
        } else {
            $newSolution = new Solution($exercisePhaseTeam);
        }

        $exercisePhaseTeam->setSolution($newSolution);

        $this->entityManager->persist($newSolution);
    }

    private function persistPhaseAndRedirectToEdit(Exercise $exercise, ExercisePhase $exercisePhase): RedirectResponse
    {
        $existingPhaseWithHighestSorting = $this
            ->exercisePhaseRepository
            ->findOneBy(['belongsToExercise' => $exercise], ['sorting' => 'desc']);

        $exercisePhase->setSorting(
            $existingPhaseWithHighestSorting
                ? $existingPhaseWithHighestSorting->getSorting() + 1
                : 0
        );

        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();

        return $this->redirectToRoute('exercise-phase__edit', ['id' => $exercise->getId(), 'phase_id' => $exercisePhase->getId()]);
    }

    private function getPhaseForm(ExercisePhase $exercisePhase): FormInterface
    {
        return match ($exercisePhase->getType()) {
            ExercisePhaseType::VIDEO_ANALYSIS => $this->createForm(VideoAnalysisPhaseFormFormType::class, $exercisePhase),
            ExercisePhaseType::VIDEO_CUT => $this->createForm(VideoCutPhaseFormFormType::class, $exercisePhase),
            ExercisePhaseType::REFLEXION => $this->createForm(ReflexionPhaseFormType::class, $exercisePhase),
            ExercisePhaseType::MATERIAL => $this->createForm(MaterialPhaseFormType::class, $exercisePhase),
        };
    }

    private function handlePhaseEditFormSubmit(FormInterface $form, Exercise $exercise): RedirectResponse
    {
        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $form->getData();

        if ($this->hasInvalidPreviousPhase($exercisePhase)) {
            $this->addFlash(
                'danger',
                'Verknüpfung von Phasen aktuell nur möglich wenn die vorherige Phase vom Typ "Video-Analyse" ist.'
            );

            return $this->redirectToRoute('exercise-phase__edit', ['id' => $exercise->getId(), 'phase_id' => $exercisePhase->getId()]);
        } elseif ($this->hasNoActiveComponent($exercisePhase)) {
            $this->addFlash(
                'danger',
                'Mindestens eine Komponente muss aktiv sein'
            );

            return $this->redirectToRoute('exercise-phase__edit', ['id' => $exercise->getId(), 'phase_id' => $exercisePhase->getId()]);
        } else {
            $this->entityManager->persist($exercisePhase);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('exercisePhase.edit.messages.success', [], 'DegreeBase')
            );
        }

        return $this->redirectToRoute('exercise-phase__edit', ['id' => $exercise->getId(), 'phase_id' => $exercisePhase->getId()]);
    }

    private function hasInvalidPreviousPhase(ExercisePhase $exercisePhase): bool
    {
        $exercisePhaseDependedOn = $exercisePhase->getDependsOnExercisePhase();

        if ($exercisePhaseDependedOn == null) {
            return false;
        }

        return !$this->exercisePhaseService->isValidDependingOnExerciseCombination($exercisePhaseDependedOn, $exercisePhase);
    }

    private function hasNoActiveComponent(ExercisePhase $exercisePhase): bool
    {
        $isVideoAnalysis = $exercisePhase->getType() == ExercisePhaseType::VIDEO_ANALYSIS;

        if (!$isVideoAnalysis) {
            return false;
        }

        /** @var VideoAnalysisPhase $exercisePhase */
        return !$exercisePhase->getVideoAnnotationsActive() && !$exercisePhase->getVideoCodesActive();
    }
}
