<?php

namespace App\Domain\ExercisePhase\Model;

use App\Domain\VideoCode\Model\VideoCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class VideoAnalysisPhase extends ExercisePhase
{
    const ?ExercisePhaseType type = ExercisePhaseType::VIDEO_ANALYSIS;

    /**
     * @var Collection<VideoCode>
     */
    #[ORM\OneToMany(targetEntity: VideoCode::class, mappedBy: "exercisePhase", cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["name" => "ASC"])]
    private Collection $videoCodes;

    #[ORM\Column(type: "boolean")]
    private bool $videoAnnotationsActive = true;

    #[ORM\Column(type: "boolean")]
    private bool $videoCodesActive = false;

    public function __construct(string $id = null)
    {
        parent::__construct($id);

        $this->videoCodes = new ArrayCollection();
    }

    /**
     * @return Collection<VideoCode>
     */
    public function getVideoCodes(): Collection
    {
        return $this->videoCodes;
    }

    public function addVideoCode(VideoCode $videoCode): self
    {
        if (!$this->videoCodes->contains($videoCode)) {
            $this->videoCodes[] = $videoCode;
        }

        return $this;
    }

    public function removeVideoCode(VideoCode $videoCode): self
    {
        if ($this->videoCodes->contains($videoCode)) {
            $this->videoCodes->removeElement($videoCode);
        }

        return $this;
    }

    public function getVideoAnnotationsActive(): ?bool
    {
        return $this->videoAnnotationsActive;
    }

    public function setVideoAnnotationsActive(bool $videoAnnotationsActive): self
    {
        $this->videoAnnotationsActive = $videoAnnotationsActive;

        return $this;
    }

    public function getVideoCodesActive(): ?bool
    {
        return $this->videoCodesActive;
    }

    public function setVideoCodesActive(bool $videoCodesActive): self
    {
        $this->videoCodesActive = $videoCodesActive;

        return $this;
    }
}
