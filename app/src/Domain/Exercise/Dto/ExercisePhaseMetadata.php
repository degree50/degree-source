<?php

namespace App\Domain\Exercise\Dto;

readonly class ExercisePhaseMetadata
{
    public function __construct(
        public bool   $needsReview,
        public bool   $isDone,
        public string $phaseTitle,
        public string $iconClass
    )
    {
    }

    public function toArray(): array
    {
        return [
            'needsReview' => $this->needsReview,
            'isDone' => $this->isDone,
            'phaseTitle' => $this->phaseTitle,
            'iconClass' => $this->iconClass
        ];
    }
}
