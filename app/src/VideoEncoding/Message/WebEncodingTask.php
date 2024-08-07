<?php

namespace App\VideoEncoding\Message;

use App\Domain\VirtualizedFile\Model\VirtualizedFile;

readonly class WebEncodingTask
{
    public function __construct(
        public string          $videoId,
        public VirtualizedFile $desiredOutputDirectory
    )
    {
    }
}
