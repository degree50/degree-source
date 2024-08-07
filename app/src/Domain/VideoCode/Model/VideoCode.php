<?php

namespace App\Domain\VideoCode\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use Doctrine\ORM\Mapping as ORM;

// NOTE:
// This actually what we call a 'VideoCodePrototype' not an actual 'videoCode'!!!
// The naming was very misleading in the past. It might make sense to
// change the schema in the future.

#[ORM\Entity]
class VideoCode
{
    use IdentityTrait;

    #[ORM\Column(type: "string", length: 255)]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: VideoAnalysisPhase::class, inversedBy: "videoCodes")]
    #[ORM\JoinColumn(nullable: true)]
    private ?VideoAnalysisPhase $exercisePhase;

    #[ORM\Column(type: "string", length: 7)]
    private string $color = '';

    public function __construct($id = null)
    {
        $this->generateOrSetId($id);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getExercisePhase(): ?VideoAnalysisPhase
    {
        return $this->exercisePhase;
    }

    public function setExercisePhase(?VideoAnalysisPhase $exercisePhase): void
    {
        $this->exercisePhase = $exercisePhase;
    }
}
