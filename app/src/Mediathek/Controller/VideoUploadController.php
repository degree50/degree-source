<?php

namespace App\Mediathek\Controller;

use App\Domain\Course\Model\Course;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\Video\Service\VideoService;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\Mediathek\Form\MediathekVideoFormType;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use App\Security\Voter\VideoVoter;
use App\Twig\AppRuntime;
use App\VideoEncoding\Message\WebEncodingTask;
use App\VideoEncoding\MessageHandler\WebEncodingHandler;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles the upload of video, audio and subtitles files inside the mediathek.
 * The locations where the files are saved to are specified inside @see {oneup_flysystem.yaml}.
 *
 * As soon as the videos form data is being submitted, we dispatch a
 * @see {VideoEncoding\Message\WebEncodingTask}, which triggers the encoding of the
 * video file and outputs the result into the encoded_videos/ directory.
 *
 * The application then makes only use of the encoded_files, but not of the originally uploaded videos (these are deleted after the encoding completion).
 *
 * NOTE: The upload of the original files which are later encoded by the WebEncodingTask is handled by our UploadListener-Implementation
 * @see UploadListener
 */
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class VideoUploadController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly MessageBusInterface    $messageBus,
        private readonly VideoRepository        $videoRepository,
        private readonly VideoService           $videoService,
        private readonly AppRuntime             $appRuntime,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * Either shows the video upload form or redirects to the mediathek if the form has been submitted.
     * After a successful submit a WebEncodingTask is being dispatched, so that the original video gets encoded.
     * @see WebEncodingHandler
     * @see WebEncodingTask
     */
    #[IsGranted(VideoVoter::CREATE)]
    #[Route("/video/uploads/{id?}", name: "mediathek__video--upload")]
    public function showVideoUploadForm(Request $request, Course $course = null): Response
    {
        $videoUuid = $request->query->get('videoUuid');

        if (!$videoUuid) {
            $videoUuid = Uuid::uuid4()->toString();
        }

        $video = $this->videoRepository->find($videoUuid);

        if (!$video) {
            $video = new Video($videoUuid);
        }

        /** @var User $user */
        $user = $this->getUser();
        $video->setCreator($user);
        if ($course) {
            $video->addCourse($course);
        }

        $form = $this->createForm(MediathekVideoFormType::class, $video, [
            'action' => $this->generateUrl('mediathek__video--upload', ['videoUuid' => $videoUuid])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $video = $form->getData();
            assert($video instanceof Video);

            // dispatch event to start encoding when form is submitted
            $outputDirectory = VirtualizedFile::fromMountPointAndFilename(AppRuntime::ENCODED_VIDEOS, $video->getId());
            $this->messageBus->dispatch(new WebEncodingTask($video->getId(), $outputDirectory));

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            $this->addFlash(
                'info',
                $this->translator->trans('video.upload.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('mediathek__video--player', ['id' => $video->getId()]);
        }

        return $this->render('Mediathek/VideoUpload/VideoUpload.html.twig', [
            // we generate the desired UUID for the video entity server-side, to ensure the file
            // upload and the normal HTML form fit together properly.
            'uuid' => $videoUuid,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(VideoVoter::EDIT, subject: "video")]
    #[Route("/video/edit/{id}", name: "mediathek__video--edit")]
    public function edit(Request $request, Video $video = null): Response
    {
        if (!$video) {
            return $this->render("Security/403.html.twig");
        }

        $form = $this->createForm(MediathekVideoFormType::class, $video);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $video = $form->getData();
            assert($video instanceof Video);

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('video.edit.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('mediathek--index');
        }

        return $this->render('Mediathek/VideoUpload/Edit.html.twig', [
            'video' => $video,
            'videoMap' => $video->getAsClientSideVideo($this->appRuntime),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete the video entity and the encoded video
     */
    #[IsGranted(VideoVoter::DELETE, subject: "video")]
    #[Route("/video/delete/{id}/{confirm}", name: "mediathek__video--delete")]
    public function delete(Video $video = null, bool $confirm = false): Response
    {
        if (!$video) {
            return $this->render("Security/403.html.twig");
        }

        if ($confirm) {
            $this->videoService->deleteVideo($video);
            $this->addFlash(
                'success',
                $this->translator->trans('video.delete.messages.success', [], 'DegreeBase')
            );
            return $this->redirectToRoute('mediathek--index');
        }

        return $this->render('Mediathek/VideoUpload/Delete.html.twig', [
            'video' => $video,
        ]);
    }

    /**
     * Triggered by VideoUploadController.js to remove newly uploaded videos
     */
    #[Route("/video/delete-ajax/{id}", name: "mediathek__video--delete-ajax")]
    public function deleteAjax(Video $video = null): Response
    {
        if (!$video) {
            return new Response('not allowed', 403);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($video->getCreator() !== $user) {
            return new Response('NOT CREATOR', Response::HTTP_FORBIDDEN);
        }

        $this->videoService->removeOriginalVideoFile($video);

        return new Response('OK');
    }

    /**
     * Triggered by VideoUploadController.js to remove newly uploaded subtitles
     */
    #[Route("/video/delete-subtitle-ajax/{id}", name: "mediathek__subtitle--delete-ajax")]
    public function deleteSubtitleAjax(Video $video = null): Response
    {
        if (!$video) {
            return new Response('not allowed', 403);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($video->getCreator() !== $user) {
            return new Response('NOT CREATOR', Response::HTTP_FORBIDDEN);
        }

        $this->videoService->removeOriginalSubtitleFile($video);

        return new Response('OK');
    }

    /**
     * Triggered by AudioDescriptionUploadController.js to remove newly uploaded audioDescriptions
     */
    #[Route("/video/delete-audio-description-ajax/{id}", name: "mediathek__audio_description--delete-ajax")]
    public function deleteAudioDescriptionAjax(Video $video = null): Response
    {
        if (!$video) {
            return new Response('not allowed', 403);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($video->getCreator() !== $user) {
            return new Response('NOT CREATOR', Response::HTTP_FORBIDDEN);
        }

        $this->videoService->removeOriginalAudioDescriptionFile($video);

        return new Response('OK');
    }
}
