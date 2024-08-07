<?php

namespace App\Domain\Solution\Dto\ServerSideSolutionData;

/**
 * Server side representation of a videoCode.
 *
 * @see \App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder
 **/
final readonly class ServerSideVideoCode
{
    private string $start;
    private string $end;
    private string $text;
    private string $memo;
    private string $idFromPrototype;
    private ?string $color;

    private function __construct(string $start, string $end, string $text, string $memo, string $idFromPrototype, ?string $color = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->text = $text;
        $this->memo = $memo;
        $this->idFromPrototype = $idFromPrototype;
        $this->color = $color;
    }

    /**
     * Create Entity from array.
     * This is used to read from persisted and clientSide JSON.
     */
    public static function fromArray(array $input): ServerSideVideoCode
    {
        return new self(
            $input['start'],
            $input['end'],
            $input['text'],
            $input['memo'],
            $input['idFromPrototype'],
            $input['color'],
        );
    }

    /**
     * Prepare to be persisted or sent to client as JSON
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'text' => $this->text,
            'memo' => $this->memo,
            'idFromPrototype' => $this->idFromPrototype,
            'color' => $this->color,
        ];
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function getEnd(): string
    {
        return $this->end;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getMemo(): string
    {
        return $this->memo;
    }

    public function getIdFromPrototype(): string
    {
        return $this->idFromPrototype;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}
