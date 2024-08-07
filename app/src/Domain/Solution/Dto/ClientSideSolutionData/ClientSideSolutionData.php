<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use JsonSerializable;

/**
 * Client side representation of solution data.
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideSolutionData implements JsonSerializable
{
    /**
     * @param string[] $annotationIds
     * @param string[] $videoCodeIds
     * @param string[] $cutIds
     * @param string[] $videoCodePrototypeIds
     * @param string|null $materialId
     */
    private function __construct(
        private array   $annotationIds,
        private array   $videoCodeIds,
        private array   $cutIds,
        private array   $videoCodePrototypeIds,
        private ?string $materialId,
    )
    {
        foreach ($annotationIds as $annotationId) {
            assert(is_string($annotationId));
        }

        foreach ($videoCodeIds as $videoCodeId) {
            assert(is_string($videoCodeId));
        }

        foreach ($cutIds as $cutId) {
            assert(is_string($cutId));
        }

        foreach ($videoCodePrototypeIds as $videoCodePrototypeId) {
            assert(is_string($videoCodePrototypeId));
        }

        assert(is_string($materialId) || is_null($materialId));
    }

    public static function create(
        array   $annotationIds,
        array   $videoCodeIds,
        array   $cutIds,
        array   $videoCodePrototypeIds,
        ?string $materialId,
    ): ClientSideSolutionData
    {
        return new self(
            $annotationIds,
            $videoCodeIds,
            $cutIds,
            $videoCodePrototypeIds,
            $materialId
        );
    }

    public static function fromArray(array $input): ClientSideSolutionData
    {
        return new self(
            $input['annotations'],
            $input['videoCodes'],
            $input['cutList'],
            $input['videoCodePrototypes'],
            $input['material'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'annotations' => $this->annotationIds,
            'videoCodes' => $this->videoCodeIds,
            'cutList' => $this->cutIds,
            'videoCodePrototypes' => $this->videoCodePrototypeIds,
            'material' => $this->materialId,
        ];
    }

    public function getAnnotationIds(): array
    {
        return $this->annotationIds;
    }

    public function getVideoCodeIds(): array
    {
        return $this->videoCodeIds;
    }

    public function getCutIds(): array
    {
        return $this->cutIds;
    }

    public function getVideoCodePrototypeIds(): array
    {
        return $this->videoCodePrototypeIds;
    }

    public function getMaterialId(): ?string
    {
        return $this->materialId;
    }
}
