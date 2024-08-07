<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use JsonSerializable;

/**
 * Client side representation of a cut.
 *
 * @see ClientSideSolutionDataBuilder
 **/
final readonly class ClientSideCut implements JsonSerializable
{

    private function __construct(
        private string  $start,
        private string  $end,
        private string  $text,
        private string  $memo,
        private ?string $color,
        private string  $solutionId,
        private float   $offset,
        private float   $playbackRate,
        private string  $id
    )
    {
    }

    public static function fromServerSideCut(ServerSideCut $cut, string $solutionId, int $index): ClientSideCut
    {
        return new self(
            $cut->start,
            $cut->end,
            $cut->text,
            $cut->memo,
            $cut->color,
            $solutionId,
            $cut->offset,
            $cut->playbackRate,
            $solutionId . '_' . $index
        );
    }

    public static function fromArray(array $input, int $index): ClientSideCut
    {
        return new self(
            $input['start'],
            $input['end'],
            $input['text'],
            $input['memo'],
            $input['color'],
            $input['solutionId'],
            $input['offset'],
            $input['playbackRate'],
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
            'solutionId' => $this->solutionId,
            'offset' => $this->offset,
            'playbackRate' => $this->playbackRate,
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

