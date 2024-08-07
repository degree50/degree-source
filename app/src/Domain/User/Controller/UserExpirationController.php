<?php

namespace App\Domain\User\Controller;

use App\Domain\User\Service\UserExpirationService;
use App\Domain\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserExpirationController extends AbstractController
{
    public function __construct(
        private readonly UserService           $userService,
        private readonly UserExpirationService $userExpirationService,
        private readonly TranslatorInterface   $translator,
    )
    {
    }

    /**
     * Display & process form to request a password reset.
     */
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route('/account-expiration', name: 'app_increase_user_expiration_date')]
    public function increaseUserExpirationDatePage(): Response
    {
        $user = $this->userService->getLoggendInUser();

        if ($user === null) {
            $this->addFlash(
                'danger',
                $this->translator->trans('flash-message.error.not-authenticated', [], 'UserExpiration')
            );
            return $this->redirectToRoute('app');
        }

        if ($this->userExpirationService->userCanUpdateExpirationDate($user)) {
            return $this->render('UserExpiration/IncreaseExpirationDate.html.twig', [
                'routeName' => 'app_increase_user_expiration_date_process',
                'deletionDate' => $user->getExpirationDate()->format('j. F Y'),
            ]);
        } else {
            $this->addFlash(
                'danger',
                $this->translator->trans('flash-message.error.not-now', [], 'UserExpiration')
            );
            return $this->redirectToRoute('app');
        }
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route('/account-expiration-increase', name: 'app_increase_user_expiration_date_process')]
    public function increaseUserExpirationDateAction(): RedirectResponse
    {
        $user = $this->userService->getLoggendInUser();

        if ($user === null) {
            $this->addFlash(
                'danger',
                $this->translator->trans('flash-message.error.not-authenticated', [], 'UserExpiration')
            );
            return $this->redirectToRoute('app');
        }

        $this->userExpirationService->increaseExpirationDateByOneYearForUser($user);

        $this->addFlash(
            'success',
            $this->translator->trans('flash-message.success', [], 'UserExpiration')
        );
        return $this->redirectToRoute('app');
    }
}
