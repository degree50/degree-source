<?php

namespace App\Security\Voter;

use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExercisePhaseTeamVoter extends Voter
{
    const string JOIN = 'exercisePhaseTeam_join';
    const string SHOW = 'exercisePhaseTeam_show';
    const string SHOW_SOLUTION = 'exercisePhaseTeam_showSolution';
    const string LEAVE = 'exercisePhaseTeam_leave';
    const string DELETE = 'exercisePhaseTeam_delete';
    const string UPDATE_SOLUTION = 'exercisePhaseTeam_updateSolution';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [
            self::JOIN,
            self::SHOW,
            self::SHOW_SOLUTION,
            self::LEAVE,
            self::DELETE,
            self::UPDATE_SOLUTION
        ])) {
            return false;
        }

        if (!$subject instanceof ExercisePhaseTeam) {
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

        if ($subject instanceof ExercisePhaseTeam) {
            $exercisePhaseTeam = $subject;
        } else {
            return false;
        }

        return match ($attribute) {
            self::SHOW => $this->canShow($exercisePhaseTeam, $user),
            self::SHOW_SOLUTION => $this->canShowSolution($exercisePhaseTeam, $user),
            self::JOIN => $this->canJoin($exercisePhaseTeam, $user),
            self::LEAVE => $this->canLeave($exercisePhaseTeam, $user),
            self::DELETE => $this->canDelete($exercisePhaseTeam, $user),
            self::UPDATE_SOLUTION => $this->canUpdateSolution($exercisePhaseTeam, $user),
            default => throw new InvalidArgumentException('Unknown attribute ' . $attribute),
        };
    }

    private function canLeave(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        $isMember = $exercisePhaseTeam->getMembers()->contains($user);
        // WHY: The last member can't leave - only delete the Team
        $isNotTheOnlyMember = $exercisePhaseTeam->getMembers()->count() > 1;

        return $isMember && $isNotTheOnlyMember;
    }

    private function canDelete(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        $isMember = $exercisePhaseTeam->getMembers()->contains($user);
        // WHY: Only the last member can delete the Team
        $isTheOnlyMember = $exercisePhaseTeam->getMembers()->count() === 1;

        return $isMember && $isTheOnlyMember;
    }

    private function canShow(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        return $exercisePhaseTeam->getMembers()->contains($user);
    }

    private function canShowSolution(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        return $this->canShow($exercisePhaseTeam, $user) || $exercisePhaseTeam->getExercisePhase()->getOtherSolutionsAreAccessible();
    }

    private function canJoin(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        // WHY: Admins & Dozenten can only create their own Team
        // (see issue #261)
        if ($user->isAdmin() || $user->isDozent()) {
            return false;
        }

        $existingTeams = $exercisePhaseTeam->getExercisePhase()->getTeams();
        $canJoin = true;
        foreach ($existingTeams as $team) {
            if ($team->getMembers()->contains($user)) {
                $canJoin = false;
            }
        }

        return $canJoin;
    }

    private function canUpdateSolution(ExercisePhaseTeam $exercisePhaseTeam, User $user): bool
    {
        return $exercisePhaseTeam->getMembers()->contains($user);
    }
}
