<?php

namespace App\Domain\Exercise\Model;

/**
 * The Status of an Exercise
 */
enum ExerciseStatus: string
{
    case NEU = 'NEU';
    case IN_BEARBEITUNG = 'IN_BEARBEITUNG';
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
            ExerciseStatus::cases()
        );
    }
}
