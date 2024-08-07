<?php

namespace App\Tests\Behat;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

trait AttachmentContextTrait
{
    /**
     * @Given I have an attachment with ID :attachmentId
     */
    public function iHaveAnAttachmentWithId($attachmentId): void
    {
        $attachment = new Attachment($attachmentId);
        $fileName = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($fileName, 'my file');
        $attachment->setName($fileName);
        $attachment->setMimeType('application/pdf');

        $user = $this->getUserByEmail('foo@bar.de');
        $attachment->setCreator($user);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();
    }

    /**
     * @Given An Attachment with Id :attachmentId created by User :username exists for ExercisePhase :exercisePhaseId
     */
    public function ensureAttachmentByUserExistsInExercisePhase($attachmentId, $username, $exercisePhaseId): void
    {
        $user = $this->getUserByEmail($username);
        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $this->entityManager->find(ExercisePhase::class, $exercisePhaseId);
        /** @var Attachment $attachment */
        $attachment = $this->entityManager->find(Attachment::class, $attachmentId);

        if (!$attachment) {
            $attachment = new Attachment($attachmentId);
            $fileName = tempnam(sys_get_temp_dir(), 'foo');
            file_put_contents($fileName, 'my file');
            $attachment->setName('TEST_ATTACHMENT_' . $attachmentId);
            $attachment->setMimeType('application/pdf');
        }

        $attachment->setCreator($user);
        $exercisePhase->addAttachment($attachment);

        $this->entityManager->persist($exercisePhase);
        $this->entityManager->persist($attachment);

        $this->entityManager->flush();
    }

    /**
     * @Then The Attachment :attachmentId exists
     */
    public function assertAttachmentExists($attachmentId): void
    {
        $attachment = $this->entityManager->find(Attachment::class, $attachmentId);
        assertNotNull($attachment);
    }

    /**
     * @Then The Attachment :attachmentId does not exist
     */
    public function assertAttachmentDoesNotExist($attachmentId): void
    {
        $attachment = $this->entityManager->find(Attachment::class, $attachmentId);
        assertEquals(null, $attachment);
    }
}
