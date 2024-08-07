<?php

namespace App\Domain\ExercisePhase\Model;

/**
 * The Status of an ExercisePhase
 */
enum ExercisePhaseStatus: string
{
    case INITIAL = 'INITIAL';
    case IN_BEARBEITUNG = 'IN_BEARBEITUNG';
    case IN_REVIEW = 'IN_REVIEW';
    case BEENDET = 'BEENDET';

    /**
     * @return string[]
     */
    public static function getPossibleValues(): array
    {
        return array_map(
            function ($case) {
                return $case->value;
            },
            ExercisePhaseStatus::cases()
        );
    }
}
