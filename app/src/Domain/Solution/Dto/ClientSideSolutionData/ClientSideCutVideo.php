<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Domain\CutVideo\Model\CutVideo;
use App\Domain\Video\Model\Video;
use JsonSerializable;

/**
 * Client side representation of an encoded cut video.
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideCutVideo implements JsonSerializable
{

    private function __construct(
        private string             $id,
        private string             $name,
        private string             $createdAt,
        private float              $duration,
        private ClientSideVideoUrl $url
    )
    {
    }

    public static function fromCutVideoEntity(CutVideo $cutVideo, ClientSideVideoUrl $url): ClientSideCutVideo
    {
        return new self(
            $cutVideo->getId(),
            $cutVideo->getName(),
            $cutVideo->getCreatedAt()->format("d.m.Y"),
            $cutVideo->getVideoDuration(),
            $url,
        );
    }

    public static function fromVideoEntity(Video $video, ClientSideVideoUrl $url): ClientSideCutVideo
    {
        return new self(
            $video->getId(),
            $video->getTitle(),
            $video->getCreatedAt()->format("d.m.Y"),
            $video->getVideoDuration(),
            $url,
        );
    }

    public static function fromArray(array $input): ClientSideCutVideo
    {
        return new self(
            $input['id'],
            $input['name'],
            $input['createdAt'],
            $input['duration'],
            $input['url']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'createdAt' => $this->createdAt,
            'duration' => $this->duration,
            'url' => $this->url,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
