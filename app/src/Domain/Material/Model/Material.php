<?php

namespace App\Domain\Material\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Material
{
    use IdentityTrait;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private string $material;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "materials")]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\Column(name: "created_at", type: "datetimetz_immutable")]
    private ?DateTimeImmutable $createdAt;

    #[ORM\Column(name: "last_updated_at", type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $lastUpdatedAt = null;

    #[ORM\ManyToOne(targetEntity: ExercisePhaseTeam::class)]
    #[ORM\JoinColumn(name: "original_phase_team", nullable: true, onDelete: "SET NULL")]
    private ?ExercisePhaseTeam $originalPhaseTeam;

    public function __construct(string $id = null)
    {
        $this->generateOrSetId($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): void
    {
        $this->material = $material;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getOriginalPhaseTeam(): ?ExercisePhaseTeam
    {
        return $this->originalPhaseTeam;
    }

    public function setOriginalPhaseTeam(?ExercisePhaseTeam $originalPhaseTeam): void
    {
        $this->originalPhaseTeam = $originalPhaseTeam;

        if ($originalPhaseTeam !== null) {
            $this->name = $this->generateNameFromExercisePhaseTeam($originalPhaseTeam);
        }
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getLastUpdatedAt(): ?DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(?DateTimeImmutable $lastUpdatedAt): void
    {
        $this->lastUpdatedAt = $lastUpdatedAt;
    }

    private function generateNameFromExercisePhaseTeam(ExercisePhaseTeam $exercisePhaseTeam): string
    {
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();
        $exercisePhaseName = $exercisePhase->getName();
        $exercise = $exercisePhase->getBelongsToExercise();
        $exerciseName = $exercise->getName();
        $courseName = $exercise->getCourse()->getName();

        return "$courseName - $exerciseName - $exercisePhaseName";
    }
}
