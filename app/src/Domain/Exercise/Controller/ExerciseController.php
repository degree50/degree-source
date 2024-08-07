<?php

namespace App\Domain\Exercise\Controller;

use App\Domain\Course\Model\Course;
use App\Domain\Exercise\Dto\CopyExerciseFormDto;
use App\Domain\Exercise\Form\CopyExerciseFormType;
use App\Domain\Exercise\Form\ExerciseFormType;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder;
use App\Domain\Solution\Service\SolutionService;
use App\Domain\User\Model\User;
use App\Security\Voter\CourseVoter;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\ExerciseVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class ExerciseController extends AbstractController
{
    public function __construct(
        private readonly ExercisePhaseRepository     $exercisePhaseRepository,
        private readonly ExercisePhaseService        $exercisePhaseService,
        private readonly TranslatorInterface         $translator,
        private readonly ExerciseService             $exerciseService,
        private readonly ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private readonly SolutionService             $solutionService,
        private readonly EntityManagerInterface      $entityManager
    )
    {
    }

    /**
     * This action is responsible for showing the general overview of an exercise.
     * This includes the exercise description as well as an overview of phases.
     */
    #[IsGranted(ExerciseVoter::VIEW, subject: "exercise")]
    #[Route("/exercise/{id}/show", name: "exercise__show")]
    public function show(Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->getUser();
        $nextExercisePhase = $this->exercisePhaseRepository->findFirstExercisePhase($exercise);

        return $this->render(
            'Exercise/Show.html.twig',
            [
                'testMode' => false,
                'exercise' => $exercise,
                'exercisePhase' => null,
                'previousExercisePhase' => null,
                'phases' => $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user),
                'currentPhaseIndex' => false,
                'nextExercisePhase' => $nextExercisePhase,
                'amountOfPhases' => count($exercise->getPhases()) - 1,
            ]
        );
    }

    /**
     * Version for testing of showOverview()
     */
    #[IsGranted(ExerciseVoter::TEST, subject: "exercise")]
    #[Route("/exercise/{id}/test", name: "exercise__test")]
    public function test(Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $nextExercisePhase = $this->exercisePhaseRepository->findFirstExercisePhase($exercise);

        return $this->render(
            'Exercise/Show.html.twig',
            [
                'testMode' => true,
                'exercise' => $exercise,
                'exercisePhase' => null,
                'previousExercisePhase' => null,
                'phases' => $this->exerciseService->getPhasesForTesting($exercise),
                'currentPhaseIndex' => false,
                'nextExercisePhase' => $nextExercisePhase,
                'amountOfPhases' => count($exercise->getPhases()) - 1,
            ]
        );
    }

    /**
     * This action is responsible for showing the phase overview screen from which a student
     * is able to start solving the phase or seeing other students solutions.
     */
    #[IsGranted(ExerciseVoter::VIEW, subject: "exercise")]
    #[Route("/exercise/{id}/phase/{phaseId}", name: "exercise__show-phase")]
    public function showExercisePhase(Exercise $exercise = null, string $phaseId = ''): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->getUser();

        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        $previousExercisePhase = $this->exercisePhaseRepository->findExercisePhaseBefore($exercisePhase);
        $nextExercisePhase = $this->exercisePhaseRepository->findExercisePhaseAfter($exercisePhase);

        $teamOfCurrentUser = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase);
        if ($teamOfCurrentUser) {
            $otherTeams = $this->exercisePhaseTeamRepository->findOtherTeamsByPhaseExcludingTests($exercisePhase, $teamOfCurrentUser);
        } else {
            $otherTeams = $this->exercisePhaseTeamRepository->findAllByPhaseExcludingTests($exercisePhase);
        }

        // WHY: Make sure the first team that's shown is the team of the current user
        $allTeams = $teamOfCurrentUser ? array_merge([$teamOfCurrentUser], $otherTeams) : $otherTeams;

        return $this->render(
            'Exercise/ShowPhase.html.twig',
            [
                'testMode' => false,
                'exercise' => $exercise,
                'exercisePhase' => $exercisePhase,
                'phases' => $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user),
                'currentPhaseIndex' => $exercisePhase->getSorting(),
                'previousExercisePhase' => $previousExercisePhase,
                'nextExercisePhase' => $nextExercisePhase,
                'teams' => $allTeams,
                'otherTeams' => $otherTeams,
                'teamOfCurrentUser' => $teamOfCurrentUser,
                'amountOfPhases' => count($exercise->getPhases()) - 1,
            ]
        );
    }

    /**
     * Version for testing of showExercisePhase()
     * Removes other teams and only shows the team of the current user.
     */
    #[IsGranted(ExerciseVoter::TEST, subject: "exercise")]
    #[Route("/exercise/test/{id}/phase/{phaseId}", name: "exercise__show-test-phase")]
    public function showTestExercisePhase(Exercise $exercise = null, string $phaseId = ''): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->getUser();

        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        $previousExercisePhase = $this->exercisePhaseRepository->findExercisePhaseBefore($exercisePhase);
        $nextExercisePhase = $this->exercisePhaseRepository->findExercisePhaseAfter($exercisePhase);

        $teamOfCurrentUser = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase, true);

        $teams = [];

        if ($teamOfCurrentUser !== null) {
            $teams[] = $teamOfCurrentUser;
        }

        return $this->render(
            'Exercise/ShowPhase.html.twig',
            [
                'testMode' => true,
                'exercise' => $exercise,
                'exercisePhase' => $exercisePhase,
                'phases' => $this->exerciseService->getPhasesForTesting($exercise),
                'currentPhaseIndex' => $exercisePhase->getSorting(),
                'previousExercisePhase' => $previousExercisePhase,
                'nextExercisePhase' => $nextExercisePhase,
                'teams' => $teams,
                'otherTeams' => [],
                'teamOfCurrentUser' => $teamOfCurrentUser,
                'amountOfPhases' => count($exercise->getPhases()) - 1,
            ]
        );
    }

    #[IsGranted(CourseVoter::NEW_EXERCISE, subject: "course")]
    #[Route("/exercise/new/{id}", name: "exercise__new")]
    public function new(Request $request, Course $course = null): Response
    {
        if (!$course) {
            return $this->render("Security/403.html.twig");
        }

        $exercise = new Exercise();
        $exercise->setCourse($course);
        /** @var User $user */
        $user = $this->getUser();
        $exercise->setCreator($user);

        $form = $this->createForm(ExerciseFormType::class, $exercise);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$exercise` variable has also been updated
            $exercise = $form->getData();

            $this->entityManager->persist($exercise);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('exercise.new.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
        }

        return $this->render('Exercise/New.html.twig', [
            'course' => $course,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit", name: "exercise__edit")]
    public function edit(Request $request, Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $form = $this->createForm(ExerciseFormType::class, $exercise);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$exercise` variable has also been updated
            /** @var Exercise $exercise */
            $exercise = $form->getData();

            $this->entityManager->persist($exercise);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('exercise.edit.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
        }

        $exerciseHasSolutions = $this->getExerciseHasSolutions($exercise);

        $exercisePhases = $exercise->getPhases()->toArray();

        $phasesWithAllowedSortingIndication = array_map(function ($index, ExercisePhase $phase) use ($exercisePhases) {
            $previousPhase = $index === 0 ? null : $exercisePhases[$index - 1];
            $canMoveUp = $phase->getDependsOnExercisePhase() !== $previousPhase;

            /**
             * @var ExercisePhase $nextPhase
             */
            $nextPhase = $index === count($exercisePhases) - 1 ? null : $exercisePhases[$index + 1];
            $canMoveDown = $nextPhase && $nextPhase->getDependsOnExercisePhase() !== $phase;

            return [
                "phase" => $phase,
                "canMoveUp" => $canMoveUp,
                "canMoveDown" => $canMoveDown,
            ];
        }, array_keys($exercisePhases), $exercisePhases);

        return $this->render('Exercise/Edit.html.twig', [
            'exercise' => $exercise,
            'exercisePhases' => $phasesWithAllowedSortingIndication,
            'exerciseHasSolutions' => $exerciseHasSolutions,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/edit/change-status", name: "exercise__change-status")]
    public function changeStatus(Request $request, Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $newStatus = (int)$request->query->get('status', Exercise::EXERCISE_CREATED);
        $exercise->setStatus($newStatus);

        if ($newStatus == Exercise::EXERCISE_PUBLISHED && count($exercise->getPhases()) == 0) {
            $this->addFlash(
                'danger',
                'Aufgabe hat noch keine Phasen!'
            );

            return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
        }

        $this->entityManager->persist($exercise);
        $this->entityManager->flush();

        return $this->redirectToRoute('exercise__edit', ['id' => $exercise->getId()]);
    }

    #[IsGranted(ExerciseVoter::DELETE, subject: "exercise")]
    #[Route("/exercise/{id}/delete", name: "exercise__delete")]
    public function delete(Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $this->exerciseService->deleteExercise($exercise);

        $this->addFlash(
            'success',
            $this->translator->trans('exercise.delete.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute('exercise-overview', ['id' => $exercise->getCourse()->getId()]);
    }

    #[IsGranted(ExerciseVoter::EDIT, subject: "exercise")]
    #[Route("/exercise/{id}/copy", name: "exercise__copy")]
    public function copy(Request $request, Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $form = $this->createForm(CopyExerciseFormType::class, CopyExerciseFormDto::fromExercise($exercise));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var CopyExerciseFormDto $formDto
             */
            $formDto = $form->getData();

            /** @var User $user */
            $user = $this->getUser();

            /**
             * The new copy of the exercise that will be persisted
             */
            $newExercise = new Exercise();

            // copy stuff over to new Exercise
            $newExercise->setName('Kopie - ' . $exercise->getName());
            $newExercise->setDescription($exercise->getDescription());
            // set creator to current user performing the action
            $newExercise->setCreator($user);
            $newExercise->setCreatedAtValue();
            // set status to "created" (unpublished)
            $newExercise->setStatus(Exercise::EXERCISE_CREATED);
            // set course to Course the user selected in form
            $newExercise->setCourse($formDto->course);

            // create new ExercisePhases by duplicating the original ones
            if ($formDto->copyPhases) {
                $newPhases = $this->exercisePhaseService->duplicatePhasesOfExercise($exercise, $newExercise);
                $newExercise->setPhases($newPhases);
            }

            $this->entityManager->persist($newExercise);
            $this->entityManager->persist($newExercise->getCourse());
            $this->entityManager->flush();

            return $this->redirectToRoute('exercise__edit', ['id' => $newExercise->getId()]);
        }

        return $this->render('Exercise/Copy.html.twig', [
            'exercise' => $exercise,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(ExerciseVoter::SHOW_SOLUTION, subject: "exercise")]
    #[Route("/exercise/{id}/show-solutions/{phaseId?}", name: "exercise__show-solutions")]
    public function showSolutions(Request $request, Exercise $exercise = null): Response
    {
        if (!$exercise) {
            return $this->render("Security/403.html.twig");
        }

        $phaseId = $request->get('phaseId');

        $exercisePhase = $phaseId
            ? $this->exercisePhaseRepository->find($phaseId)
            : $exercise->getPhases()->first();

        if (!($exercisePhase instanceof ExercisePhase)) {
            $this->addFlash(
                'danger',
                $this->translator->trans('exerciseOverview.error.exercise-no-phase', [], 'DegreeBase')
            );

            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('exercise-overview'));
        }

        $teams = $this->exercisePhaseTeamRepository->findAllByPhaseExcludingTests($exercisePhase);

        // HOTFIX: It's possible to create a team without solutions right now, e.g. when a user creates a team in a
        // group phase and does not start the phase for this group at least once.
        // This leads to an 500 Error down the line, when the cut video of the solution is accessed. (can't get a
        // cut video from a solution that does not exist).
        // This is a quick fix and should be addressed with more insight later.
        $teams = array_filter($teams, fn($team) => $team->getSolution() !== null);

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $data = $exercisePhase->getType() === ExercisePhaseType::REFLEXION
            ? null
            : $this->solutionService->retrieveAndAddDataToClientSideDataBuilderForSolutionView(
                $clientSideSolutionDataBuilder,
                $teams
            );

        /** @var User $user */
        $user = $this->getUser();
        $phases = $this->exerciseService->getPhasesWithStatusMetadata($exercise, $user);

        return $this->render('ExercisePhase/ShowSolutions.html.twig', [
            'config' => $this->exercisePhaseService->getConfig($exercisePhase, $user, true),
            'data' => $data,
            'exercise' => $exercise,
            'phases' => $phases,
            'currentExercisePhase' => $exercisePhase,
            'hasSolutions' => $exercisePhase->getHasSolutions()
        ]);
    }

    private function getExerciseHasSolutions(Exercise $exercise): bool
    {
        $phases = $exercise->getPhases()->toArray();

        /** @var ExercisePhase $phase */
        foreach ($phases as $phase) {
            if ($phase->getHasSolutions()) {
                return true;
            }
        }

        return false;
    }
}
