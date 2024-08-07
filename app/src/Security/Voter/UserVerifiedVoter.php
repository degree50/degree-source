<?php

namespace App\Security\Voter;

use App\Domain\User\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVerifiedVoter extends Voter
{
    const string USER_VERIFIED = 'user-verified';

    public function supports($attribute, $subject): bool
    {
        return $attribute === self::USER_VERIFIED;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $user->isVerified();
    }
}
