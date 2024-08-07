<?php

namespace App\Domain\Solution\Dto\ServerSideSolutionData;

/**
 * Server side representation of an annotation.
 *
 * @see \App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder
 **/
final class ServerSideAnnotation
{
    private string $start;
    private string $end;
    private string $text;
    private string $memo;
    private ?string $color;

    private function __construct(string $start, string $end, string $text, string $memo, ?string $color)
    {
        $this->start = $start;
        $this->end = $end;
        $this->text = $text;
        $this->memo = $memo;
        $this->color = $color;
    }

    public static function fromArray(array $input): ServerSideAnnotation
    {
        return new self(
            $input['start'],
            $input['end'],
            $input['text'],
            $input['memo'],
            $input['color'],
        );
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'text' => $this->text,
            'memo' => $this->memo,
            'color' => $this->color,
        ];
    }
}
