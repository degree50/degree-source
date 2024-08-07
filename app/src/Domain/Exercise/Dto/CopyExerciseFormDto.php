<?php

namespace App\Domain\Exercise\Dto;

use App\Domain\Course\Model\Course;
use App\Domain\Exercise\Model\Exercise;

class CopyExerciseFormDto
{
    private function __construct(
        public Course $course,
        public bool   $copyPhases
    )
    {
    }

    public static function fromExercise(Exercise $exercise): CopyExerciseFormDto
    {
        // copyPhases is true by default and will be set by CopyExerciseFormType
        return new self($exercise->getCourse(), true);
    }
}
