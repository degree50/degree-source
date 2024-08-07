<?php

namespace App\Domain\VirtualizedFile\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class VirtualizedFile
{
    /**
     * file name in the form "storage_prefix://filename"
     */
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $virtualPathAndFilename;

    public static function fromString(string $virtualPathAndFilename): self
    {
        $file = new self();
        $file->virtualPathAndFilename = $virtualPathAndFilename;
        return $file;
    }

    public static function fromMountPointAndFilename(string $mountPoint, string $filename): self
    {
        $file = new self();
        $file->virtualPathAndFilename = $mountPoint . '://' . $filename;
        return $file;
    }

    public function getVirtualPathAndFilename(): ?string
    {
        return $this->virtualPathAndFilename;
    }

    public function getMountPoint(): string
    {
        [$mountPoint,] = explode('://', $this->virtualPathAndFilename, 2);
        return $mountPoint;
    }

    public function getRelativePathAndFilename(): string
    {
        [, $relativePathAndFilename] = explode('://', $this->virtualPathAndFilename, 2);
        return $relativePathAndFilename;
    }

    public function getBasename(): string
    {
        return basename($this->virtualPathAndFilename);
    }

    public function appendPathSegment(string $newPathSegment): self
    {
        return VirtualizedFile::fromString($this->virtualPathAndFilename . '/' . $newPathSegment);
    }

    public function __toString()
    {
        return 'VirtualizedFile[' . $this->getVirtualPathAndFilename() . ']';
    }

    public function hasFile(): bool
    {
        return $this->virtualPathAndFilename !== null;
    }
}
