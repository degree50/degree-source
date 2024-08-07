<?php

namespace App\Domain\Exercise\Model;

use App\Domain\Course\Model\Course;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// Lifecycle callbacks are used to automatically set the createdAt value
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class Exercise
{
    use IdentityTrait;

    const int EXERCISE_CREATED = 0;
    const int EXERCISE_FINISHED = 1;
    const int EXERCISE_PUBLISHED = 2;

    #[Assert\NotBlank]
    #[ORM\Column]
    public string $name = '';

    /**
     * NOTE: the assertion is currently necessary as a validation workaround for our CKEditor-Formtype
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: "text")]
    public string $description = '';

    /**
     * @var Collection<ExercisePhase>
     */
    #[ORM\OneToMany(targetEntity: ExercisePhase::class, mappedBy: "belongsToExercise", cascade: ["all"], orphanRemoval: true)]
    #[ORM\OrderBy(["sorting" => "ASC"])]
    private Collection $phases;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: "exercises")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Course $course;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "createdExercises")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $creator;

    #[ORM\Column(name: "created_at", type: "datetimetz_immutable")]
    private ?DateTimeImmutable $createdAt;

    /**
     * 0 = created
     * 1 = finished
     * 2 = published
     */
    #[ORM\Column(type: "integer")]
    private int $status = self::EXERCISE_CREATED;

    public function __construct(string $id = null)
    {
        $this->phases = new ArrayCollection();
        $this->generateOrSetId($id);
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getPhases(): Collection
    {
        return $this->phases;
    }

    public function setPhases(Collection $phases): void
    {
        foreach ($phases as $phase) {
            $phase->belongsToExercise = $this;
        }
        $this->phases = $phases;
    }

    public function addPhase(ExercisePhase $exercisePhase): void
    {
        $exercisePhase->belongsToExercise = $this;
        $exercisePhase->sorting = $this->phases->count();
        $this->phases->add($exercisePhase);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Note that the parameter has to be optional, so that null can be passed to
     * it, whenever we use this entity in symphony forms, because validation
     * happens AFTER the form has been processed.
     * This is currently necessary as a validation workaround for our CKEditor-FormType (see ExerciseType.php)
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description ?? '';

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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getPhaseAtSortingPosition(int $position): ?ExercisePhase
    {
        return $this
            ->getPhases()
            ->filter(
                function (ExercisePhase $exercisePhase) use ($position) {
                    return $exercisePhase->getSorting() === $position;
                }
            )
            ->first();
    }
}
