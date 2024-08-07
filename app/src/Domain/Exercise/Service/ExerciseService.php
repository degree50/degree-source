<?php

namespace App\Domain\Exercise\Service;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Exercise\Dto\ExercisePhaseMetadata;
use App\Domain\Exercise\Dto\ExercisePhaseWithMetadataDto;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Model\ExerciseStatus;
use App\Domain\Exercise\Repository\ExerciseRepository;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

readonly class ExerciseService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private ExerciseRepository          $exerciseRepository,
        private ExercisePhaseService        $exercisePhaseService,
        private ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
    )
    {
    }

    /**
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public static function userIsCourseDozent(User $user, Course $course): bool
    {
        return $user->getCourseRoles()->exists(
            fn($i, CourseRole $courseRole) => $courseRole->getCourse() === $course && $courseRole->isCourseDozent()
        );
    }

    /**
     * @param User $user
     * @return Exercise[]
     */
    public function getExercisesForUser(User $user): array
    {
        /** @var Exercise[] $result */
        $result = [];

        foreach ($user->getCourseRoles()->getValues() as $courseRole) {
            $result = [...$result, ...$courseRole->getCourse()->getExercises()];
        }

        $visibleExercises = array_filter($result, fn(Exercise $exercise) => $exercise->getStatus() !== 0);

        return $visibleExercises;
    }

    /**
     * Used for testing the exercise
     *
     * @param Exercise $exercise
     * @return array
     */
    public function getPhasesForTesting(Exercise $exercise): array
    {
        $phasesWithMapping = [];
        foreach ($exercise->getPhases() as $phase) {
            $phasesWithMapping[] = $this->getPhaseWithStatusMetadataForTesting($phase);
        }
        return $phasesWithMapping;
    }

    /**
     * @param Exercise $exercise
     * @param User $user
     * @return array
     */
    public function getPhasesWithStatusMetadata(Exercise $exercise, User $user): array
    {
        $phasesWithMapping = [];
        foreach ($exercise->getPhases() as $phase) {
            if ($user->isStudent() || self::userIsCourseDozent($user, $exercise->getCourse())) {
                $phasesWithMapping[] = $this->getPhaseWithStatusMetadataForStudent($phase, $user);
            } else {
                $phasesWithMapping[] = $this->getPhaseWithStatusMetadataForDozent($phase);
            }
        }
        return $phasesWithMapping;
    }

    public function deleteExercisesCreatedByUser(User $user): void
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('creator', $user));
        $exercises = $this->exerciseRepository->findAllForUserWithCriteria($user, $criteria);

        foreach ($exercises as $exercise) {
            $this->deleteExercise($exercise);
        }
    }

    /**
     * Checks the most current edit date by one of the users exercisePhaseTeams
     * of the exercise.
     * If no teams are found, null is returned.
     * */
    public function getLastEditDateByUser(Exercise $exercise, User $user): ?DateTimeImmutable
    {
        $phases = $exercise->getPhases()->toArray();

        return array_reduce($phases, function ($maybeDate, $phase) use ($user) {
            /** @var ExercisePhase $phase */
            $team = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $phase);

            if (is_null($team)) {
                return $maybeDate;
            }

            return $team->getPhaseLastOpenedAt() > $maybeDate ? $team->getPhaseLastOpenedAt() : $maybeDate;
        }, null);
    }

    public function needsReview(Exercise $exercise): bool
    {
        return $exercise->getPhases()->exists(
            fn($_key, ExercisePhase $exercisePhase) => $exercisePhase->getTeams()->exists(fn($_key, ExercisePhaseTeam $team) => $this->exercisePhaseService->getStatusForTeam($team) === ExercisePhaseStatus::IN_REVIEW)
        );
    }

    public function getExerciseStatusForUser(Exercise $exercise, User $user): ExerciseStatus
    {
        $exercisePhases = $exercise->getPhases();
        $teams = $exercise->getPhases()->map(
            fn(ExercisePhase $phase) => $phase->getTeams()->filter(
                fn(ExercisePhaseTeam $team) => $team->getMembers()->contains($user)
            )->first()
        // Why "mixed": Collection filter returns false if collection is empty o.O
        )->filter(fn(mixed $team) => $team instanceof ExercisePhaseTeam);

        // no phase started yet
        if ($teams->isEmpty()) {
            return ExerciseStatus::NEU;
        }

        if (
            // at least one phase not started yet
            $teams->count() < $exercisePhases->count()
            // at least one phase in BEARBEITUNG
            || $teams->exists(
                fn($_key, ExercisePhaseTeam $team) => $team->getStatus() === ExercisePhaseStatus::IN_BEARBEITUNG
                    || $team->getStatus() === ExercisePhaseStatus::IN_REVIEW
            )
        ) {
            return ExerciseStatus::IN_BEARBEITUNG;
        }

        return ExerciseStatus::BEENDET;
    }

    /*
     * The "needsReview" status is derived from all phases and their teams.
     * So if there is at least one phase that has the "reviewRequired" flag set to true and
     * where the ExercisePhaseStatus of the team is "IN_REVIEW" the "needsReview" status of this Dto
     * will be set to true.
     */

    public function deleteExercise(Exercise $exercise): void
    {
        /**
         * Due to ORM cascading options the following things will also happen when we delete an Exercise:
         *
         *   1. All attached ExercisePhases will be removed @see Exercise::$phases
         */
        $this->entityManager->remove($exercise);
        $this->entityManager->flush();
    }

    public function removeUnpublishedExercisesOfUser(User $user): void
    {
        $unpublishedExercisesOfUser = $this->exerciseRepository->findBy([
            'creator' => $user,
            'status' => Exercise::EXERCISE_CREATED
        ]);

        foreach ($unpublishedExercisesOfUser as $exercise) {
            $this->deleteExercise($exercise);
        }
    }

    private function getPhaseWithStatusMetadataForStudent(ExercisePhase $phase, User $user): array
    {
        $team = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $phase);
        $phaseMetadata = new ExercisePhaseMetadata(
            $this->exercisePhaseService->getStatusForTeam($team) === ExercisePhaseStatus::IN_REVIEW,
            $this->exercisePhaseService->getStatusForTeam($team) === ExercisePhaseStatus::BEENDET,
            $this->exercisePhaseService->getPhaseTypeTitle($phase->getType()),
            $this->exercisePhaseService->getPhaseTypeIconClasses($phase->getType())
        );
        $phaseWithMetadata = new ExercisePhaseWithMetadataDto($phase, $phaseMetadata);

        return $phaseWithMetadata->toArray();
    }

    private function getPhaseWithStatusMetadataForDozent(ExercisePhase $phase): array
    {
        $phaseMetadata = new ExercisePhaseMetadata(
            $this->exercisePhaseService->phaseHasAtLeastOneSolutionToReview($phase),
            false, // not relevant for dozenten, but needed inside the twig template
            $this->exercisePhaseService->getPhaseTypeTitle($phase->getType()),
            $this->exercisePhaseService->getPhaseTypeIconClasses($phase->getType())
        );
        $phaseWithMetadata = new ExercisePhaseWithMetadataDto($phase, $phaseMetadata);
        return $phaseWithMetadata->toArray();
    }

    private function getPhaseWithStatusMetadataForTesting(ExercisePhase $phase): array
    {
        return [
            'phase' => $phase,
            'metadata' => [
                'needsReview' => false,
                'isDone' => false,
                'phaseTitle' => $this->exercisePhaseService->getPhaseTypeTitle($phase->getType()),
                'iconClass' => $this->exercisePhaseService->getPhaseTypeIconClasses($phase->getType()),
            ]
        ];
    }
}
