<?php

namespace App\VideoEncoding\Service;

final readonly class SubtitleEntry
{
    public function __construct(
        public string  $block,
        public ?int    $indexOfLineTimeEntryInBlock = null,
        public ?string $start = null,
        public ?string $end = null
    )
    {
    }
}
