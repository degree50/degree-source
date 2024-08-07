<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideMaterial;
use JsonSerializable;

/**
 * Client side representation of material
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideMaterial implements JsonSerializable
{

    private function __construct(
        private string $id,
        private string $solutionId,
        private string $material
    )
    {
    }

    public static function fromServerSideMaterial(ServerSideMaterial $material, string $solutionId): self
    {
        return new self(
            $solutionId . '_material',
            $solutionId,
            $material->toString()
        );
    }

    public static function fromArray(array $input): self
    {
        return new self(
            $input['id'],
            $input['solutionId'],
            $input['material'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'solutionId' => $this->solutionId,
            'material' => $this->material,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
