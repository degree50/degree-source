<?php

namespace App\Security\Voter;

use App\Domain\User\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TermsOfUseVoter extends Voter
{
    const string ACCEPTED = 'terms-of-use-accepted';
    const int TERMS_OF_USE_VERSION = 3;

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

        return $user->getTermsOfUseAccepted() && $user->getTermsOfUseVersion() >= TermsOfUseVoter::TERMS_OF_USE_VERSION;
    }
}
