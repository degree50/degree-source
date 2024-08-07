<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideVideoCode;
use JsonSerializable;

/**
 * Client side representation of a videoCode.
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideVideoCode implements JsonSerializable
{
    private function __construct(
        private string  $start,
        private string  $end,
        private string  $text,
        private string  $memo,
        private ?string $color,
        private string  $idFromPrototype,
        private string  $solutionId,
        private string  $id
    )
    {
    }

    public static function fromServerSideVideoCode(ServerSideVideoCode $videoCode, string $solutionId, int $index): ClientSideVideoCode
    {
        return new self(
            $videoCode->getStart(),
            $videoCode->getEnd(),
            $videoCode->getText(),
            $videoCode->getMemo(),
            $videoCode->getColor(),
            $videoCode->getIdFromPrototype(),
            $solutionId,
            $solutionId . '_' . $index
        );
    }

    public static function fromArray(array $input, int $index): ClientSideVideoCode
    {
        return new self(
            $input['start'],
            $input['end'],
            $input['text'],
            $input['memo'],
            $input['color'],
            $input['solutionId'],
            $input['idFromPrototype'],
            $input['solutionId'] . '_' . $index
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'start' => $this->start,
            'end' => $this->end,
            'text' => $this->text,
            'memo' => $this->memo,
            'color' => $this->color,
            'idFromPrototype' => $this->idFromPrototype,
            'solutionId' => $this->solutionId,
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

