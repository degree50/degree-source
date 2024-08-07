<?php

namespace App\Domain\CutVideo\Service;

use App\Domain\CutVideo\Model\CutVideo;
use App\Domain\CutVideo\Repository\CutVideoRepository;
use App\Domain\Solution\Model\Solution;
use App\Domain\Video\Model\Video;
use App\FileSystem\FileSystemService;

/**
 * This service handles operations on video entities.
 */
readonly class CutVideoService
{
    public function __construct(
        private FileSystemService  $fileSystemService,
        private CutVideoRepository $cutVideoRepository,
    )
    {
    }

    public function deleteCutVideosOfOriginalVideo(Video $video): void
    {
        $cutVideos = $this->cutVideoRepository->findBy(['originalVideo' => $video]);

        foreach ($cutVideos as $cutVideo) {
            $this->deleteDirectoryOfCutVideo($cutVideo);
            $this->cutVideoRepository->deleteCutVideo($cutVideo);
        }
    }

    public function deleteCutVideoOfSolution(Solution $solution): void
    {
        $cutVideo = $this->getCutVideoOfSolution($solution);
        if ($cutVideo !== null) {
            $this->deleteDirectoryOfCutVideo($cutVideo);
            $this->cutVideoRepository->deleteCutVideo($cutVideo);
        }
    }

    public function deleteDirectoryOfCutVideo(CutVideo $cutVideo): void
    {
        $this->fileSystemService->deleteDirectory($cutVideo->getEncodedVideoDirectory());
    }

    public function createCutVideoForVideoAndSolution(Video $originalVideo, Solution $solution): CutVideo
    {
        $cutVideo = new CutVideo($originalVideo, $solution);

        $this->cutVideoRepository->add($cutVideo);

        return $cutVideo;
    }

    public function getCutVideoOfSolution(Solution $solution): CutVideo|null
    {
        $qb = $this->cutVideoRepository->createQueryBuilder('cutVideo')
            ->where('cutVideo.solution = :solution')
            ->setParameter('solution', $solution);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
