<?php

namespace App\VideoEncoding\Message;

readonly class CutListEncodingTask
{
    public function __construct(
        public string $exercisePhaseTeamId,
        public string $videoId
    )
    {
    }
}
