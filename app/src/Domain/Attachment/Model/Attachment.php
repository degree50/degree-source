<?php

namespace App\Domain\Attachment\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\User\Model\User;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Attachment
 *
 * @Vich\Uploadable
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Attachment
{
    use IdentityTrait;

    #[ORM\Column(name: "name", type: "string", length: 255)]
    private string $name = "";

    #[ORM\Embedded(class: VirtualizedFile::class)]
    private ?VirtualizedFile $uploadedFile;

    #[ORM\Column(name: "mime_type", type: "string", length: 255)]
    private string $mimeType;

    #[ORM\Column(name: "upload_at", type: "datetimetz_immutable")]
    private ?DateTimeImmutable $uploadAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "createdAttachments")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $creator;

    #[ORM\ManyToOne(targetEntity: ExercisePhase::class, inversedBy: "attachments")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ExercisePhase $exercisePhase;

    /**
     * Attachment constructor.
     */
    public function __construct($id = null)
    {
        $this->generateOrSetId($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[ORM\PrePersist]
    public function setUploadedAtValue(): void
    {
        $this->uploadAt = new DateTimeImmutable();
    }

    public function getUploadAt(): ?DateTimeImmutable
    {
        return $this->uploadAt;
    }

    public function getExercisePhase(): ExercisePhase
    {
        return $this->exercisePhase;
    }

    public function setExercisePhase(ExercisePhase $exercisePhase): void
    {
        $this->exercisePhase = $exercisePhase;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): void
    {
        $this->creator = $creator;
    }

    public function getUploadedFile(): ?VirtualizedFile
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(VirtualizedFile $uploadedFile): void
    {
        $this->uploadedFile = $uploadedFile;
    }
}
