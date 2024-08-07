<?php

namespace App\Command;

use App\Domain\User\Service\UserEmailValidationService;
use App\Domain\User\Service\UserExpirationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveExpiredUsersCommand extends Command
{
    protected static $defaultName = 'app:remove-expired-users';

    public function __construct(
        private readonly UserExpirationService      $userExpirationService,
        private readonly UserEmailValidationService $userEmailValidationService
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(
            'Notifying soon to be expired users | Removing expired users | Removing users with verification timeout'
        );

        $io->writeln('Deleting all expired users..');
        $expiredUsers = $this->userExpirationService->removeAllExpiredUsers();
        if (count($expiredUsers) === 0) {
            $io->success('No expired users found');
        } else {
            $io->writeln('User ids of deleted users:');
            $io->listing($expiredUsers);
            $io->success('Deleted all expired users (' . count($expiredUsers) . ')');
        }

        $io->writeln('Deleting all users with verification timeout..');
        $usersWithVerificationTimeout = $this->userEmailValidationService->removeAllUsersWithVerificationTimeout();
        if (count($usersWithVerificationTimeout) === 0) {
            $io->success('No users with verification timeout found');
        } else {
            $io->writeln('User ids of deleted users:');
            $io->listing($usersWithVerificationTimeout);
            $io->success('Deleted all users with verification timeout (' . count($usersWithVerificationTimeout) . ')');
        }

        $io->writeln('Notifying soon to be expired users..');
        $soonToBeExpiredUsers = $this->userExpirationService->notifySoonToBeExpiredUsers();
        if (count($soonToBeExpiredUsers) === 0) {
            $io->success('No soon to be expired users found');
        } else {
            $io->writeln('User ids of notified users:');
            $io->listing($soonToBeExpiredUsers);
            $io->success('Notified soon to be expired users (' . count($soonToBeExpiredUsers) . ')');
        }

        return 0;
    }
}
