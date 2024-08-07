<?php

namespace App\Domain\ExercisePhase\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ReflexionPhase extends ExercisePhase
{
    const ?ExercisePhaseType type = ExercisePhaseType::REFLEXION;

    public function __construct(string $id = null)
    {
        parent::__construct($id);

        // ReflexionPhase is always a group phase
        $this->setIsGroupPhase(true);
    }
}
