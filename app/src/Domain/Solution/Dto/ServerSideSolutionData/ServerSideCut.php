<?php

namespace App\Domain\Solution\Dto\ServerSideSolutionData;

use App\VideoEncoding\TimeCode;

/**
 * Server side representation of a cut.
 *
 * @see \App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder
 **/
final readonly class ServerSideCut
{
    public string $start;
    public string $end;
    public string $text;
    public string $memo;
    public ?string $color;
    public float $offset;
    public float $playbackRate;

    private function __construct(
        string  $start,
        string  $end,
        string  $text,
        string  $memo,
        ?string $color,
        float   $offset,
        float   $playbackRate
    )
    {
        $this->start = $start;
        $this->end = $end;
        $this->text = $text;
        $this->memo = $memo;
        $this->color = $color;
        $this->offset = $offset;
        $this->playbackRate = $playbackRate;
    }

    public static function fromArray(array $input): ServerSideCut
    {
        return new self(
            $input['start'],
            $input['end'],
            $input['text'],
            $input['memo'],
            $input['color'],
            $input['offset'],
            $input['playbackRate']
        );
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'text' => $this->text,
            'memo' => $this->memo,
            'color' => $this->color,
            'offset' => $this->offset,
            'playbackRate' => $this->playbackRate
        ];
    }

    /**
     * Get the duration (length) of the cut in seconds with milliseconds.
     *
     * @return float
     */
    public function getDuration(): float
    {
        return TimeCode::fromTimeString($this->end)->toFloat() - TimeCode::fromTimeString($this->start)->toFloat();
    }
}

