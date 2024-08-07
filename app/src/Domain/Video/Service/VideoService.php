<?php

namespace App\Domain\Video\Service;

use App\Domain\CutVideo\Repository\CutVideoRepository;
use App\Domain\CutVideo\Service\CutVideoService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\FileSystem\FileSystemService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This service handles operations on video entities.
 */
readonly class VideoService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VideoRepository        $videoRepository,
        private VideoFavouritesService $videoFavouritesService,
        private FileSystemService      $fileSystemService,
        private CutVideoService        $cutVideoService,
        private CutVideoRepository     $cutVideoRepository,
    )
    {
    }

    public function deleteVideosCreatedByUser(User $user): void
    {
        $videosCreatedByUser = $this->videoRepository->findByCreator($user);

        foreach ($videosCreatedByUser as $video) {
            $this->deleteVideo($video);
        }
    }

    /**
     * Removes a video entity along with its encoded video directory.
     * Also removes all exercises that are associated with the video.
     */
    public function deleteVideo(Video $video): void
    {
        $exercisePhases = $video->getExercisePhases();

        foreach ($exercisePhases as $exercisePhase) {
            /** @var ExercisePhase $exercisePhase */
            $exercisePhase->removeVideo($video);
            $this->entityManager->persist($exercisePhase);
        }

        // remove VideoFavourites
        $this->videoFavouritesService->removeVideoFavoritesOfVideo($video);

        $this->fileSystemService->deleteDirectory($video->getEncodedVideoDirectory());

        // remove CutVideos of this video
        $this->cutVideoService->deleteCutVideosOfOriginalVideo($video);

        $this->videoRepository->deleteVideo($video);
    }

    /**
     * This method removes the originally uploaded video file from the file system.
     * Note: This does not affect any encoded files.
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function removeOriginalVideoFile(Video $video): void
    {
        $this->fileSystemService->deleteFile($video->getUploadedVideoFile());

        $video->setUploadedVideoFile(null);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * This method removes the originally uploaded subtitle file from the file system.
     * Note: This does not affect the subtitles.vtt file wich is created inside
     * the encoded_videos/<id> directory upon encoding!
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function removeOriginalSubtitleFile(Video $video): void
    {
        $this->fileSystemService->deleteFile($video->getUploadedSubtitleFile());

        $video->setUploadedSubtitleFile(null);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * Persists the an uploaded video file to the respective video and also sets some basic properties like <creator> and dataPrivacy properties.
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function persistUploadedVideoFile(
        Video           $video,
        VirtualizedFile $uploadedVideoFile,
        User            $user,
    ): Video
    {
        $video->setCreator($user);
        $video->setUploadedVideoFile($uploadedVideoFile);
        $video->setDataPrivacyAccepted(false);
        $video->setDataPrivacyPermissionsAccepted(false);

        $this->entityManager->persist($video);
        $this->entityManager->flush();

        return $video;
    }

    /**
     * Persists the an uploaded subtitle file to the respective video and also sets some basic properties like <creator> and dataPrivacy properties.
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function persistUploadedSubtitleFile(
        Video           $video,
        VirtualizedFile $uploadedSubtitleFile,
        User            $user
    ): void
    {
        $video->setCreator($user);
        $video->setUploadedSubtitleFile($uploadedSubtitleFile);
        $video->setDataPrivacyAccepted(false);
        $video->setDataPrivacyPermissionsAccepted(false);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * Persists the an uploaded audio file to the respective video and also sets some basic properties like <creator> and dataPrivacy properties.
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function persistUploadedAudioDescriptionFile(
        ?Video          $video,
        VirtualizedFile $uploadedAudioDescriptionFile,
        User            $user
    ): void
    {
        $video->setCreator($user);
        $video->setUploadedAudioDescriptionFile($uploadedAudioDescriptionFile);
        $video->setDataPrivacyAccepted(false);
        $video->setDataPrivacyPermissionsAccepted(false);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * This method removes the originally uploaded audio description file from the file system.
     *
     * @see {App\Mediathek\Controller\VideoUploadController}
     */
    public function removeOriginalAudioDescriptionFile(Video $video): void
    {
        $this->fileSystemService->deleteFile($video->getUploadedAudioDescriptionFile());

        $video->setUploadedAudioDescriptionFile(null);

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    public function removeUnUsedVideosOfUser(User $user): void
    {
        /** @var Video[] $videos */
        $videos = $this->videoRepository->findByCreator($user);

        foreach ($videos as $video) {
            if ($video->getExercisePhases()->isEmpty()) {
                $this->deleteVideo($video);
            }
        }
    }
}
