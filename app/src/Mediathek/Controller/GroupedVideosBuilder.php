<?php

namespace App\Mediathek\Controller;

use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Mediathek\Dto\VideoWithFavoriteStatusDto;

class GroupedVideosBuilder
{
    private User $user;
    private array $ownVideos = [];
    private array $otherVideos = [];

    public function __construct(private readonly VideoFavouritesService $videoFavouritesService)
    {
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function addOwnVideo(Video $video): static
    {
        $this->ownVideos[] = new VideoWithFavoriteStatusDto(
            $video,
            $this->videoFavouritesService->videoIsFavorite($video, $this->user),
        );

        return $this;
    }

    public function addOtherVideo(Video $video): static
    {
        $this->otherVideos[] = new VideoWithFavoriteStatusDto(
            $video,
            $this->videoFavouritesService->videoIsFavorite($video, $this->user),
        );

        return $this;
    }

    public function create(): array
    {
        return [
            [
                'id' => 'ownVideos',
                'videos' => $this->ownVideos
            ],
            [
                'id' => 'otherVideos',
                'videos' => $this->otherVideos
            ]
        ];
    }
}
