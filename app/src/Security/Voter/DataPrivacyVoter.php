<?php

namespace App\Security\Voter;

use App\Domain\User\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DataPrivacyVoter extends Voter
{
    const string ACCEPTED = 'data-privacy-accepted';
    const int DATA_PRIVACY_VERSION = 3;

    public function supports($attribute, $subject): bool
    {
        return $attribute === self::ACCEPTED;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $user->getDataPrivacyAccepted() && $user->getDataPrivacyVersion() >= DataPrivacyVoter::DATA_PRIVACY_VERSION;
    }
}
