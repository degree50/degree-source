<?php

namespace App\Mediathek\Dto;

use App\Domain\Video\Model\Video;

/**
 * Dto which contains video as well as its favorite status.
 */
readonly class VideoWithFavoriteStatusDto
{
    public function __construct(
        public Video $video,
        public bool  $isFavorite
    )
    {
    }
}
