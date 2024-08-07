<?php

namespace App\Domain\Exercise\Dto;

use App\Domain\Exercise\Service\ExerciseService;

class GroupedExercisesBuilder
{
    private array $ownExercises = [];
    private array $otherExercises = [];

    public function __construct(
        private readonly ExerciseService $exerciseService
    )
    {
    }

    public function addOwnExercise($exercise): static
    {
        $this->ownExercises[] = new ExerciseWithReviewStatusDTO(
            $exercise,
            $this->exerciseService->needsReview($exercise)
        );

        return $this;
    }

    public function addOtherExercise($exercise): static
    {
        $this->otherExercises[] = new ExerciseWithReviewStatusDTO(
            $exercise,
            $this->exerciseService->needsReview($exercise)
        );

        return $this;
    }

    public function create(): array
    {
        return [
            [
                'id' => 'ownExercises',
                'exercises' => $this->ownExercises
            ],
            [
                'id' => 'otherExercises',
                'exercises' => $this->otherExercises
            ]
        ];
    }
}
