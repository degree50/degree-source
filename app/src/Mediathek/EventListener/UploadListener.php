<?php


namespace App\Mediathek\EventListener;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\Video\Service\VideoService;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\FileSystem\FileSystemService;
use App\Mediathek\Controller\VideoUploadController;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\Filesystem;
use Oneup\UploaderBundle\Event\PostUploadEvent;
use Oneup\UploaderBundle\Uploader\Response\ResponseInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This listener handles the upload of subtitle, attachment, audioDescription and video files inside the video upload form
 * of our mediathek. The dropzone handling is done inside the VideoUploadController.js, AudioDescriptionUploadController.js and SubtitlesUploadController.js frontend files.
 * The data these controllers use is provided by https://github.com/1up-lab/OneupUploaderBundle/ inside the VideoUpload.html.twig-Template.
 *
 * The actual video form is handled by
 * @see VideoUploadController
 */
class UploadListener
{
    const string TARGET_VIDEO = 'video';
    const string TARGET_ATTACHMENT = 'attachment';
    const string TARGET_SUBTITLE = 'subtitle';
    const string TARGET_AUDIO_DESCRIPTION = 'audio_description';

    public function __construct(
        private readonly FileSystemService       $fileSystemService,
        private readonly EntityManagerInterface  $entityManager,
        private readonly VideoRepository         $videoRepository,
        private readonly Security                $security,
        private readonly ExercisePhaseRepository $exercisePhaseRepository,
        private readonly VideoService            $videoService,
    )
    {
    }

    public function onUpload(PostUploadEvent $event): ?ResponseInterface
    {
        $target = $event->getRequest()->get('target');
        /** @var User $user */
        $user = $this->security->getUser();

        return match ($target) {
            self::TARGET_VIDEO => $this->uploadVideo($event, $user),
            self::TARGET_SUBTITLE => $this->uploadSubtitle($event, $user),
            self::TARGET_ATTACHMENT => $this->uploadAttachment($event, $user),
            self::TARGET_AUDIO_DESCRIPTION => $this->uploadAudioDescription($event, $user),
            default => throw new InvalidArgumentException('Unknown target: "' . $target . '"'),
        };
    }

    private function uploadAttachment(PostUploadEvent $event, User $user): ResponseInterface
    {
        $request = $event->getRequest();

        /** @var UploadedFile $originalFile */
        $originalFile = $request->files->get('file');

        $attachment = new Attachment();
        $attachment->setCreator($user);
        $attachment->setMimeType($originalFile->getClientMimeType());
        $attachment->setName($originalFile->getClientOriginalName());

        $phaseId = $request->get('phaseId');
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $attachment->setExercisePhase($exercisePhase);

        $uploadedFile = $this->uploadFile($attachment->getId(), $event);
        $attachment->setUploadedFile($uploadedFile);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        $response = $event->getResponse();
        $response['success'] = true;
        $response['attachmentId'] = $attachment->getId();
        return $response;
    }

    private function uploadSubtitle(PostUploadEvent $event, User $user)
    {
        $id = $event->getRequest()->get('id');
        assert($id !== '', 'ID is set');

        $video = $this->videoRepository->find($id);

        if (!$video) {
            $video = new Video($id);
        }

        $uploadedSubtitleFile = $this->uploadFile($id, $event);

        $this->videoService->persistUploadedSubtitleFile($video, $uploadedSubtitleFile, $user);

        $response = $event->getResponse();
        $response['success'] = true;
        return $response;
    }

    private function uploadVideo(PostUploadEvent $event, User $user)
    {
        $id = $event->getRequest()->get('id');
        assert($id !== '', 'ID is set');

        $video = $this->videoRepository->find($id);

        if (!$video) {
            $video = new Video($id);
        }

        $uploadedVideoFile = $this->uploadFile($id, $event);

        $this->videoService->persistUploadedVideoFile($video, $uploadedVideoFile, $user);

        $response = $event->getResponse();
        $response['success'] = true;
        return $response;
    }

    private function uploadFile(string $fileName, PostUploadEvent $event)
    {
        $fileSystem = $event->getFile()->getFileSystem();
        assert($fileSystem instanceof Filesystem, "FileSystem must be a Flysystem FileSystem");
        $mountPrefix = $this->fileSystemService->getMountPrefixForFilesystem($fileSystem);

        $targetFileName = sprintf('%s.%s',
            $fileName,
            $event->getFile()->getExtension()
        );
        $uploadedFile = VirtualizedFile::fromMountPointAndFilename($mountPrefix, $targetFileName);

        $fileSystem->move($event->getFile()->getPathname(), $targetFileName);

        return $uploadedFile;
    }

    private function uploadAudioDescription(PostUploadEvent $event, User $user)
    {
        $id = $event->getRequest()->get('id');
        assert($id !== '', 'ID is set');

        $video = $this->videoRepository->find($id);

        if (!$video) {
            $video = new Video($id);
        }

        $uploadedAudioDescriptionFile = $this->uploadFile($id, $event);

        $this->videoService->persistUploadedAudioDescriptionFile($video, $uploadedAudioDescriptionFile, $user);

        $response = $event->getResponse();
        $response['success'] = true;
        return $response;
    }
}
