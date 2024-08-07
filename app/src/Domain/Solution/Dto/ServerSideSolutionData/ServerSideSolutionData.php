<?php

namespace App\Domain\Solution\Dto\ServerSideSolutionData;

/**
 * Server side representation of solutionData.
 *
 * @see \App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder
 **/
final class ServerSideSolutionData
{
    /**
     * @var ServerSideAnnotation[]
     */
    private array $serverSideAnnotations;

    /**
     * @var ServerSideVideoCode[]
     */
    private array $serverSideVideoCodes;

    /**
     * @var ServerSideCut[]
     */
    private array $serverSideCutList;

    /**
     * @var ServerSideVideoCodePrototype[]
     */
    private array $serverSideVideoCodePrototypes;

    /**
     * @var ServerSideMaterial | null
     */
    private ?ServerSideMaterial $serverSideMaterial;

    private function __construct(
        array               $serverSideAnnotations,
        array               $serverSideVideoCodes,
        array               $serverSideCutList,
        array               $serverSideVideoCodePrototypes,
        ?ServerSideMaterial $serverSideMaterial,
    )
    {
        foreach ($serverSideAnnotations as $annotation) {
            assert($annotation instanceof ServerSideAnnotation);
        }

        foreach ($serverSideVideoCodes as $videoCode) {
            assert($videoCode instanceof ServerSideVideoCode);
        }

        foreach ($serverSideCutList as $cut) {
            assert($cut instanceof ServerSideCut);
        }

        foreach ($serverSideVideoCodePrototypes as $videoCodePrototype) {
            assert($videoCodePrototype instanceof ServerSideVideoCodePrototype);
        }

        assert($serverSideMaterial === null || $serverSideMaterial instanceof ServerSideMaterial);

        $this->serverSideAnnotations = $serverSideAnnotations;
        $this->serverSideVideoCodes = $serverSideVideoCodes;
        $this->serverSideCutList = $serverSideCutList;
        $this->serverSideVideoCodePrototypes = $serverSideVideoCodePrototypes;
        $this->serverSideMaterial = $serverSideMaterial;
    }

    /**
     * Create Entity from array.
     * This is used to read persisted JSON.
     *
     */
    public static function fromArray(array $input): ServerSideSolutionData
    {
        $serverSideAnnotations = array_map(function ($annotation) {
            return ServerSideAnnotation::fromArray($annotation);
        }, $input['annotations']);

        $serverSideVideoCodes = array_map(function ($videoCode) {
            return ServerSideVideoCode::fromArray($videoCode);
        }, $input['videoCodes']);

        $serverSideCutList = array_map(function ($cut) {
            return ServerSideCut::fromArray($cut);
        }, $input['cutList']);

        // NOTE:
        // Prototypes have been saved under the name 'customVideoCodesPool' as JSON before.
        // We need to stay compatible with already persisted solutions.
        // That's where the naming mismatch is coming from!
        $serverSideVideoCodePrototypes = [];
        if (!empty($input['customVideoCodesPool'])) {
            $serverSideVideoCodePrototypes = array_map(function ($videoCodePrototype) {
                return ServerSideVideoCodePrototype::fromArray($videoCodePrototype);
            }, $input['customVideoCodesPool']);
        }

        $serverSideMaterial = array_key_exists('material', $input) && !is_null($input['material'])
            ? ServerSideMaterial::fromString($input['material'])
            : null;

        return new self(
            $serverSideAnnotations,
            $serverSideVideoCodes,
            $serverSideCutList,
            $serverSideVideoCodePrototypes,
            $serverSideMaterial
        );
    }

    /**
     * Create Entity from clientSide JSON.
     *
     */
    public static function fromClientJSON(array $input): self
    {
        $serverSideAnnotations = array_map(function ($annotation) {
            return ServerSideAnnotation::fromArray($annotation);
        }, $input['annotations']);

        $serverSideVideoCodes = array_map(function ($videoCode) {
            return ServerSideVideoCode::fromArray($videoCode);
        }, $input['videoCodes']);

        $serverSideCutList = array_map(function ($cut) {
            return ServerSideCut::fromArray($cut);
        }, $input['cutList']);

        $serverSideVideoCodePrototypes = [];
        if (!empty($input['videoCodePrototypes'])) {
            $serverSideVideoCodePrototypes = array_map(function ($videoCodePrototype) {
                return ServerSideVideoCodePrototype::fromArray($videoCodePrototype);
            }, $input['videoCodePrototypes']);
        }

        $serverSideMaterial = !empty($input['material'])
            ? ServerSideMaterial::fromArray($input['material'])
            : null;

        return new self(
            $serverSideAnnotations,
            $serverSideVideoCodes,
            $serverSideCutList,
            $serverSideVideoCodePrototypes,
            $serverSideMaterial
        );
    }

    /**
     * Prepare solution to be persisted or sent to client as JSON
     */
    public function toArray(): array
    {
        $annotations = array_map(function (ServerSideAnnotation $annotation) {
            return $annotation->toArray();
        }, $this->serverSideAnnotations);

        $videoCodes = array_map(function (ServerSideVideoCode $videoCode) {
            return $videoCode->toArray();
        }, $this->serverSideVideoCodes);

        $cutList = array_map(function (ServerSideCut $cut) {
            return $cut->toArray();
        }, $this->serverSideCutList);

        $videoCodePrototypes = array_map(function (ServerSideVideoCodePrototype $videoCodePrototype) {
            return $videoCodePrototype->toArray();
        }, $this->serverSideVideoCodePrototypes);

        $material = $this->serverSideMaterial?->toString();

        // Why 'customVideoCodesPool' instead of 'videoCodePrototypes'? -> see fromArray()-method for explanation
        return [
            'annotations' => $annotations,
            'videoCodes' => $videoCodes,
            'cutList' => $cutList,
            'customVideoCodesPool' => $videoCodePrototypes,
            'material' => $material,
        ];
    }

    public function getAnnotations(): array
    {
        return $this->serverSideAnnotations;
    }

    public function getVideoCodes(): array
    {
        return $this->serverSideVideoCodes;
    }

    public function getCutList(): array
    {
        return $this->serverSideCutList;
    }

    public function getVideoCodePrototypes(): array
    {
        return $this->serverSideVideoCodePrototypes;
    }

    public function getMaterial(): ServerSideMaterial|null
    {
        return $this->serverSideMaterial;
    }
}
