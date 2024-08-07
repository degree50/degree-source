<?php
declare(strict_types=1);

namespace App\Security;

use App\Domain\User\Model\User;
use Nbgrp\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;

/**
 * Create new users if they do not exist locally
 */
class SamlUserFactory implements SamlUserFactoryInterface
{
    public function createUser(string $identifier, array $attributes): User
    {
        $userIdentifier = $attributes['mail'][0];

        $user = new User();
        $user->setIsSSOUser(true);
        $user->setIsStudent(true);
        $user->setPassword('notused');
        $user->setEmail($userIdentifier);
        // SAML users are verified by default
        $user->setIsVerified(true);

        return $user;
    }
}
