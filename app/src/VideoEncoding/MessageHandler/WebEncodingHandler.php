<?php

namespace App\VideoEncoding\MessageHandler;

use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\Video\Service\VideoService;
use App\FileSystem\FileSystemService;
use App\Mediathek\Controller\VideoUploadController;
use App\VideoEncoding\Message\WebEncodingTask;
use App\VideoEncoding\Service\EncodingService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Encodes freshly uploaded videos
 * @see VideoUploadController
 *
 * NOTE: If you make changes to a handler you need to manually stop the worker and restart it,
 * to apply and test the changes.
 *
 * Stop workers:
 * ./symfony-console messenger:stop-workers
 *
 * Start workers:
 * ./symfony-console messenger:consume async -vv
 * Note: the -v|-vv|-vvv option determines the log level
 */
#[AsMessageHandler]
readonly class WebEncodingHandler
{
    public function __construct(
        private LoggerInterface        $logger,
        private FileSystemService      $fileSystemService,
        private VideoRepository        $videoRepository,
        private EntityManagerInterface $entityManager,
        private VideoService           $videoService,
        private EncodingService        $encodingService,
    )
    {
    }

    public function __invoke(WebEncodingTask $encodingTask): void
    {
        /* @var Video|null $video */
        $video = $this->videoRepository->find($encodingTask->videoId);

        try {
            if ($video === null) {
                $this->logger->warning('Video not found for encoding', ['videoId' => $encodingTask->videoId]);
                throw new Exception('Video not found for encoding', ['videoId' => $encodingTask->videoId]);
            }

            $video->setEncodingStatus(Video::ENCODING_STARTED);
            $this->entityManager->persist($video);
            $this->entityManager->flush();

            $temporaryDirectory = $this->fileSystemService->generateUniqueTemporaryDirectory();
            $temporaryDirectoryPath = $this->fileSystemService->localPath($temporaryDirectory);

            $this->encodingService->encodeMP4WithAudioDescription($video, $temporaryDirectoryPath);

            // We use our encoded mp4 file as baseline for further encoding to HLS
            // That way we can guarantee that the resulting HLS will be playable.
            $mp4Url = $temporaryDirectoryPath . '/x264.mp4';
            $videoDuration = $this->encodingService->probeForVideoDuration($mp4Url);
            $this->encodingService->encodeHLS($mp4Url, $temporaryDirectoryPath);

            $this->encodingService->createPreviewImage($mp4Url, $temporaryDirectoryPath, $videoDuration);

            if (!empty($video->getUploadedSubtitleFile()->getVirtualPathAndFilename())) {
                $this->copyUploadedSubtitleFileToOutputDirectory($video, $temporaryDirectoryPath);
            } else {
                $this->createEmptyDefaultSubtitlesFile($temporaryDirectoryPath);
            }

            $video->setVideoDuration($videoDuration);
            $video->setEncodingStatus(Video::ENCODING_FINISHED);

            $this->fileSystemService->moveDirectory($temporaryDirectory, $encodingTask->desiredOutputDirectory);

            $video->setEncodedVideoDirectory($encodingTask->desiredOutputDirectory);
        } catch (Exception $exception) {
            $video->setEncodingStatus(Video::ENCODING_ERROR);
            $this->logger->error('Error while encoding video', ['exception' => $exception]);
        } finally {
            // Remove intermediate uploaded files to save disc space
            $this->videoService->removeOriginalSubtitleFile($video);
            $this->videoService->removeOriginalAudioDescriptionFile($video);
            $this->videoService->removeOriginalVideoFile($video);

            $this->pingAndReconnectDB();

            $this->entityManager->persist($video);
            $this->entityManager->flush();

            // WHY: chown output directory recursively because they're created as root by encoder process
            shell_exec('chown -R www-data:www-data ' . $this->fileSystemService->localPath($video->getEncodedVideoDirectory()));
        }
    }

    private function copyUploadedSubtitleFileToOutputDirectory(Video $video, $localOutputDirectory): void
    {
        $uploadedFilePath = $this->fileSystemService->localPath($video->getUploadedSubtitleFile());
        $destinationPath = $localOutputDirectory . '/subtitles.vtt';
        copy($uploadedFilePath, $destinationPath);
    }

    private function createEmptyDefaultSubtitlesFile($localOutputDirectory): void
    {
        $path = $localOutputDirectory . '/subtitles.vtt';

        file_put_contents($path, '');
    }

    private function pingAndReconnectDB(): void
    {
        // WHY: The encoding process might take quite long and the db connection might have been
        // lost/closed in the meantime. Therefore we check if we still have a connection and
        // otherwise reconnect.
        if ($this->entityManager->getConnection()->isConnected() === false) {
            $this->entityManager->getConnection()->close();
            $this->entityManager->getConnection()->connect();
        }
    }
}
