<?php

namespace App\VideoEncoding\MessageHandler;

use App\Domain\CutVideo\Model\CutVideo;
use App\Domain\CutVideo\Repository\CutVideoRepository;
use App\Domain\ExercisePhase\Model\VideoCutPhase;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Video\Model\Video;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\FileSystem\FileSystemService;
use App\Twig\AppRuntime;
use App\VideoEncoding\Message\CutListEncodingTask;
use App\VideoEncoding\Service\EncodingService;
use App\VideoEncoding\Service\SubtitleService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler which is responsible for the encoding of videos from a given cutList
 * Invokes an FFMPEG worker to do the actual encoding
 * NOTE: empty space between cutList items which might exist inside the
 * frontend view, is not taken into consideration when creating the video
 * on the serverside.
 *
 * NOTE: If you make changes to a handler you need to manually stop the worker and restart it,
 * to apply and test the changes.
 *
 * Stop workers:
 * ./symfony-console messenger:stop-workers
 *
 * Start workers:
 * ./symfony-console messenger:consume async -vV
 */
#[AsMessageHandler]
readonly class CutListEncodingHandler
{
    public function __construct(
        private LoggerInterface             $logger,
        private FileSystemService           $fileSystemService,
        private EntityManagerInterface      $entityManager,
        private ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private EncodingService             $encodingService,
        private SubtitleService             $subtitleService,
        private CutVideoRepository          $cutVideoRepository
    )
    {
    }

    public function __invoke(CutListEncodingTask $encodingTask): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->find($encodingTask->exercisePhaseTeamId);
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        if (!$exercisePhase instanceof VideoCutPhase) {
            return;
        }

        $cutList = $exercisePhaseTeam->getSolution()->getSolution()->getCutList();

        if (empty($cutList)) {
            return;
        }

        /* @var CutVideo $cutVideo */
        $cutVideo = $this->cutVideoRepository->find($encodingTask->videoId);

        try {
            $outputDirectory = VirtualizedFile::fromMountPointAndFilename(AppRuntime::ENCODED_VIDEOS, $cutVideo->getId());
            $localOutputDirectory = $this->fileSystemService->localPath($outputDirectory);
            $originalVideoFilePath = $this->fileSystemService->localPath($cutVideo->getOriginalVideo()->getEncodedVideoDirectory()) . '/x264.mp4';

            $this->logger->info('Creating intermediate clips from cutList...');
            $tempClipsDirectory = $this->fileSystemService->generateUniqueTemporaryDirectory();
            $clipPaths = $this->encodingService->createTemporaryMp4ClipsFromCutList($tempClipsDirectory, $cutList, $originalVideoFilePath);

            $this->logger->info('Concatenating clips into video...');
            $mp4Url = $this->encodingService->concatMp4Clips($clipPaths, $localOutputDirectory);

            $this->fileSystemService->deleteDirectory($tempClipsDirectory);

            $cutVideo->setVideoDuration($this->encodingService->probeForVideoDuration($mp4Url));

            $this->encodingService->encodeHLS($mp4Url, $localOutputDirectory);

            $cutVideo->setEncodedVideoDirectory($outputDirectory);

            $this->logger->info("Done combining clips into video <$mp4Url>");

            // get path without file name of mp4 and append "subtitles.vtt"
            $originalSubtitlePath = str_replace("x264.mp4", "subtitles.vtt", $originalVideoFilePath);

            // cut subtitles
            if (file_exists($originalSubtitlePath)) {
                $this->logger->info("Cutting subtitles");
                $originalSubtitles = file_get_contents($originalSubtitlePath);

                $newSubtitles = $this->subtitleService->cutSubtitlesForCutList($originalSubtitles, $cutList);
                $newSubtitlesPath = $localOutputDirectory . "/subtitles.vtt";

                file_put_contents($newSubtitlesPath, $newSubtitles);

                $cutVideo->setSubtitleFile(VirtualizedFile::fromString($newSubtitlesPath));

                $this->logger->info("Done cutting subtitles");
            }

            $this->pingAndReconnectDB();

            $cutVideo->setEncodingStatus(Video::ENCODING_FINISHED);

            $this->entityManager->persist($cutVideo);
            $this->entityManager->flush();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $cutVideo->setEncodingStatus(Video::ENCODING_ERROR);
            $this->entityManager->persist($cutVideo);
            $this->entityManager->flush();
        }
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
