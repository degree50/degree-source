<?php

namespace App\Domain\User\Service;

use App\Domain\User\Model\User;
use App\Domain\User\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class UserExpirationService
{
    public function __construct(
        private UserService            $userService,
        private UserRepository         $userRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface        $mailer,
        private LoggerInterface        $logger,
        private TranslatorInterface    $translator,
    )
    {
    }

    /**
     * @return string[] Ids of the notified users
     */
    public function notifySoonToBeExpiredUsers(): array
    {
        $soonToBeExpiredUsers = $this->userRepository->findAllUnNotifiedSoonToBeExpiredUsers();
        $soonToBeExpiredStudentIds = [];

        foreach ($soonToBeExpiredUsers as $user) {
            // skip admins and dozenten
            if ($user->isAdmin() || $user->isDozent()) {
                continue;
            }

            $successfullySent = $this->sendExpirationNotice($user);

            // Only mark the user as notified if the email was sent successfully
            if ($successfullySent) {
                $user->setExpirationNoticeSent(true);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $soonToBeExpiredStudentIds[] = $user->getId();
            }
        }

        return $soonToBeExpiredStudentIds;
    }

    /**
     * @return string[] Ids of deleted users
     */
    public function removeAllExpiredUsers(): array
    {
        // get expired users
        $expiredUsers = $this->userRepository->findAllExpiredUsers();
        $expiredStudentIds = [];

        foreach ($expiredUsers as $user) {
            if ($user->isAdmin() || $user->isDozent()) {
                continue;
            }

            $expiredStudentIds[] = $user->getId();
            $this->userService->deleteStudent($user);
        }

        return $expiredStudentIds;
    }

    public function increaseExpirationDateByOneYearForUser(User $user): void
    {
        $oldExpirationDate = $user->getExpirationDate();
        $newExpirationDate = $oldExpirationDate->add(DateInterval::createFromDateString('1 year'));

        $user->setExpirationDate($newExpirationDate);
        $user->setExpirationNoticeSent(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @return bool true if the user's expiration date can be updated by user.
     */
    public function userCanUpdateExpirationDate(User $user): bool
    {
        $expirationNotificationWindowStart = (new DateTimeImmutable())
            ->add(DateInterval::createFromDateString(User::EXPIRATION_NOTICE_DURATION_STRING));

        // For example: Expiration date is smaller than "6 month from now".
        return $user->getExpirationDate() < $expirationNotificationWindowStart;
    }

    /**
     * @param User $user
     * @return bool true if the email was sent successfully
     */
    private function sendExpirationNotice(User $user): bool
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($this->translator->trans('email.subject', [], 'UserExpiration'))
            ->htmlTemplate('UserExpiration/notification_email.html.twig')
            ->context([
                'routeName' => 'app_increase_user_expiration_date',
                'deletionDate' => $user->getExpirationDate()->format('j. F Y'),
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send expiration notice email to user', [
                'userId' => $user->getId(),
                'exception' => $e,
            ]);
            return false;
        }
    }
}
