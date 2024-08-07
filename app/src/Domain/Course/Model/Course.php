<?php

namespace App\Domain\Course\Model;

use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Fachbereich\Model\Fachbereich;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Course
{
    use IdentityTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public string $name = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: "datetime")]
    public DateTime $creationDate;

    /**
     * @var Collection<Exercise>
     */
    #[ORM\OneToMany(targetEntity: Exercise::class, mappedBy: "course", cascade: ["all"], orphanRemoval: true)]
    private Collection $exercises;

    /**
     * @var Collection<CourseRole>
     */
    #[ORM\OneToMany(targetEntity: CourseRole::class, mappedBy: "course", cascade: ["all"], orphanRemoval: true)]
    private Collection $courseRoles;

    /**
     * @var Collection<Video>
     */
    #[ORM\ManyToMany(targetEntity: Video::class, mappedBy: "courses")]
    private Collection $videos;

    #[ORM\ManyToOne(targetEntity: Fachbereich::class)]
    private ?Fachbereich $fachbereich;

    public function __construct($id = null)
    {
        $this->generateOrSetId($id);
        $this->courseRoles = new ArrayCollection();
        $this->exercises = new ArrayCollection();
        $this->creationDate = new DateTime();
        $this->videos = new ArrayCollection();
    }

    /**
     * @return Collection<CourseRole>
     */
    public function getCourseRoles(): Collection
    {
        return $this->courseRoles;
    }

    public function addCourseRole(CourseRole $courseRole): self
    {
        if (!$this->courseRoles->contains($courseRole)) {
            $this->courseRoles[] = $courseRole;
            $courseRole->setCourse($this);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function getCreationDateYear(): int
    {
        return $this->creationDate->format('Y');
    }

    /**
     * @return Collection<Exercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }

    public function addExercise(Exercise $exercise): self
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises[] = $exercise;
            $exercise->setCourse($this);
        }

        return $this;
    }

    /**
     * @return Collection<Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->addCourse($this);
        }

        return $this;
    }

    /**
     * @return Fachbereich|null
     */
    public function getFachbereich(): ?Fachbereich
    {
        return $this->fachbereich;
    }

    /**
     * @param Fachbereich|null $fachbereich
     */
    public function setFachbereich(?Fachbereich $fachbereich): void
    {
        $this->fachbereich = $fachbereich;
    }

    /**
     * @return Collection<User>
     */
    public function getDozents(): Collection
    {
        return $this->courseRoles
            ->filter(function (CourseRole $courseRole) {
                return $courseRole->isCourseDozent() && $courseRole->getUser()->isDozent();
            })
            ->map(function (CourseRole $courseRole) {
                return $courseRole->getUser();
            });
    }
}
