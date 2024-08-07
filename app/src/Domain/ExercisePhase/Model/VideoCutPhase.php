<?php

namespace App\Domain\ExercisePhase\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class VideoCutPhase extends ExercisePhase
{
    const ?ExercisePhaseType type = ExercisePhaseType::VIDEO_CUT;

    const array PHASE_COMPONENTS_GROUP = [];

    public function __construct(string $id = null)
    {
        parent::__construct($id);
    }
}
