<?php

namespace App\Domain\ExercisePhase\Model;

/**
 * These values are used all over the place. As keys for translations, in frontend, everywhere.
 */
enum ExercisePhaseType: string
{
    case VIDEO_ANALYSIS = 'videoAnalysis';
    case VIDEO_CUT = 'videoCutting';
    case REFLEXION = 'reflexion';
    case MATERIAL = 'material';

    /**
     * @return string[]
     */
    public static function getPossibleValues(): array
    {
        return array_map(
            function ($exercisePhaseType) {
                return $exercisePhaseType->value;
            },
            ExercisePhaseType::cases()
        );
    }
}
