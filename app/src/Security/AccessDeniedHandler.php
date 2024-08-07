<?php

namespace App\Security;

use App\Domain\User\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

readonly class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security              $security,
        private Environment           $twig
    )
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): RedirectResponse|Response
    {
        /**
         * @var User $user
         * */
        $user = $this->security->getUser();

        if (!$user->isVerified()) {
            return new RedirectResponse($this->urlGenerator->generate('app_verify_email_pending'));
        }

        if (!$user->acceptedCurrentDataPrivacy()) {
            return new RedirectResponse($this->urlGenerator->generate('app_data-privacy'));
        }

        if (!$user->acceptedCurrentTermsOfUse()) {
            return new RedirectResponse($this->urlGenerator->generate('app_terms-of-use'));
        }

        $content = $this->twig->render('Security/403.html.twig', [
            'message' => $accessDeniedException->getMessage(),
        ]);
        return new Response($content, 403);
    }
}
