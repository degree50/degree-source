<?php

namespace App\Domain\Exercise\Dto;

use App\Domain\Exercise\Model\Exercise;

/**
 * Dto which contains an exercise as well as its review status.
 */
class ExerciseWithReviewStatusDTO
{
    public function __construct(
        private readonly Exercise $exercise,
        private bool              $needsReview
    )
    {
    }

    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    public function getNeedsReview(): bool
    {
        return $this->needsReview;
    }

    public function setNeedsReview($needsReview): void
    {
        $this->needsReview = $needsReview;
    }
}
