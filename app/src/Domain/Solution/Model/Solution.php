<?php

namespace App\Domain\Solution\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideSolutionData;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Solution
{
    use IdentityTrait;

    #[ORM\OneToOne(targetEntity: ExercisePhaseTeam::class, inversedBy: "solution")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ExercisePhaseTeam $exercisePhaseTeam;

    #[ORM\Column(type: "json")]
    private array $solution;

    #[ORM\Column(type: "datetimetz_immutable")]
    private DateTimeImmutable $update_timestamp;

    public function __construct(ExercisePhaseTeam $exercisePhaseTeam, string $id = null, string $initialMaterial = null)
    {
        $this->generateOrSetId($id);
        $this->exercisePhaseTeam = $exercisePhaseTeam;
        // empty initial solution
        $this->solution = [
            'annotations' => [],
            'videoCodes' => [],
            'cutList' => [],
            'material' => $initialMaterial,
        ];
        $this->update_timestamp = new DateTimeImmutable();
    }

    public function getExercisePhaseTeam(): ExercisePhaseTeam
    {
        return $this->exercisePhaseTeam;
    }

    public function getSolution(): ?ServerSideSolutionData
    {
        return ServerSideSolutionData::fromArray($this->solution);
    }

    public function setSolution(ServerSideSolutionData $solutionData): self
    {
        $this->solution = $solutionData->toArray();

        return $this;
    }

    public function getUpdateTimestamp(): DateTimeImmutable
    {
        return $this->update_timestamp;
    }

    public function setUpdateTimestamp(DateTimeImmutable $update_timestamp): void
    {
        $this->update_timestamp = $update_timestamp;
    }

    public function __toString(): string
    {
        return "Solution[$this->id()]";
    }
}
