<?php

namespace App\Security\Voter;

use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Solution\Model\Solution;
use App\Domain\User\Model\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SolutionVoter extends Voter
{
    const string REVIEW_SOLUTION = 'solution_reviewSolution';

    private ExercisePhaseTeamRepository $exercisePhaseTeamRepository;

    public function __construct(ExercisePhaseTeamRepository $exercisePhaseTeamRepository)
    {
        $this->exercisePhaseTeamRepository = $exercisePhaseTeamRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if ($attribute != self::REVIEW_SOLUTION) {
            return false;
        }

        if (!$subject instanceof Solution) {
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

        if ($subject instanceof Solution) {
            $solution = $subject;
        } else {
            return false;
        }

        return match ($attribute) {
            self::REVIEW_SOLUTION => $this->canReviewSolution($solution, $user),
            default => throw new InvalidArgumentException('Unknown attribute ' . $attribute),
        };
    }

    private function canReviewSolution(Solution $solution, User $user): bool
    {
        // admins can do anything
        if ($user->isAdmin()) {
            return true;
        }

        // only Dozenten review solutions
        if (!$user->isDozent()) {
            return false;
        }

        $course = $this->exercisePhaseTeamRepository
            ->findBySolution($solution)
            ->getExercisePhase()
            ->getBelongsToExercise()
            ->getCourse();

        // check if Dozent has access to course
        return ExerciseService::userIsCourseDozent($user, $course);
    }
}
