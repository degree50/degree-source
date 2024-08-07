<?php

namespace App\Domain\Video\Model;

use App\Domain\Course\Model\Course;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideCutVideo;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideVideoUrl;
use App\Domain\User\Model\User;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\Twig\AppRuntime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Video
{
    use IdentityTrait;

    const int ENCODING_NOT_STARTED = 0;
    const int ENCODING_STARTED = 2;
    const int ENCODING_ERROR = 3;
    const int ENCODING_FINISHED = 1;

    #[ORM\Column(type: "text")]
    private string $title = '';

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = '';

    // NOTE
    // The VirtualizedFiles below need to be marked as optional, so that we can set
    // them as NULL and write NULL values to the database (e.g. if no subtitles have been uploaded).
    //
    // However they will never be NULL when we retrieve them from the database, because
    // doctrine will always initialize them as VirtualizedFile class (because of the "Embedded" annotation),
    // no matter if their column is set to NULL inside the database or not.
    //
    // This could lead to weird situations, where one would expect to receive NULL
    // by the getters in this class, but the getters still return a VirtualizedFil.
    // (which also means, that it is not possible to successfully use is_null() or empty() directly on the getter results)
    //
    // To check if you actually have some kind of value you should rely on the methods
    // defined on VirtualizedFile itself and e.g. check their return values for NULL etc.
    // For example if $uploadedVideoFile would be NULL inside the database,
    // its respective VirtualizedFile->getVirtualPathAndFilename() would also return NULL.

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $uploadedVideoFile;

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $uploadedSubtitleFile;

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $uploadedAudioDescriptionFile;

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $encodedVideoDirectory;

    /**
     * @var Collection<ExercisePhase>
     */
    #[ORM\ManyToMany(targetEntity: ExercisePhase::class, mappedBy: "videos")]
    private Collection $exercisePhases;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "createdVideos")]
    #[ORM\JoinColumn(nullable: false)]
    private User $creator;

    #[ORM\Column(name: "created_at", type: "datetimetz_immutable")]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<Course>
     */
    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: "videos")]
    private Collection $courses;

    #[ORM\Column(type: "boolean")]
    private ?bool $dataPrivacyAccepted;

    #[ORM\Column(type: "boolean")]
    private ?bool $dataPrivacyPermissionsAccepted;

    /**
     * 0 = not started
     * 1 = finished
     * 2 = started
     * 3 = error
     */
    #[ORM\Column(type: "integer")]
    private int $encodingStatus = self::ENCODING_NOT_STARTED;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $videoDuration = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $personalNotes = null;

    #[ORM\Column(type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $encodingStarted = null;

    #[ORM\Column(type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $encodingFinished = null;

    public function __construct(string $id = '')
    {
        $this->generateOrSetId($id);
        $this->exercisePhases = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getUploadedVideoFile(): ?VirtualizedFile
    {
        return $this->uploadedVideoFile;
    }

    public function setUploadedVideoFile(?VirtualizedFile $uploadedVideoFile): void
    {
        $this->uploadedVideoFile = $uploadedVideoFile;
    }

    /**
     * @return Collection<ExercisePhase>
     */
    public function getExercisePhases(): Collection
    {
        return $this->exercisePhases;
    }

    public function addExercisePhase(ExercisePhase $exercisePhase): self
    {
        if (!$this->exercisePhases->contains($exercisePhase)) {
            $this->exercisePhases[] = $exercisePhase;
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

    /**
     * @return Collection<Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->contains($course)) {
            $this->courses->removeElement($course);
        }

        return $this;
    }

    public function getDataPrivacyAccepted(): ?bool
    {
        return $this->dataPrivacyAccepted;
    }

    public function setDataPrivacyAccepted(?bool $dataPrivacyAccepted): self
    {
        $this->dataPrivacyAccepted = $dataPrivacyAccepted;

        return $this;
    }

    public function getVideoDuration(): float
    {
        // if the video has not yet been encoded, we do not yet have a duration
        return $this->videoDuration ?? 0;
    }

    public function setVideoDuration(?float $videoDuration): void
    {
        $this->videoDuration = $videoDuration;
    }

    public function getEncodingStatus(): int
    {
        return $this->encodingStatus;
    }

    public function setEncodingStatus(int $encodingStatus): void
    {
        switch ($encodingStatus) {
            case self::ENCODING_STARTED:
                $this->encodingStarted = new DateTimeImmutable();
                break;
            case self::ENCODING_FINISHED:
                $this->encodingFinished = new DateTimeImmutable();
                break;
        }

        $this->encodingStatus = $encodingStatus;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Used for testing purposes only.
     *
     * @param DateTimeImmutable $createdAt
     * @return void
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAsClientSideVideo(AppRuntime $appRuntime): ClientSideCutVideo
    {
        $videoUrl = $appRuntime->virtualizedFileUrl($this->getEncodedVideoDirectory());

        return ClientSideCutVideo::fromVideoEntity(
            $this,
            ClientSideVideoUrl::fromBaseUrl($videoUrl)
        );
    }

    public function getEncodedVideoDirectory(): ?VirtualizedFile
    {
        return $this->encodedVideoDirectory;
    }

    public function setEncodedVideoDirectory(?VirtualizedFile $encodedVideoDirectory): void
    {
        $this->encodedVideoDirectory = $encodedVideoDirectory;
    }

    public function getPersonalNotes(): ?string
    {
        return $this->personalNotes;
    }

    public function setPersonalNotes(?string $personalNotes): self
    {
        $this->personalNotes = $personalNotes;

        return $this;
    }

    public function getEncodingStarted(): ?DateTimeImmutable
    {
        return $this->encodingStarted;
    }

    public function getEncodingFinished(): ?DateTimeImmutable
    {
        return $this->encodingFinished;
    }

    public function getDataPrivacyPermissionsAccepted(): ?bool
    {
        return $this->dataPrivacyPermissionsAccepted;
    }

    public function setDataPrivacyPermissionsAccepted($dataPrivacyPermissionsAccepted): void
    {
        $this->dataPrivacyPermissionsAccepted = $dataPrivacyPermissionsAccepted;
    }

    public function getUploadedSubtitleFile(): VirtualizedFile
    {
        return $this->uploadedSubtitleFile;
    }

    public function setUploadedSubtitleFile(?VirtualizedFile $uploadedSubtitleFile): void
    {
        $this->uploadedSubtitleFile = $uploadedSubtitleFile;
    }

    public function getUploadedAudioDescriptionFile(): VirtualizedFile
    {
        return $this->uploadedAudioDescriptionFile;
    }

    public function setUploadedAudioDescriptionFile(?VirtualizedFile $uploadedAudioDescriptionFile): void
    {
        $this->uploadedAudioDescriptionFile = $uploadedAudioDescriptionFile;
    }
}
