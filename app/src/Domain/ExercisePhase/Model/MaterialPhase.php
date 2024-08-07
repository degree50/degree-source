<?php

namespace App\Domain\ExercisePhase\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MaterialPhase extends ExercisePhase
{
    const ?ExercisePhaseType type = ExercisePhaseType::MATERIAL;

    #[ORM\Column(name: "material", type: "text")]
    private string|null $material = '';

    #[ORM\Column(name: "reviewRequired", type: "boolean")]
    private bool|null $reviewRequired = false;

    public function __construct(string $id = null)
    {
        parent::__construct($id);
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): void
    {
        $this->material = $material;
    }

    public function getReviewRequired(): bool
    {
        if (is_null($this->reviewRequired)) {
            return false;
        }

        return $this->reviewRequired;
    }

    public function setReviewRequired(bool $reviewRequired): void
    {
        $this->reviewRequired = $reviewRequired;
    }
}
