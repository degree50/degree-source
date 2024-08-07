<?php

namespace App\Domain\ExercisePhase\Service;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\AutosavedSolution\Repository\AutosavedSolutionRepository;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Model\MaterialPhase;
use App\Domain\ExercisePhase\Model\ReflexionPhase;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\User\Model\User;
use App\Domain\User\Service\UserMaterialService;
use App\Domain\Video\Model\Video;
use App\Domain\VideoCode\Model\VideoCode;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Twig\AppRuntime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ExercisePhaseService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private UserMaterialService         $materialService,
        private AutosavedSolutionRepository $autoSavedSolutionRepository,
        private TranslatorInterface         $translator,
        private AppRuntime                  $appRuntime,
        private VideoFavouritesService      $videoFavouritesService,
        private UrlGeneratorInterface       $router
    )
    {
    }

    /**
     * Get the exercise phase status for a single team
     */
    public static function getStatusForTeam(?ExercisePhaseTeam $exercisePhaseTeam): ExercisePhaseStatus
    {
        if (is_null($exercisePhaseTeam)) {
            return ExercisePhaseStatus::INITIAL;
        } else {
            return $exercisePhaseTeam->getStatus();
        }
    }

    public function getPhaseTypeTitle(ExercisePhaseType $exercisePhaseType): string
    {
        return $this->translator->trans("exercisePhase.types.$exercisePhaseType->value.label", [], 'DegreeBase');
    }

    public function getPhaseTypeIconClasses(ExercisePhaseType $exercisePhaseType): string
    {
        return $this->translator->trans("exercisePhase.types.$exercisePhaseType->value.iconClass", [], 'DegreeBase');
    }

    /**
     * Check if the combination of ExercisePhase "depending on" and the "depending" ExercisePhase is valid.
     */
    public function isValidDependingOnExerciseCombination(
        ExercisePhase $phaseDependingOn,
        ExercisePhase $dependingPhase
    ): bool
    {
        // check sorting: $phaseDependingOn must come _before_ this $dependingPhase
        if ($phaseDependingOn->getSorting() >= $dependingPhase->getSorting()) {
            return false;
        }

        // check type combination

        // Reflexion can depend on any other(!) type
        if (
            $dependingPhase->getType() === ExercisePhaseType::REFLEXION
            && $phaseDependingOn->getType() !== ExercisePhaseType::REFLEXION
        ) {
            return true;
        }

        // VideoCutting can depend on VideoAnalysis
        if (
            $dependingPhase->getType() === ExercisePhaseType::VIDEO_CUT
            && $phaseDependingOn->getType() === ExercisePhaseType::VIDEO_ANALYSIS
        ) {
            return true;
        }

        // MaterialPhase can depend on VideoAnalysis & VideoCutting
        if (
            $dependingPhase->getType() === ExercisePhaseType::MATERIAL
            && (
                $phaseDependingOn->getType() === ExercisePhaseType::VIDEO_ANALYSIS
                || $phaseDependingOn->getType() === ExercisePhaseType::VIDEO_CUT
            )
        ) {
            return true;
        }

        // VideoAnalysis can depend on VideoAnalysis
        if (
            $dependingPhase->getType() === ExercisePhaseType::VIDEO_ANALYSIS
            && $phaseDependingOn->getType() === ExercisePhaseType::VIDEO_ANALYSIS
        ) {
            return true;
        }

        // other combinations are invalid
        return false;
    }

    /**
     * @return Collection<ExercisePhase>
     */
    public function duplicatePhasesOfExercise(Exercise $originalExercise, Exercise $newExercise): Collection
    {
        $newPhases = $originalExercise->getPhases()->map(
            function (ExercisePhase $originalPhase) use ($newExercise) {
                $newPhase = match ($originalPhase->getType()) {
                    ExercisePhaseType::VIDEO_ANALYSIS => new VideoAnalysisPhase(),
                    ExercisePhaseType::VIDEO_CUT => new VideoCutPhase(),
                    ExercisePhaseType::REFLEXION => new ReflexionPhase(),
                    ExercisePhaseType::MATERIAL => new MaterialPhase(),
                };

                switch ($originalPhase->getType()) {
                    case ExercisePhaseType::VIDEO_ANALYSIS:
                    {
                        /** @var VideoAnalysisPhase $newPhase */
                        /** @var VideoAnalysisPhase $originalPhase */
                        $newPhase->setVideoAnnotationsActive($originalPhase->getVideoAnnotationsActive());
                        $newPhase->setVideoCodesActive($originalPhase->getVideoCodesActive());

                        // create new VideoCodes from original VideoCodes
                        foreach ($originalPhase->getVideoCodes() as $videoCode) {
                            $newVideoCode = new VideoCode();
                            $newVideoCode->setName($videoCode->getName());
                            $newVideoCode->setColor($videoCode->getColor());

                            $newVideoCode->setExercisePhase($newPhase);
                            $newPhase->addVideoCode($newVideoCode);
                        }

                        break;
                    }

                    case ExercisePhaseType::MATERIAL:
                    {
                        /** @var MaterialPhase $newPhase */
                        /** @var MaterialPhase $originalPhase */
                        $newPhase->setMaterial($originalPhase->getMaterial());
                        $newPhase->setReviewRequired($originalPhase->getReviewRequired());

                        break;
                    }

                    case ExercisePhaseType::VIDEO_CUT:
                    case ExercisePhaseType::REFLEXION:
                        break;
                }

                $newPhase->setName($originalPhase->getName());
                $newPhase->setTask($originalPhase->getTask());
                $newPhase->setSorting($originalPhase->getSorting());
                $newPhase->setIsGroupPhase($originalPhase->isGroupPhase());
                $newPhase->setOtherSolutionsAreAccessible($originalPhase->getOtherSolutionsAreAccessible());

                foreach ($originalPhase->getAttachments() as $attachment) {
                    $newPhase->addAttachment($attachment);
                }

                foreach ($originalPhase->getVideos() as $video) {
                    // WHY: The video also needs to be made available in the Course of the new Exercise
                    $newExercise->getCourse()->addVideo($video);
                    $newPhase->addVideo($video);
                }

                $newPhase->setBelongsToExercise($newExercise);

                return $newPhase;
            }
        );

        // depends on exercisePhase
        $originalExercise->getPhases()->map(
            function (ExercisePhase $originalPhase) use ($newPhases, $originalExercise) {
                if ($originalPhase->getDependsOnExercisePhase() !== null) {
                    $indexOfDependingExercise = $originalExercise->getPhases()->indexOf($originalPhase);
                    $indexOfExerciseDependingOn = $originalExercise->getPhases()->indexOf($originalPhase->getDependsOnExercisePhase());

                    /** @var ExercisePhase $newDependingPhase */
                    $newDependingPhase = $newPhases->get($indexOfDependingExercise);
                    /** @var ExercisePhase $newDependingPhase */
                    $newPhaseDependingOn = $newPhases->get($indexOfExerciseDependingOn);

                    $newDependingPhase->setDependsOnExercisePhase($newPhaseDependingOn);
                }
            }
        );

        return $newPhases;
    }

    public function finishPhase(ExercisePhaseTeam $phaseTeam): void
    {
        if ($phaseTeam->getExercisePhase()->getType() == ExercisePhaseType::MATERIAL) {
            $this->finishMaterialPhase($phaseTeam);
        } else {
            $this->finishRegularPhase($phaseTeam);
        }
    }

    /**
     * Finds the last auto saved solution by an exercisePhaseTeam and
     * Creates a solution entity using its contents.
     * */
    public function promoteLastAutoSavedSolutionToRealSolution(ExercisePhaseTeam $exercisePhaseTeam): void
    {
        $solution = $exercisePhaseTeam->getSolution();

        // use solution of the latest auto saved one
        $latestAutoSavedSolution = $this->autoSavedSolutionRepository->findOneBy(
            ['team' => $exercisePhaseTeam],
            ['update_timestamp' => 'desc']
        );

        if ($latestAutoSavedSolution) {
            $solution->setSolution($latestAutoSavedSolution->getSolution());
            $solution->setUpdateTimestamp($latestAutoSavedSolution->getUpdateTimestamp());
        }

        $this->entityManager->persist($solution);
        $this->entityManager->flush();
    }

    /**
     * Removes auto saved solutions by an exercisePhaseTeam.
     * */
    public function cleanupAutoSavedSolutions(ExercisePhaseTeam $exercisePhaseTeam): void
    {
        $autoSavedSolutions = $exercisePhaseTeam->getAutosavedSolutions();
        foreach ($autoSavedSolutions as $autoSavedSolution) {
            $this->entityManager->remove($autoSavedSolution);
        }

        $this->entityManager->flush();
    }

    public function finishReview(ExercisePhaseTeam $phaseTeam): void
    {
        $this->finishMaterialPhase($phaseTeam);
    }

    public function openPhase(ExercisePhaseTeam $exercisePhaseTeam): void
    {
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        if (
            $exercisePhase instanceof MaterialPhase &&
            ($this->getStatusForTeam($exercisePhaseTeam) === ExercisePhaseStatus::BEENDET ||
                $this->getStatusForTeam($exercisePhaseTeam) === ExercisePhaseStatus::IN_REVIEW
            )
        ) {
            // WHY:
            // When a material phase is finished, the final material will be copied to each
            // students dashboard. It would not make sense to allow re-opening the phase in this
            // scenario.
            return; // NoOp
        } else {
            // WHY:
            // While we set the status to "IN_BEARBEITUNG" when we initially create the phase,
            // a user might re-enter the phase after the status has already been set to "BEENDET".
            // In that case we want the phase to reflect that, by having the "IN_BEARBEITUNG" status again.
            $exercisePhaseTeam->setStatus(ExercisePhaseStatus::IN_BEARBEITUNG);
            $exercisePhaseTeam->setPhaseLastOpenedAt(new DateTimeImmutable());

            $this->entityManager->persist($exercisePhaseTeam);
            $this->entityManager->flush();
        }
    }

    /**
     * Get the exercise phase status for a single user
     * */
    public function getStatusForUser(ExercisePhase $exercisePhase, User $user): ExercisePhaseStatus
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase);

        return $this->getStatusForTeam($exercisePhaseTeam);
    }

    /**
     * Get the exercise phase status for all teams and checks if at least one team has a solution
     * to review.
     */
    public function phaseHasAtLeastOneSolutionToReview(ExercisePhase $exercisePhase): bool
    {
        $oneTeamWithSolutionToReview = $this->exercisePhaseTeamRepository->findOneBy([
            'exercisePhase' => $exercisePhase,
            'isTest' => false,
            'status' => ExercisePhaseStatus::IN_REVIEW
        ]);

        return $oneTeamWithSolutionToReview !== null;
    }

    /**
     * @param ExercisePhase $exercisePhase
     * @param User $user
     * @param bool $testMode
     * @return ExercisePhaseTeam
     */
    public function createPhaseTeam(ExercisePhase $exercisePhase, User $user, bool $testMode = false): ExercisePhaseTeam
    {
        $exercisePhaseTeam = new ExercisePhaseTeam();
        $exercisePhaseTeam->setCreator($user);
        $exercisePhaseTeam->setExercisePhase($exercisePhase);
        $exercisePhaseTeam->setIsTest($testMode);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        return $exercisePhaseTeam;
    }

    public function addMemberToPhaseTeam(ExercisePhaseTeam $exercisePhaseTeam, User $user): void
    {
        $exercisePhaseTeam->addMember($user);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    public function deleteExercisePhase(ExercisePhase $exercisePhase): void
    {
        $this->entityManager->remove($exercisePhase);
        $this->entityManager->flush();
    }

    public function getConfig(ExercisePhase $exercisePhase, User $user, $readOnly = false): array
    {
        $components = $this->getComponentsOfExercisePhase($exercisePhase);

        $previousPhase = $exercisePhase->getDependsOnExercisePhase();
        $dependsOnPreviousPhase = $previousPhase !== null;
        $previousPhaseType = $previousPhase?->getType();
        $previousPhaseComponents = $previousPhase === null ? null : $this->getComponentsOfExercisePhase($previousPhase);

        return [
            'title' => $exercisePhase->getName(),
            'description' => $exercisePhase->getTask(),
            'type' => $exercisePhase->getType(),
            'components' => $components,
            'userId' => $user->getId(),
            'userName' => $user->getUsername(),
            'isStudent' => $user->isStudent(),
            'isGroupPhase' => $exercisePhase->isGroupPhase(),
            'dependsOnPreviousPhase' => $dependsOnPreviousPhase,
            'previousPhaseType' => $previousPhaseType,
            'previousPhaseComponents' => $previousPhaseComponents,
            'readOnly' => $readOnly,
            'attachments' => array_map(function (Attachment $entry) {
                return [
                    'id' => $entry->getId(),
                    'name' => $entry->getName(),
                    'type' => $entry->getMimeType(),
                    'url' => $this->router->generate('exercise-overview__attachment--download', ['id' => $entry->getId()])
                ];
            }, $exercisePhase->getAttachments()->toArray()),
            'videos' => array_map(function (Video $video) use ($user) {
                return array_merge($video->getAsClientSideVideo($this->appRuntime)->toArray(), [
                    'isFavorite' => $this->videoFavouritesService->videoIsFavorite($video, $user)
                ]);
            }, $exercisePhase->getVideos()->toArray()),
        ];
    }

    private function finishRegularPhase(ExercisePhaseTeam $phaseTeam): void
    {
        $phaseTeam->setStatus(ExercisePhaseStatus::BEENDET);

        $this->entityManager->persist($phaseTeam);
        $this->entityManager->flush();
    }

    /**
     * Sets the status to "Beendet" for an exercisePhaseTeam of a material phase and
     * also creates a copy of the material for each individual member of the team.
     * */
    private function finishMaterialPhase(ExercisePhaseTeam $phaseTeam): void
    {
        $exercisePhase = $phaseTeam->getExercisePhase();
        /** @var MaterialPhase $materialPhase */
        $materialPhase = $exercisePhase;

        if (
            $phaseTeam->getStatus() !== ExercisePhaseStatus::IN_BEARBEITUNG
            && $phaseTeam->getStatus() !== ExercisePhaseStatus::IN_REVIEW
        ) {
            return;
        }

        if (
            $materialPhase->getReviewRequired()
            && $phaseTeam->getStatus() !== ExercisePhaseStatus::IN_REVIEW
        ) {
            $phaseTeam->setStatus(ExercisePhaseStatus::IN_REVIEW);
        } else {
            $phaseTeam->setStatus(ExercisePhaseStatus::BEENDET);
            $this->materialService->createMaterialsForExercisePhaseTeamMembers($phaseTeam);
        }

        $this->entityManager->flush();
        $this->entityManager->persist($phaseTeam);
    }

    private function getComponentsOfExercisePhase(ExercisePhase $exercisePhase): array
    {
        $components = [];

        switch ($exercisePhase->getType()) {
            case ExercisePhaseType::VIDEO_ANALYSIS:
                /**
                 * @var VideoAnalysisPhase $exercisePhase
                 **/
                if ($exercisePhase->getVideoAnnotationsActive()) {
                    $components[] = ExercisePhase::VIDEO_ANNOTATION;
                }
                if ($exercisePhase->getVideoCodesActive()) {
                    $components[] = ExercisePhase::VIDEO_CODE;
                }

                break;
            case ExercisePhaseType::VIDEO_CUT:
                $components[] = ExercisePhase::VIDEO_CUTTING;
                break;
            default:
                break;
        }

        return $components;
    }
}
