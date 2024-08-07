<?php

namespace App\Domain\User\Service;

use App\Domain\User\Model\User;
use App\Domain\User\Repository\UserRepository;

/**
 * Most of the email validation logic is done in SymfonyCasts\Bundle\VerifyEmail.
 *
 * This Service handles the domain specific use cases.
 */
readonly class UserEmailValidationService
{
    public function __construct(
        private UserService    $userService,
        private UserRepository $userRepository
    )
    {
    }

    /**
     * @return string[] Ids of deleted users
     */
    public function removeAllUsersWithVerificationTimeout(): array
    {
        /** @var User $usersWithVerificationTimeout */
        $usersWithVerificationTimeout = $this->userRepository->findAllUsersWithVerificationTimeout();
        $studentIds = [];

        foreach ($usersWithVerificationTimeout as $user) {
            if ($user->isAdmin() || $user->isDozent()) {
                continue;
            }

            $studentIds[] = $user->getId();
            $this->userService->deleteStudent($user);
        }

        return $studentIds;
    }
}
