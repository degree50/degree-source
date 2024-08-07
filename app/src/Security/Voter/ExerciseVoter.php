<?php

namespace App\Security\Voter;

use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Model\ExerciseStatus;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\User\Model\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExerciseVoter extends Voter
{
    const string VIEW = 'exercise_view';
    const string TEST = 'exercise_test';
    const string SHOW_SOLUTION = 'exercise_showSolution';
    const string EDIT = 'exercise_edit';
    const string DELETE = 'exercise_delete';
    const string IS_OPENED = 'exercise_isOpened';
    const string IS_FINISHED = 'exercise_isFinished';

    public function __construct(private readonly ExerciseService $exerciseService)
    {
    }

    public static function canTest(Exercise $exercise, User $user): bool
    {
        // creator can test
        if ($exercise->getCreator() === $user) {
            return true;
        }

        // student can only test his/her own exercises
        if ($user->isStudent()) {
            return false;
        }

        // if user has role "dozent" in course, he or she can test
        $isCourseDozent = ExerciseService::userIsCourseDozent($user, $exercise->getCourse());

        return $isCourseDozent && $user->isDozent();
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [
            self::VIEW,
            self::TEST,
            self::SHOW_SOLUTION,
            self::EDIT,
            self::DELETE,
            self::IS_OPENED,
            self::IS_FINISHED
        ])) {
            return false;
        }

        if (!$subject instanceof Exercise) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User || !$subject instanceof Exercise) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // admin has access to everything
        if ($user->isAdmin()) {
            return true;
        }

        return match ($attribute) {
            self::IS_FINISHED => $this->exerciseHasBeenFinished($subject, $user),
            self::IS_OPENED => $this->exerciseHasBeenStarted($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::TEST => $this->canTest($subject, $user),
            self::EDIT, self::DELETE, self::SHOW_SOLUTION => $this->canEditOrDelete($subject, $user),
            default => throw new InvalidArgumentException('Unknown attribute ' . $attribute),
        };
    }

    private function exerciseHasBeenStarted(Exercise $exercise, User $user): bool
    {
        return $this->exerciseService->getExerciseStatusForUser($exercise, $user) === ExerciseStatus::IN_BEARBEITUNG;
    }

    private function exerciseHasBeenFinished(Exercise $exercise, User $user): bool
    {
        return $this->exerciseService->getExerciseStatusForUser($exercise, $user) === ExerciseStatus::BEENDET;
    }

    private function canView(Exercise $exercise, User $user): bool
    {
        $course = $exercise->getCourse();
        $hasAccessToCourse = $user->getCourseRoles()->exists(
            fn($i, CourseRole $courseRole) => $courseRole->getCourse() === $course &&
                $courseRole->getUser() === $user
        );

        if ($hasAccessToCourse) {
            // Dozent has access
            if ($user->isDozent()) {
                return true;
            }

            // everyone else needs to wait for publication
            $exercisePublished = $exercise->getStatus() == Exercise::EXERCISE_PUBLISHED;
            $exerciseNotEmpty = count($exercise->getPhases()) > 0;

            if ($exercisePublished && $exerciseNotEmpty) {
                return true;
            }
        }

        return false;
    }

    private function canEditOrDelete(Exercise $exercise, User $user): bool
    {
        // creator has access
        if ($user === $exercise->getCreator()) {
            return true;
        }

        $isCourseDozent = ExerciseService::userIsCourseDozent($user, $exercise->getCourse());

        // Dozent && KursDozent has access
        if ($isCourseDozent && $user->isDozent()) {
            return true;
        }

        return false;
    }
}
