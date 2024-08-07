<?php

namespace App\Domain\Exercise\Dto;

use App\Domain\ExercisePhase\Model\ExercisePhase;

readonly class ExercisePhaseWithMetadataDto
{
    public function __construct(
        public ExercisePhase         $phase,
        public ExercisePhaseMetadata $metadata
    )
    {
    }

    public function toArray(): array
    {
        return [
            'phase' => $this->phase,
            'metadata' => $this->metadata->toArray()
        ];
    }
}
