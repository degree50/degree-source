<?php

namespace App\Tests\Behat;

use App\Domain\Course\Model\Course;
use App\Domain\CutVideo\Model\CutVideo;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\Solution\Model\Solution;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use DateTimeImmutable;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

/**
 *
 */
trait VideoContextTrait
{
    /**
     * @Given The Video with Id :videoId is added to exercisePhase with Id :exercisePhaseId
     *
     * NOTE: The video you are trying to add needs to be available inside the same course, the
     * exercisePhase belongs to
     */
    public function theVideoWithIdIsAddedToExercisePhaseWithId($videoId, $exercisePhaseId): void
    {
        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $this->entityManager->find(ExercisePhase::class, $exercisePhaseId);
        /** @var Video $video */
        $video = $this->entityManager->find(Video::class, $videoId);

        // check if video exists
        assertEquals($videoId, $video->getId());

        $exercisePhase->addVideo($video);

        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();
    }

    /**
     * @Given the Video with Id :videoId is added to Course :courseId
     */
    public function theVideoWithIdIsAddedToCourse($videoId, $courseId): void
    {
        /** @var Course $course */
        $course = $this->entityManager->find(Course::class, $courseId);
        $video = $this->entityManager->find(Video::class, $videoId);

        if ($course) {
            $video->addCourse($course);
        }

        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    /**
     * @Given A Video with Id :videoId created by User :username exists
     */
    public function ensureVideoByUserExists($videoId, $username): Video
    {
        /** @var Video $video */
        $video = $this->entityManager->find(Video::class, $videoId);

        if (!$video) {
            $user = $this->getUserByEmail($username);
            // fixed creation date for video
            $now = new DateTimeImmutable();
            $creationDateForVideo = $now->setTimestamp(1711960922); // 2024-04-01 08:42:02

            $video = new Video($videoId);
            $video->setCreator($user);
            $video->setDataPrivacyAccepted(true);
            $video->setDataPrivacyPermissionsAccepted(true);
            $video->setTitle('TEST_Video_' . $videoId);
            $video->setEncodingStatus(Video::ENCODING_FINISHED);
            $video->setCreatedAt($creationDateForVideo);
            $outputDirectory = VirtualizedFile::fromMountPointAndFilename('encoded_videos', $video->getId());
            $video->setEncodedVideoDirectory($outputDirectory);

            $this->entityManager->persist($video);
            $this->entityManager->flush();
        }

        return $video;
    }

    /**
     * @Then No Video created by User :username should exist
     */
    public function assertVideosByUserDoNotExist($username): void
    {
        $videos = $this->videoRepository->findAll();

        /**
         * Why
         * @see assertExercisesByUserDoNotExist $exercises
         */
        $videosCreatedByUser = array_filter(
            $videos,
            function (Video $video) use ($username) {
                return $video->getCreator() === $username;
            }
        );

        assertEquals(0, count($videosCreatedByUser));
    }

    /**
     * @Then I only receive the regular video :videoId and not the cut video :cutVideoId for creator :userId
     */
    public function iOnlyReceiveTheRegularVideoAndNotTheCutVideoForCreator($videoId, $cutVideoId, $userId): void
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $userId);

        $queryResult = $this->videoRepository->findAllForUser($user);

        assertNotEmpty($queryResult, 'Query result is empty!');

        $filteredByCutId = array_filter($queryResult, function ($video) use ($cutVideoId) {
            return $video->getId() === $cutVideoId;
        });

        assertEmpty($filteredByCutId, 'Cut video was found. (Should not be part of result!)');

        $filteredByVideoId = array_filter($queryResult, function ($video) use ($videoId) {
            return $video->getId() === $videoId;
        });

        assertNotEmpty($filteredByVideoId, 'Regular video was not found. (Should be part of result!)');
        assertEquals($videoId, $filteredByVideoId[0]->getId());
        assertEquals($userId, $filteredByVideoId[0]->getCreator()->getId());
    }

    /**
     * @When I delete the video :videoId
     */
    public function iDeleteTheVideo($videoId): void
    {
        $video = $this->videoRepository->find($videoId);
        assertEquals($videoId, $video->getId());
        $this->videoService->deleteVideo($video);
    }

    /**
     * @Then The video :videoId is deleted
     */
    public function theVideoShouldIsDeleted($videoId): void
    {
        $video = $this->videoRepository->find($videoId);
        assertEquals(null, $video);
    }

    /**
     * @Then The Video :videoId exists
     */
    public function theVideoExists($videoId): void
    {
        $video = $this->videoRepository->find($videoId);
        assertNotNull($video);
    }

    /**
     * @Then The Video :videoId does not exist
     */
    public function theVideoDoesNotExist($videoId): void
    {
        $video = $this->videoRepository->find($videoId);
        assertEquals(null, $video);
    }

    /**
     * @Given A CutVideo with Id :cutVideoId of Video :videoId belonging to Solution :solutionId exists
     */
    public function aCutVideoWithIdBelongingToSolutionCreatedByUserExists(string $cutVideoId, string $videoId, string $solutionId): void
    {
        /** @var Video $video */
        $video = $this->videoRepository->find($videoId);
        /** @var Solution $solution */
        $solution = $this->entityManager->find(Solution::class, $solutionId);

        $cutVideo = new CutVideo($video, $solution, $cutVideoId);
        $cutVideo->setEncodedVideoDirectory(VirtualizedFile::fromMountPointAndFilename('encoded_videos', $cutVideoId));
        $cutVideo->setEncodingStatus(Video::ENCODING_FINISHED);
        // fixed creation date for video
        $cutVideo->setCreatedAt((new DateTimeImmutable())->setTimestamp(1711960922));

        $this->entityManager->persist($cutVideo);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @Then The CutVideo with Id :videoId does not exist
     */
    public function theCutVideoDoesNotExist($cutVideoId): void
    {
        $cutVideo = $this->cutVideoRepository->find($cutVideoId);
        assertEquals(null, $cutVideo);
    }

    /**
     * @Then The CutVideo with Id :cutVideoId exists
     */
    public function theCutVideoExists($cutVideoId): void
    {
        $cutVideo = $this->cutVideoRepository->find($cutVideoId);
        assertNotNull($cutVideo);
    }

    /**
     * @Then No CutVideo of original :originalVideoId exists
     */
    public function noCutVideoOfOriginalExists(string $originalVideoId): void
    {
        $cutVideos = $this->cutVideoRepository->findBy(['originalVideo' => $originalVideoId]);
        $count = count($cutVideos);
        assertEquals(0, $count);
    }
}
