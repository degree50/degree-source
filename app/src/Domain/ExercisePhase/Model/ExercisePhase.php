<?php

namespace App\Domain\ExercisePhase\Model;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes a single Phase of an exercise (e.g. a "Video-Analysis" or "Video-Cutting").
 * An exercisePhase might depend on a previous exercise if that is the case, the previous
 * phase is determined by the next lower sorting of the phases.
 *
 * If "otherSolutionsAreAccessible" it is possible for "studierende" to display the solutions
 * of other "studierende" for the phase.
 */
#[ORM\Entity]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "phaseType", type: "string")]
#[ORM\DiscriminatorMap([
    "videoAnalysisPhase" => VideoAnalysisPhase::class,
    "videoCutPhase" => VideoCutPhase::class,
    "reflexionPhase" => ReflexionPhase::class,
    "materialPhase" => MaterialPhase::class,
])]
abstract class ExercisePhase
{
    use IdentityTrait;

    const ?ExercisePhaseType type = null;

    // components for phases
    // "Components" for ExercisePhases is an old concept/approach that is rarely used anymore.
    const string VIDEO_CODE = 'videoCode';
    const string VIDEO_CUTTING = 'videoCutting';
    const string VIDEO_ANNOTATION = 'videoAnnotation';

    #[ORM\Column(type: "boolean")]
    public bool $isGroupPhase = false;

    #[Assert\NotBlank]
    #[ORM\Column]
    public string $name = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: "text")]
    public string $task = '';

    #[ORM\ManyToOne(targetEntity: Exercise::class, inversedBy: "phases")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    public Exercise $belongsToExercise;

    #[ORM\Column]
    public int $sorting;

    /**
     * @var Collection<ExercisePhaseTeam>
     */
    #[ORM\OneToMany(targetEntity: ExercisePhaseTeam::class, mappedBy: "exercisePhase", cascade: ["all"])]
    private Collection $teams;

    /**
     * @var Collection<Attachment>
     */
    #[ORM\OneToMany(targetEntity: Attachment::class, mappedBy: "exercisePhase", cascade: ["all"], orphanRemoval: true)]
    #[ORM\OrderBy(["uploadAt" => "DESC"])]
    private Collection $attachments;

    /**
     * @var Collection<Video>
     */
    #[ORM\ManyToMany(targetEntity: Video::class, inversedBy: "exercisePhases")]
    private Collection $videos;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?ExercisePhase $dependsOnExercisePhase = null;

    #[ORM\Column(type: "boolean")]
    private bool $otherSolutionsAreAccessible = false;

    /**
     * @var Collection<ExercisePhase>
     */
    private Collection $phasesDependentOnThis;

    protected function __construct(string $id = null)
    {
        $this->generateOrSetId($id);
        $this->teams = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->phasesDependentOnThis = new ArrayCollection();
        $this->isGroupPhase = false;
    }

    public static function byType(ExercisePhaseType $type, string $id = null): VideoAnalysisPhase|VideoCutPhase|ReflexionPhase|MaterialPhase
    {
        return match ($type) {
            ExercisePhaseType::VIDEO_ANALYSIS => new VideoAnalysisPhase($id),
            ExercisePhaseType::VIDEO_CUT => new VideoCutPhase($id),
            ExercisePhaseType::REFLEXION => new ReflexionPhase($id),
            ExercisePhaseType::MATERIAL => new MaterialPhase($id),
        };
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * NOTE: Runtime only
     * @return ExercisePhaseType
     */
    public function getType(): ExercisePhaseType
    {
        return $this::type;
    }

    public function addTeam(ExercisePhaseTeam $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setExercisePhase($this);
        }

        return $this;
    }

    public function removeTeam(ExercisePhaseTeam $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            // set the owning side to null (unless already changed)
            if ($team->getExercisePhase() === $this) {
                $team->setExercisePhase(null);
            }
        }

        return $this;
    }

    public function getBelongsToExercise(): Exercise
    {
        return $this->belongsToExercise;
    }

    public function setBelongsToExercise(Exercise $belongsToExercise): void
    {
        $this->belongsToExercise = $belongsToExercise;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * Note that the parameter has to be optional, so that null can be passed to
     * it, whenever we use this entity in symphony forms, because validation
     * happens AFTER the form has been processed.
     * This is currently necessary as a validation workaround for our CKEditor-FormType (see ExercisePhaseFormType.php)
     */
    public function setTask(?string $task): void
    {
        $this->task = $task ?? '';
    }

    public function isGroupPhase(): bool
    {
        return $this->isGroupPhase;
    }

    public function setIsGroupPhase(bool $isGroupPhase): void
    {
        $this->isGroupPhase = $isGroupPhase;
    }

    public function addAttachment(Attachment $attachment): self
    {
        $this->attachments->add($attachment);
        $attachment->setExercisePhase($this);
        return $this;
    }

    public function removeAttachment(Attachment $attachment): void
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * @return Collection<Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
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
            // Also add the phase to the video.
            // Persisting of the video is done by symfony.
            $video->addExercisePhase($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
        }

        return $this;
    }

    public function getDependsOnExercisePhase(): ?ExercisePhase
    {
        return $this->dependsOnExercisePhase;
    }

    public function setDependsOnExercisePhase(?ExercisePhase $exercisePhase): self
    {
        $this->dependsOnExercisePhase = $exercisePhase;

        return $this;
    }

    public function getOtherSolutionsAreAccessible(): ?bool
    {
        return $this->otherSolutionsAreAccessible;
    }

    public function setOtherSolutionsAreAccessible(bool $otherSolutionsAreAccessible): self
    {
        $this->otherSolutionsAreAccessible = $otherSolutionsAreAccessible;

        return $this;
    }

    public function getHasSolutions(): bool
    {
        $teams = $this->getTeams()->toArray();
        $solutionsWithoutTestSolution = array_filter($teams, fn(ExercisePhaseTeam $team) => !$team->isTest());
        return count($solutionsWithoutTestSolution) > 0;
    }

    /*
     * Returns boolean if the phase has a solution.
     * Excludes the test solution.
     *
     * @return bool
     */

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    /**
     * Check if a Solution exists on a Team that the user is a member of.
     * Excludes the test solution.
     */
    public function getHasSolutionForUser(User $user): bool
    {
        return $this
            ->getTeams()
            ->exists(
                function ($i, ExercisePhaseTeam $exercisePhaseTeam) use ($user) {
                    return !$exercisePhaseTeam->isTest() && $exercisePhaseTeam->hasSolution() && $exercisePhaseTeam->getMembers()->contains($user);
                }
            );
    }
}
