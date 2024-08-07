<?php

namespace App\Security\Voter;

use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExercisePhaseVoter extends Voter
{
    const string SHOW = 'exercisePhase_show';
    const string NEXT = 'exercisePhase_next';
    const string DELETE = 'exercisePhase_delete';
    const string TEST = 'exercisePhase_test';
    const string SHOW_SOLUTIONS = 'exercisePhase_showSolutions';
    const string CREATE_TEAM = 'exercisePhase_createTeam';
    const string VIEW_OTHER_SOLUTIONS = 'exercisePhase_viewOtherSolutions';
    const string VIEW = 'exercisePhase_viewExercisePhase';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [
            self::SHOW,
            self::NEXT,
            self::DELETE,
            self::SHOW_SOLUTIONS,
            self::CREATE_TEAM,
            self::VIEW_OTHER_SOLUTIONS,
            self::VIEW,
            self::TEST
        ])) {
            return false;
        }

        if (!$subject instanceof ExercisePhase) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($subject instanceof ExercisePhase) {
            $exercisePhase = $subject;
        } else {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        /* @var CourseRole|null $courseRoleOfUser */
        $courseRoleOfUser = $user->getCourseRoles()->findFirst(
            fn($i, $courseRole) => $courseRole->getCourse() === $exercisePhase->getBelongsToExercise()->getCourse()
        );

        if ($user->isDozent()) {
            return $courseRoleOfUser->isCourseDozent();
        }

        if ($exercisePhase->getBelongsToExercise()->getCreator() === $user) {
            return true;
        }

        return match ($attribute) {
            self::SHOW => $this->canShow($exercisePhase, $user),
            self::NEXT => $this->canGetToNextPhase($exercisePhase, $user),
            self::DELETE => $this->canDelete($exercisePhase, $user),
            self::SHOW_SOLUTIONS => $this->canShowSolutions($exercisePhase, $user),
            self::CREATE_TEAM => $this->canCreateTeam($exercisePhase, $user),
            self::VIEW_OTHER_SOLUTIONS => $this->canViewOtherSolutions($exercisePhase, $user),
            self::VIEW => $this->canViewExercisePhase($exercisePhase, $user),
            self::TEST => $this->canTest($exercisePhase, $user),
            default => throw new InvalidArgumentException('Unknown attribute ' . $attribute),
        };
    }

    private function canShow(ExercisePhase $exercisePhase, User $user): bool
    {
        return $exercisePhase->getTeams()->exists(fn($i, ExercisePhaseTeam $exercisePhaseTeam) => $exercisePhaseTeam->getMembers()->contains($user));
    }

    /**
     * User can view this ExercisePhase.
     * He can if it's the first or the user has a Solution for the previous ExercisePhase or the previous phase was a reflexion phase.
     *
     * @param ExercisePhase $exercisePhase
     * @param User $user
     * @return bool
     */
    private function canViewExercisePhase(ExercisePhase $exercisePhase, User $user): bool
    {
        // check if previous ExercisePhase exists or has solution
        $sortingPosition = $exercisePhase->getSorting();

        // is first ExercisePhase
        if ($sortingPosition === 0) {
            return true;
        }

        $phaseSortingsUpToThisPhase = range(0, $exercisePhase->getSorting() - 1);
        $exercise = $exercisePhase->getBelongsToExercise();

        $phasesBeforeAreDone = array_reduce(
            $phaseSortingsUpToThisPhase,
            function ($allAreDone, $phaseSorting) use ($exercise, $user) {
                $phase = $exercise->getPhaseAtSortingPosition($phaseSorting);

                if (empty($phase)) {
                    return $allAreDone;
                }

                if ($phase->getType() === ExercisePhaseType::REFLEXION) {
                    return $allAreDone;
                }

                if ($phase->getHasSolutionForUser($user)) {
                    return $allAreDone;
                }

                return false;
            }, true);

        return $phasesBeforeAreDone;
    }

    private function canViewOtherSolutions(ExercisePhase $exercisePhase, User $user): bool
    {
        // As there is a bug that crashes the app if the user himself (even the admin) has not
        // yet has startet the phase once (and thus created a Team -> which causes the crash)
        if ($exercisePhase->getHasSolutionForUser($user)) {
            return $exercisePhase->getOtherSolutionsAreAccessible() || $exercisePhase->getBelongsToExercise()->getCreator() === $user;
        }

        return false;
    }

    private function canGetToNextPhase(ExercisePhase $exercisePhase, User $user): bool
    {
        return $exercisePhase->getHasSolutionForUser($user);
    }

    private function canDelete(ExercisePhase $exercisePhase, User $user): bool
    {
        return $user === $exercisePhase->getBelongsToExercise()->getCreator();
    }

    private function canShowSolutions(ExercisePhase $exercisePhase, User $user): bool
    {
        return $user === $exercisePhase->getBelongsToExercise()->getCreator();
    }

    private function canCreateTeam(ExercisePhase $exercisePhase, User $user): bool
    {
        $existingTeams = $exercisePhase->getTeams();
        $canCreate = true;
        /** @var ExercisePhaseTeam $team */
        foreach ($existingTeams as $team) {
            if ($team->isTest()) {
                continue;
            }

            if ($team->getMembers()->contains($user)) {
                $canCreate = false;
            }

            if ($team->getCreator() === $user) {
                $canCreate = false;
            }
        }

        return $canCreate;
    }

    private function canTest(ExercisePhase $exercisePhase, User $user): bool
    {
        $exercise = $exercisePhase->getBelongsToExercise();
        return ExerciseVoter::canTest($exercise, $user);
    }
}
