<?php

namespace App\Domain\VideoFavorite\Service;

use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\VideoFavorite\Model\VideoFavorite;
use App\Domain\VideoFavorite\Repository\VideoFavoritesRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class VideoFavouritesService
{
    public function __construct(
        private EntityManagerInterface   $entityManager,
        private VideoFavoritesRepository $videoFavoritesRepository,
    )
    {
    }

    public function videoIsFavorite(Video $video, User $user): bool
    {
        $maybeFavorite = $this->videoFavoritesRepository->findOneByUserAndVideo($user, $video);

        return !is_null($maybeFavorite);
    }

    public function toggleFavorite(Video $video, User $user): void
    {
        $maybeFavorite = $this->videoFavoritesRepository->findOneByUserAndVideo($user, $video);

        if (!is_null($maybeFavorite)) {
            $this->removeVideoFavorite($maybeFavorite);
        } else {
            $this->addVideoFavouriteForUser($video, $user);
        }
    }

    public function deleteFavoriteVideosByUser(User $user): void
    {
        $videoFavorites = $this->videoFavoritesRepository->findByUser($user);

        foreach ($videoFavorites as $videoFavorite) {
            $this->removeVideoFavorite($videoFavorite);
        }
    }

    public function removeVideoFavorite(VideoFavorite $videoFavorite): void
    {
        $this->entityManager->remove($videoFavorite);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @return Video[]
     */
    public function getFavouriteVideosForUser(User $user): array
    {
        return $this->videoFavoritesRepository->findByUser($user);
    }

    /**
     * @param Video $video
     * @param User $user
     */
    public function addVideoFavouriteForUser(Video $video, User $user): void
    {
        $newFavorite = new VideoFavorite($user, $video);

        $this->entityManager->persist($newFavorite);
        $this->entityManager->flush();
    }

    public function removeVideoFavoritesOfVideo(Video $video): void
    {
        $this->videoFavoritesRepository->removeAllWithVideo($video);
    }
}
