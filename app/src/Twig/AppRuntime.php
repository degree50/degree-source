<?php

namespace App\Twig;

use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    // Mount points need to be in sync with oneup_flysystem.yaml
    const string UPLOADED_VIDEOS = 'uploaded_videos';
    const string UPLOADED_SUBTITLES = 'uploaded_subtitles';
    const string UPLOADED_AUDIO_DESCRIPTIONS = 'uploaded_audio_descriptions';
    const string ENCODED_VIDEOS = 'encoded_videos';
    const string UPLOADED_ATTACHMENTS = 'uploaded_attachments';

    public function __construct()
    {
        // this simple example doesn't define any dependency, but in your own
        // extensions, you'll need to inject services using this constructor
    }

    public function virtualizedFileUrl(?VirtualizedFile $virtualizedFile): ?string
    {
        if (!$virtualizedFile) {
            return null;
        }

        if ($virtualizedFile->getMountPoint() === self::UPLOADED_VIDEOS) {
            return '/var/data/persistent/videos/original/' . $virtualizedFile->getRelativePathAndFilename();
        }

        if ($virtualizedFile->getMountPoint() === self::UPLOADED_SUBTITLES) {
            return '/var/data/persistent/subtitles/original/' . $virtualizedFile->getRelativePathAndFilename();
        }

        if ($virtualizedFile->getMountPoint() === self::UPLOADED_AUDIO_DESCRIPTIONS) {
            return '/var/data/persistent/audio_descriptions/original/' . $virtualizedFile->getRelativePathAndFilename();
        }

        if ($virtualizedFile->getMountPoint() === self::ENCODED_VIDEOS) {
            return '/data/encoded_videos/' . $virtualizedFile->getRelativePathAndFilename();
        }

        if ($virtualizedFile->getMountPoint() === self::UPLOADED_ATTACHMENTS) {
            return '/data/material/' . $virtualizedFile->getRelativePathAndFilename();
        }

        return 'Error: FileUrl not configured! See <AppRuntime.php>.';
    }
}
