<?php

namespace App\Domain\Attachment\Service;

use App\Domain\Attachment\Repository\AttachmentRepository;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\User\Model\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class AttachmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AttachmentRepository   $attachmentRepository
    )
    {
    }

    public function removeAttachmentsCreatedByUser(User $user): void
    {
        $attachmentsCreatedByUser = $this->attachmentRepository->getAttachmentsCreatedByUser($user);

        foreach ($attachmentsCreatedByUser as $attachment) {
            $this->entityManager->remove($attachment);
        }

        $this->entityManager->flush();
    }

    public function transferAttachmentsOfUserInExerciseToUser(User $user, Exercise $exercise, User $nextDozent): void
    {
        $attachmentsCreatedByUser = $this->attachmentRepository->getAttachmentsCreatedByUserInExercise($user, $exercise);

        foreach ($attachmentsCreatedByUser as $attachment) {
            $attachment->setCreator($nextDozent);
            $this->entityManager->persist($attachment);
        }

        $this->entityManager->flush();
    }
}
