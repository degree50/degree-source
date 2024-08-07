<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Twig\AppRuntime;
use JsonSerializable;

/**
 * Client side representation of a videoUrl.
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideVideoUrl implements JsonSerializable
{

    private function __construct(
        private string $hls,
        private string $mp4,
        private string $vtt,
        private string $thumbnail,
    )
    {
    }

    public static function fromBaseUrl(string $baseUrl): ClientSideVideoUrl
    {
        // WHY: Hotfix! Hack to prevent the server from lying to the client about available URL to possibly non-existing files.
        //      This issue came up when we started to convert cut video to hls too (to enable AD and subtitle cutting).
        //      The old mp4-only cut that were created before that change were not played anymore because we couldn't determine
        //      in the client if a hls or mp4 version of the video was actually available.
        // REASON: In 'fromBaseUrl' we just assume, that all files actually exist. That might not be the case but we have no
        //         way to know for sure as long as we do not explicitly check for ourselves.
        //         So we have to reverse engineer the file path from the public url and check if the file exist.
        // FIXME - we should probably enhance the video model for this.

        /**
         * get the video's directory path
         * example: 'https://degree-instance.de/data/encoded_videos/uuid-123-456' -> '/app/public/data/encoded_videos/uuid-123-456'
         */
        $videoDirectoryPathOnFileSystem = "/app/public/data/" . AppRuntime::ENCODED_VIDEOS . "/" . implode("/", array_slice(explode("/", $baseUrl), -1));
        $hlsPath = "$videoDirectoryPathOnFileSystem/hls.m3u8";
        $mp4Path = "$videoDirectoryPathOnFileSystem/x264.mp4";
        $vttPath = "$videoDirectoryPathOnFileSystem/subtitles.vtt";
        $thumbnailPath = "$videoDirectoryPathOnFileSystem/thumbnail.jpg";

        $hlsUrl = file_exists($hlsPath) ? $baseUrl . '/hls.m3u8' : '';
        $mp4Url = file_exists($mp4Path) ? $baseUrl . '/x264.mp4' : '';
        $vttUrl = file_exists($vttPath) ? $baseUrl . '/subtitles.vtt' : '';
        $thumbnailUrl = file_exists($thumbnailPath) ? $baseUrl . '/thumbnail.jpg' : '';

        return new self(
            $hlsUrl,
            $mp4Url,
            $vttUrl,
            $thumbnailUrl,
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'hls' => $this->hls,
            'mp4' => $this->mp4,
            'vtt' => $this->vtt,
            'thumbnail' => $this->thumbnail,
        ];
    }
}
