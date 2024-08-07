<?php

namespace App\Domain\Solution\Dto\ServerSideSolutionData;

/**
 * Server side representation of material.
 *
 * @see \App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder
 **/
final class ServerSideMaterial
{
    private string $material;

    private function __construct(string $material)
    {
        $this->material = $material;
    }

    public static function fromArray(array|null $material): ServerSideMaterial
    {
        return new self(!is_null($material) && array_key_exists('material', $material) ? $material['material'] : null);
    }

    public static function fromString(?string $material): ServerSideMaterial
    {
        return new self($material ?? '');
    }

    public function toString(): string
    {
        return $this->material;
    }
}
