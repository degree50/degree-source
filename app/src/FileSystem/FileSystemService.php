<?php

namespace App\FileSystem;

use App\DependencyInjection\Compiler\FileSystemCompilerPass;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class FileSystemService
{
    const string LOCAL_TMP_FILESYSTEM_MOUNT = 'local_tmp';

    // Name of file system mount which is guaranteed to be locally available,
    // because it is defined inside oneup_flysystem.yaml.
    private array $mountPrefixesAndFilesystems;

    public function __construct(
        private readonly MountManager $mountManager,
    )
    {
        $this->mountPrefixesAndFilesystems = [];
    }

    /**
     * This method is called from {@see FileSystemCompilerPass}
     *
     * @param Filesystem $filesystem
     * @param string $mountPrefix
     * @internal
     */
    public function registerFilesystem(Filesystem $filesystem, string $mountPrefix, string $pathPrefix): void
    {
        $this->mountPrefixesAndFilesystems[$mountPrefix] = [
            'fileSystem' => $filesystem,
            'pathPrefix' => $pathPrefix,
        ];
    }

    /**
     * Public API, to reverse-resolve the prefix for a file system.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    public function getMountPrefixForFilesystem(Filesystem $filesystem): string
    {
        $result = false;

        foreach ($this->mountPrefixesAndFilesystems as $mountPrefix => $filesystemData) {
            if ($filesystemData['fileSystem'] === $filesystem) {
                $result = $mountPrefix;
                break;
            }
        }

        if ($result === false) {
            throw new RuntimeException('given file system does not have a mount prefix assigned');
        }

        return $result;
    }

    public function generateUniqueTemporaryDirectory(): VirtualizedFile
    {
        $directory = VirtualizedFile::fromMountPointAndFilename(self::LOCAL_TMP_FILESYSTEM_MOUNT, Uuid::uuid4()->toString());
        $this->mountManager->createDirectory($directory->getVirtualPathAndFilename());
        assert($this->mountManager->directoryExists($directory->getVirtualPathAndFilename()), 'directory ' . $directory . ' could not be created');
        return $directory;
    }

    public function localPath(VirtualizedFile $inputFile): string
    {
        return $this->applyLocationPrefixOfMountPoint($inputFile->getMountPoint(), $inputFile->getRelativePathAndFilename());
    }

    public function moveDirectory(VirtualizedFile $inputDirectory, VirtualizedFile $outputDirectory): void
    {
        foreach ($this->mountManager->listContents($inputDirectory->getVirtualPathAndFilename(), MountManager::LIST_DEEP) as $file) {
            /* @var $file FileAttributes */
            $this->mountManager->move($file->path(), $outputDirectory->getVirtualPathAndFilename() . '/' . basename($file->path()));
        }
    }

    public function deleteFile(VirtualizedFile $file): void
    {
        if (
            $file->hasFile()
            && $this->mountManager->fileExists($file->getVirtualPathAndFilename())
        ) {
            $this->mountManager->delete($file->getVirtualPathAndFilename());
        }
    }

    public function deleteDirectory(VirtualizedFile $directory): void
    {
        if (
            $directory->hasFile()
            && $this->mountManager->directoryExists($directory->getVirtualPathAndFilename())
        ) {
            $this->mountManager->deleteDirectory($directory->getVirtualPathAndFilename());
        }
    }

    private function applyLocationPrefixOfMountPoint(string $mountPoint, string $path): string
    {
        $pathPrefix = $this->mountPrefixesAndFilesystems[$mountPoint]['pathPrefix'];

        return $pathPrefix . $path;
    }
}
