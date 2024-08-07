<?php

namespace App\Domain\CutVideo\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideCutVideo;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideVideoUrl;
use App\Domain\Solution\Model\Solution;
use App\Domain\Video\Model\Video;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use App\Twig\AppRuntime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CutVideo
{
    use IdentityTrait;

    const int ENCODING_NOT_STARTED = 0;
    const int ENCODING_STARTED = 2;
    const int ENCODING_ERROR = 3;
    const int ENCODING_FINISHED = 1;

    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Video $originalVideo;

    #[ORM\OneToOne(targetEntity: Solution::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Solution $solution;

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $subtitleFile;

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $encodedVideoDirectory;

    #[ORM\Column(name: "created_at", type: "datetimetz_immutable")]
    private ?DateTimeImmutable $createdAt;

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

    #[ORM\Column(type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $encodingStarted = null;

    #[ORM\Column(type: "datetimetz_immutable", nullable: true)]
    private ?DateTimeImmutable $encodingFinished = null;

    public function __construct(Video $originalVideo, Solution $solution, string $id = '')
    {
        $this->generateOrSetId($id);
        $this->originalVideo = $originalVideo;
        $this->solution = $solution;
        $this->createdAt = new DateTimeImmutable();
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

        return ClientSideCutVideo::fromCutVideoEntity(
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

    public function getEncodingStarted(): ?DateTimeImmutable
    {
        return $this->encodingStarted;
    }

    public function getEncodingFinished(): ?DateTimeImmutable
    {
        return $this->encodingFinished;
    }

    public function getSubtitleFile(): VirtualizedFile
    {
        return $this->subtitleFile;
    }

    public function setSubtitleFile(?VirtualizedFile $subtitleFile): void
    {
        $this->subtitleFile = $subtitleFile;
    }

    public function getName(): string
    {
        $exercisePhase = $this->solution->getExercisePhaseTeam()->getExercisePhase();
        $exercise = $exercisePhase->getBelongsToExercise();
        $course = $exercise->getCourse();

        return "Video-Schnitt von {$course->getName()} > {$exercise->getName()} > {$exercisePhase->getName()}";
    }

    public function getVideoDuration(): float
    {
        // if the video is not yet encoded, we can't get the duration
        return $this->videoDuration ?? 0;
    }

    public function setVideoDuration(?float $videoDuration): void
    {
        $this->videoDuration = $videoDuration;
    }

    public function getOriginalVideo(): Video
    {
        return $this->originalVideo;
    }
}
