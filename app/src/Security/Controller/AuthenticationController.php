<?php

namespace App\Security\Controller;

use App\Domain\User\Model\User;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthenticationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route("/login", name: "app_login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // WHY: We don't want to leak information about registered email addresses.
        if ($error) {
            $maskedError = new BadCredentialsException();
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'Security/Login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $maskedError ?? null,
            ]
        );
    }

    /**
     * NOTE: This route must match security.yaml at path `security.firewalls.main.logout.path`
     *
     * DO NOT TOUCH; the SAML IDP calls /saml/logout (as the URL is part of the public SAML API at <request_scheme_and_host>/saml/metadata)
     */
    #[Route("/saml/logout", name: "app_logout")]
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route("/user/data-privacy", name: "app_data-privacy")]
    public function dataPrivacy(Request $request): Response
    {
        $accepted = (bool)$request->query->get('accepted', false);
        /** @var User $user */
        $user = $this->getUser();

        if ($accepted) {
            $user->setDataPrivacyAccepted(true);
            $user->setDataPrivacyVersion(DataPrivacyVoter::DATA_PRIVACY_VERSION);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('exercise-overview');
        }

        return $this->render('Security/DataPrivacy.html.twig');
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route("/user/terms-of-use", name: "app_terms-of-use")]
    public function termsOfUse(Request $request): Response
    {
        $accepted = (bool)$request->query->get('accepted', false);
        /** @var User $user */
        $user = $this->getUser();

        if ($accepted) {
            $user->setTermsOfUseAccepted(true);
            $user->setTermsOfUseVersion(TermsOfUseVoter::TERMS_OF_USE_VERSION);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('exercise-overview');
        }

        return $this->render('Security/TermsOfUse.html.twig');
    }
}
