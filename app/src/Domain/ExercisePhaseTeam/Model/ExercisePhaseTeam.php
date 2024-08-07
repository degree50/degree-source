<?php

namespace App\Domain\ExercisePhaseTeam\Model;

use App\Domain\AutosavedSolution\Model\AutosavedSolution;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\Solution\Model\Solution;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ExercisePhaseTeam
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: ExercisePhase::class, inversedBy: "teams")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ExercisePhase $exercisePhase;

    #[ORM\OneToOne(targetEntity: Solution::class, mappedBy: "exercisePhaseTeam", cascade: ["remove"], orphanRemoval: true)]
    private ?Solution $solution = null;

    /**
     * @var Collection<User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $currentEditor;

    /**
     * @var Collection<AutosavedSolution>
     */
    #[ORM\OneToMany(targetEntity: AutosavedSolution::class, mappedBy: "team", cascade: ["remove"], orphanRemoval: true)]
    private Collection $autosavedSolutions;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $creator;

    #[ORM\Column(type: "string", enumType: ExercisePhaseStatus::class)]
    private ExercisePhaseStatus $status = ExercisePhaseStatus::IN_BEARBEITUNG;

    #[ORM\Column(name: "phase_last_opened_at", type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $phaseLastOpenedAt = null;

    #[ORM\Column(type: "boolean")]
    private bool $isTest = false;

    public function __construct(?string $id = null)
    {
        $this->members = new ArrayCollection();
        $this->autosavedSolutions = new ArrayCollection();
        $this->generateOrSetId($id);
    }

    public function getExercisePhase(): ?ExercisePhase
    {
        return $this->exercisePhase;
    }

    public function setExercisePhase(?ExercisePhase $exercisePhase): self
    {
        $this->exercisePhase = $exercisePhase;
        $exercisePhase->addTeam($this);

        return $this;
    }

    public function getSolution(): ?Solution
    {
        return $this->solution;
    }

    public function setSolution(?Solution $solution): self
    {
        $this->solution = $solution;

        return $this;
    }

    public function hasSolution(): bool
    {
        return $this->solution != null;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);

            // if $member is the creator -> set next member to be creator
            if ($member === $this->getCreator()) {
                $this->setCreator($this->members->first());
            }
        }

        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCurrentEditor(): ?User
    {
        return $this->currentEditor;
    }

    public function setCurrentEditor(?User $currentEditor): self
    {
        $this->currentEditor = $currentEditor;

        return $this;
    }

    /**
     * @return Collection<AutosavedSolution>
     */
    public function getAutosavedSolutions(): Collection
    {
        return $this->autosavedSolutions;
    }

    public function addAutosavedSolution(AutosavedSolution $autosavedSolution): self
    {
        if (!$this->autosavedSolutions->contains($autosavedSolution)) {
            $this->autosavedSolutions[] = $autosavedSolution;
            $autosavedSolution->setTeam($this);
        }

        return $this;
    }

    public function removeAutosavedSolution(AutosavedSolution $autosavedSolution): self
    {
        if ($this->autosavedSolutions->contains($autosavedSolution)) {
            $this->autosavedSolutions->removeElement($autosavedSolution);
        }

        return $this;
    }

    public function getStatus(): ExercisePhaseStatus
    {
        return $this->status;
    }

    public function setStatus(ExercisePhaseStatus $status): void
    {
        $this->status = $status;
    }

    public function hasAdminOrDozentMember(): bool
    {
        return $this->getMembers()->exists(
            function ($_key, User $member) {
                return $member->isDozent() || $member->isAdmin();
            }
        );
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function getPhaseLastOpenedAt(): ?DateTimeImmutable
    {
        return $this->phaseLastOpenedAt;
    }

    public function setPhaseLastOpenedAt($phaseLastOpenedAt): void
    {
        $this->phaseLastOpenedAt = $phaseLastOpenedAt;
    }

    public function isTest(): bool
    {
        return $this->isTest;
    }

    public function setIsTest(bool $isTest): void
    {
        $this->isTest = $isTest;
    }
}
