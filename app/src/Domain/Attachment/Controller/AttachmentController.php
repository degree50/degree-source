<?php

namespace App\Domain\Attachment\Controller;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\Attachment\Repository\AttachmentRepository;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\User\Model\User;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use App\Twig\AppRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class AttachmentController extends AbstractController
{

    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly KernelInterface        $kernel,
        private readonly AttachmentRepository   $attachmentRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route("/attachment/download/{id}", name: "exercise-overview__attachment--download")]
    public function download(AppRuntime $appRuntime, Attachment $attachment = null): BinaryFileResponse|Response
    {
        if (!$attachment) {
            return new Response('not allowed', 403);
        }

        $fileUrl = $appRuntime->virtualizedFileUrl($attachment->getUploadedFile());
        $publicResourcesFolderPath = $this->kernel->getProjectDir() . '/public/';
        $response = new BinaryFileResponse($publicResourcesFolderPath . $fileUrl);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachment->getName()
        );
        return $response;
    }

    #[Route("/attachment/delete/{id}", name: "exercise-overview__attachment--delete")]
    public function delete(AppRuntime $appRuntime, Attachment $attachment = null): Response
    {
        if (!$attachment) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $this->removeAttachment($appRuntime, $attachment);

        $this->addFlash(
            'success',
            $this->translator->trans('attachments.delete.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute('exercise-phase__edit', ['id' => $attachment->getExercisePhase()->getBelongsToExercise()->getId(), 'phase_id' => $attachment->getExercisePhase()->getId()]);
    }

    #[Route("/attachment/delete-ajax", name: "exercise-overview__attachment--delete-ajax")]
    public function deleteAjax(AppRuntime $appRuntime, Request $request): Response
    {
        $attachmentIdFromJson = json_decode($request->getContent(), true)['attachmentId'];
        $attachment = $this->attachmentRepository->find($attachmentIdFromJson);

        /** @var User $user */
        $user = $this->getUser();

        if ($attachment->getCreator() !== $user) {
            return new Response('NOT CREATOR', Response::HTTP_FORBIDDEN);
        }

        $this->removeAttachment($appRuntime, $attachment);

        return new Response('OK');
    }

    #[Route("/attachment/list/{id}", name: "exercise-overview__attachment--list")]
    public function uploadedAttachment(ExercisePhase $exercisePhase = null): Response
    {
        if (!$exercisePhase) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        return $this->render('ExercisePhase/AttachmentList.html.twig', [
            'attachmentList' => $exercisePhase->getAttachments()
        ]);
    }

    private function removeAttachment(AppRuntime $appRuntime, Attachment $attachment): void
    {
        $fileUrl = $appRuntime->virtualizedFileUrl($attachment->getUploadedFile());
        $publicResourcesFolderPath = $this->kernel->getProjectDir() . '/public/';

        $filesystem = new Filesystem();
        $filesystem->remove($publicResourcesFolderPath . $fileUrl);

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }
}
