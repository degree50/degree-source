<?php

namespace App\Domain\ExercisePhaseTeam\Controller;

use App\Domain\AutosavedSolution\Model\AutosavedSolution;
use App\Domain\CutVideo\Service\CutVideoService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideSolutionData;
use App\Domain\Solution\Model\Solution;
use App\Domain\Solution\Service\SolutionService;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\LiveSync\LiveSyncService;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\ExercisePhaseTeamVoter;
use App\Security\Voter\ExercisePhaseVoter;
use App\Security\Voter\SolutionVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use App\VideoEncoding\Message\CutListEncodingTask;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class ExercisePhaseTeamController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly TranslatorInterface         $translator,
        private readonly LiveSyncService             $liveSyncService,
        private readonly MessageBusInterface         $messageBus,
        private readonly SolutionService             $solutionService,
        private readonly ExercisePhaseService        $exercisePhaseService,
        private readonly ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private readonly CutVideoService             $cutVideoService
    )
    {
    }

    #[IsGranted(ExercisePhaseVoter::CREATE_TEAM, subject: "exercisePhase")]
    #[Route("/exercise-phase/{id}/team/new", name: "exercise-phase-team__new")]
    public function new(ExercisePhase $exercisePhase = null): Response
    {
        if (!$exercisePhase) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->getUser();
        $exercise = $exercisePhase->getBelongsToExercise();

        $existingTeams = $this->exercisePhaseTeamRepository->findBy(
            [
                'exercisePhase' => $exercisePhase,
                'isTest' => false
            ]
        );

        foreach ($existingTeams as $team) {
            if ($team->getCreator() === $user) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('exercisePhaseTeam.new.messages.alreadyCreatedATeam', [], 'DegreeBase')
                );
                return $this->redirectToRoute(
                    'exercise__show-phase',
                    [
                        'id' => $exercise->getId(),
                        'phaseId' => $exercisePhase->getId()
                    ]
                );
            }
        }

        $exercisePhaseTeam = $this->exercisePhaseService->createPhaseTeam($exercisePhase, $user);
        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);

        if ($exercisePhase->isGroupPhase()) {
            $this->addFlash(
                'success',
                $this->translator->trans('exercisePhaseTeam.new.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute(
                'exercise__show-phase',
                [
                    'id' => $exercise->getId(),
                    'phaseId' => $exercisePhase->getId()
                ]
            );
        } else {
            return $this->redirectToRoute(
                'exercise-phase__show',
                [
                    'id' => $exercisePhase->getId(),
                    'team_id' => $exercisePhaseTeam->getId()
                ]
            );
        }
    }

    #[IsGranted(ExercisePhaseVoter::TEST, subject: "exercisePhase")]
    #[Route("/exercise-phase/test/{id}/team/new", name: "exercise-phase-team__test-new")]
    public function test(ExercisePhase $exercisePhase = null): Response
    {
        if (!$exercisePhase) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->getUser();
        $exercise = $exercisePhase->getBelongsToExercise();

        $exercisePhaseTeam = $this->exercisePhaseService->createPhaseTeam($exercisePhase, $user, true);
        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);

        if ($exercisePhase->isGroupPhase()) {
            $this->addFlash(
                'success',
                $this->translator->trans('exercisePhaseTeam.new.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute(
                'exercise__show-test-phase',
                [
                    'id' => $exercise->getId(),
                    'phaseId' => $exercisePhase->getId()
                ]
            );
        } else {
            return $this->redirectToRoute(
                'exercise-phase__test',
                [
                    'id' => $exercisePhase->getId(),
                    'team_id' => $exercisePhaseTeam->getId()
                ]
            );
        }
    }

    #[IsGranted(ExercisePhaseTeamVoter::JOIN, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/{id}/team/{team_id}/join", name: "exercise-phase-team__join")]
    public function join(
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

        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);

        $this->addFlash(
            'success',
            $this->translator->trans('exercisePhaseTeam.join.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute(
            'exercise__show-phase',
            [
                'id' => $exercisePhase->getBelongsToExercise()->getId(),
                'phaseId' => $exercisePhase->getId()
            ]
        );
    }

    #[IsGranted(ExercisePhaseTeamVoter::DELETE, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/{id}/team/{team_id}/delete", name: "exercise-phase-team__delete")]
    public function delete(
        ExercisePhase     $exercisePhase = null,
        #[MapEntity(id: "team_id")]
        ExercisePhaseTeam $exercisePhaseTeam = null,
    ): Response
    {
        if (!($exercisePhase && $exercisePhaseTeam)) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($exercisePhaseTeam);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('exercisePhaseTeam.delete.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute(
            'exercise__show-phase',
            [
                'id' => $exercisePhase->getBelongsToExercise()->getId(),
                'phaseId' => $exercisePhase->getId()
            ]
        );
    }

    #[IsGranted(ExercisePhaseTeamVoter::LEAVE, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/{id}/team/{team_id}/leave", name: "exercise-phase-team__leave")]
    public function leave(
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

        $exercisePhaseTeam->removeMember($user);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('exercisePhaseTeam.leave.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute(
            'exercise__show-phase',
            [
                'id' => $exercisePhase->getBelongsToExercise()->getId(),
                'phaseId' => $exercisePhase->getId()
            ]
        );
    }

    #[Route("/exercise-phase/share-result/{id}", name: "exercise-phase-team__share-result")]
    public function shareResult(ExercisePhaseTeam $exercisePhaseTeam = null): Response
    {
        if (!$exercisePhaseTeam) {
            return $this->render("Security/403.html.twig");
        }

        $this->exercisePhaseService->promoteLastAutoSavedSolutionToRealSolution($exercisePhaseTeam);
        $this->exercisePhaseService->cleanupAutoSavedSolutions($exercisePhaseTeam);
        $this->exercisePhaseService->finishPhase($exercisePhaseTeam);

        $this->dispatchCutListEncodingTask($exercisePhaseTeam);

        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        if ($exercisePhaseTeam->isTest()) {
            return $this->redirectToRoute(
                'exercise__show-test-phase',
                [
                    'id' => $exercisePhase->getBelongsToExercise()->getId(),
                    'phaseId' => $exercisePhase->getId()
                ]
            );
        } else {
            return $this->redirectToRoute(
                'exercise__show-phase',
                [
                    'id' => $exercisePhase->getBelongsToExercise()->getId(),
                    'phaseId' => $exercisePhase->getId()
                ]
            );
        }
    }

    #[Route("/exercise-phase/finish-reflexion/{id}", name: "exercise-phase-team--finish-reflexion")]
    public function finishReflexion(ExercisePhaseTeam $exercisePhaseTeam = null): Response
    {
        if (!$exercisePhaseTeam) {
            return $this->render("Security/403.html.twig");
        }

        // TODO
        // We might refactor this, so that this can be part of share_result instead and we
        // no longer have the distinction inside the template (and also would not need this route anymore)

        $this->exercisePhaseService->finishPhase($exercisePhaseTeam);

        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        return $this->redirectToRoute(
            'exercise__show-phase',
            [
                'id' => $exercisePhase->getBelongsToExercise()->getId(),
                'phaseId' => $exercisePhase->getId()
            ]
        );
    }

    /**
     * Try to create a new AutosaveSolution and then publish the most recent version of the solution.
     */
    #[IsGranted(ExercisePhaseTeamVoter::UPDATE_SOLUTION, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/update-solution/{id}", name: "exercise-phase-team__update-solution")]
    public function updateSolution(Request $request, ExercisePhaseTeam $exercisePhaseTeam = null): Response
    {
        if (!$exercisePhaseTeam) {
            return new Response('not allowed', 403);
        }

        /** @var User $user */
        $user = $this->getUser();
        $solutionDataFromJson = json_decode($request->getContent(), true);

        $serverSideSolutionData = ServerSideSolutionData::fromClientJSON($solutionDataFromJson);

        // If current user is not current editor && there is a solution -> discard
        if ($user === $exercisePhaseTeam->getCurrentEditor()) {
            $autosaveSolution = new AutosavedSolution();
            $autosaveSolution->setTeam($exercisePhaseTeam);
            $autosaveSolution->setSolution($serverSideSolutionData);
            $autosaveSolution->setOwner($user);

            $this->entityManager->persist($autosaveSolution);
            $this->entityManager->flush();

            $response = new Response('OK');
        } else {
            $response = new Response('Not editor!', Response::HTTP_FORBIDDEN);
        }

        $exercisePhase = $exercisePhaseTeam->getExercisePhase();
        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilder(
            $clientSideSolutionDataBuilder,
            $exercisePhaseTeam,
            $exercisePhase
        );

        // push solution to clients
        $this->liveSyncService->publish($exercisePhaseTeam, [
            'data' => $clientSideSolutionDataBuilder,
            'currentEditor' => $exercisePhaseTeam->getCurrentEditor()->getId()
        ]);

        return $response;
    }

    /**
     * Update a solution of a student as Dozent.
     * This is only possible for MaterialPhases, where the Dozent can edit the material solution of a student
     * as a review process.
     */
    #[IsGranted(SolutionVoter::REVIEW_SOLUTION, subject: "solution")]
    #[Route("/exercise-phase/review-solution/{id}", name: "exercise-phase-team__review-solution")]
    public function reviewSolution(Request $request, Solution $solution = null): Response
    {
        if (!$solution) {
            return new Response('not allowed', 403);
        }

        $solutionDataFromJson = json_decode($request->getContent(), true);

        $serverSideSolutionData = ServerSideSolutionData::fromClientJSON($solutionDataFromJson);

        $solution->setSolution($serverSideSolutionData);
        $solution->setUpdateTimestamp(new DateTimeImmutable());

        $this->entityManager->persist($solution);
        $this->entityManager->flush();

        return new Response('OK');
    }

    /**
     * Try to update the currentEditor of the TeamPhase and then publish the most recent solution
     */
    #[IsGranted(ExercisePhaseTeamVoter::UPDATE_SOLUTION, subject: "exercisePhaseTeam")]
    #[Route("/exercise-phase/update-current-editor/{id}", name: "exercise-phase-team__update-current-editor")]
    public function updateCurrentEditor(Request $request, ExercisePhaseTeam $exercisePhaseTeam = null): Response
    {
        if (!$exercisePhaseTeam) {
            return new Response('not allowed', 403);
        }

        $user = $this->getUser();

        // get teamMember with the candidate id
        $currentEditorCandidateIdFromJson = json_decode(
            $request->getContent(),
            true
        )['currentEditorCandidateId'];

        $currentEditorCandidate = $exercisePhaseTeam
            ->getMembers()
            ->filter(function (User $member) use ($currentEditorCandidateIdFromJson) {
                return $member->getId() === $currentEditorCandidateIdFromJson;
            })
            ->first();

        if (!$currentEditorCandidate) {
            return new Response('currentEditor candidate is not member of exercisePhaseTeam!', Response::HTTP_FORBIDDEN);
        }

        $isAlreadySet = $currentEditorCandidate === $exercisePhaseTeam->getCurrentEditor();
        $userIsCandidate = $currentEditorCandidate === $user;
        $userIsCurrentEditor = $user === $exercisePhaseTeam->getCurrentEditor();

        // let only current editor or candidate make the change
        if (!$isAlreadySet && ($userIsCandidate || $userIsCurrentEditor)) {
            $exercisePhaseTeam->setCurrentEditor($currentEditorCandidate);

            // Why: We do not need the event info about the currentEditor
            $this->entityManager->persist($exercisePhaseTeam);
            $this->entityManager->flush();

            $exercisePhase = $exercisePhaseTeam->getExercisePhase();

            $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
            $this->solutionService->retrieveAndAddDataToClientSideDataBuilder(
                $clientSideSolutionDataBuilder,
                $exercisePhaseTeam,
                $exercisePhase
            );

            // push new state to clients
            $this->liveSyncService->publish(
                $exercisePhaseTeam,
                [
                    'data' => $clientSideSolutionDataBuilder,
                    'currentEditor' => $exercisePhaseTeam->getCurrentEditor()->getId()
                ]
            );
            return new Response('OK');
        } else {
            return new Response('Not updated!', Response::HTTP_NOT_MODIFIED);
        }
    }

    private function dispatchCutListEncodingTask(ExercisePhaseTeam $exercisePhaseTeam): void
    {
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        if (!$exercisePhase instanceof VideoCutPhase && !$exercisePhase->getVideos()->first() instanceof Video) {
            return;
        }

        $solution = $exercisePhaseTeam->getSolution();
        $solutionData = $solution->getSolution();
        $cutList = $solutionData->getCutList();

        if (empty($cutList)) {
            return;
        }

        // delete the previous cut video
        $this->cutVideoService->deleteCutVideoOfSolution($solution);
        // create a new one
        $cutVideo = $this->cutVideoService->createCutVideoForVideoAndSolution($exercisePhase->getVideos()->first(), $solution);

        // hand it over to the encoding service to create the actual video data
        $this->messageBus->dispatch(new CutListEncodingTask($exercisePhaseTeam->getId(), $cutVideo->getId()));
    }
}
