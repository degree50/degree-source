<?php

namespace App\VideoEncoding\Service;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use App\VideoEncoding\TimeCode;

class SubtitleService
{
    /**
     * @param string $originalVtt
     * @param ServerSideCut[] $cutList
     * @return string The result vtt string
     */
    public function cutSubtitlesForCutList(string $originalVtt, array $cutList): string
    {
        $subtitleEntries = $this->parseSubtitleEntryListFromVttString($originalVtt);

        $resultSubtitleBlocks = array_reduce(
            $cutList,
            function (array $resultAndPositionInCutVideo, ServerSideCut $cut) use ($subtitleEntries) {
                // get subs for cut
                $cutStartInVideo = $cut->offset;
                $cutEndInVideo = $cut->offset + $cut->getDuration();
                $cutStartInCutVideo = $resultAndPositionInCutVideo['positionInCutVideo'];

                $relevantSubtitleEntriesForCut = $this->getRelevantSubtitlesForTimeFrame(
                    $subtitleEntries,
                    $cutStartInVideo,
                    $cutEndInVideo
                );

                // change start and end of subs according to cut
                $subtitleBlocksWithUpdatedTimeStamps = $this
                    ->getSubtitleBlocksWithUpdatedTimeStampsBasedOnTimeFrameAndOffset(
                        $relevantSubtitleEntriesForCut,
                        $cutStartInVideo,
                        $cutEndInVideo,
                        $cutStartInCutVideo
                    );

                return [
                    "result" => [...$resultAndPositionInCutVideo["result"], ...$subtitleBlocksWithUpdatedTimeStamps],
                    "positionInCutVideo" => $cutStartInCutVideo + $cut->getDuration(),
                ];
            },
            [
                "result" => [],
                "positionInCutVideo" => 0,
            ]
        )["result"];

        // We still want to keep meta data & comments, so we copy them over
        $metadataSubtitleEntries = array_filter($subtitleEntries, function (SubtitleEntry $subtitleEntry) {
            return $subtitleEntry->start === null;
        });

        $metadataBlocks = array_map(function (SubtitleEntry $subtitleEntry) {
            return $subtitleEntry->block;
        }, $metadataSubtitleEntries);

        return implode("\n\n", [...$metadataBlocks, ...$resultSubtitleBlocks]);
    }

    /**
     * @param string $vtt
     * @return SubtitleEntry[]
     */
    public function parseSubtitleEntryListFromVttString(string $vtt): array
    {
        // normalize line endings to LF
        $fileContentWithLF = preg_replace("/(\r\n|\r|\n)/", "\n", $vtt);

        // remove multiple empty lines with (find with regex /\n\n+/
        // (more than 2 LF) and replace with "\n\n" (exactly 2 LF)
        $fileContentWithoutMultipleNewLines = preg_replace("/\n\n+/", "\n\n", $fileContentWithLF);

        // trim white space
        $fileContentTrimmed = trim($fileContentWithoutMultipleNewLines);

        // individual blocks are separated by a newline
        $blocks = explode("\n\n", $fileContentTrimmed);

        return array_map(function (string $block) {
            // If the block has an arrow (" --> "), then it's a text track block.
            // We ignore all other blocks ('STYLE', 'NOTE', etc).
            if (str_contains($block, " --> ")) {
                $lines = explode("\n", $block);
                // can only be first or second line
                $lineIndexWithTime = str_contains($lines[0], " --> ") ? 0 : 1;

                // get start and end from '00:00:03.000 --> 00:00:06.500 position:90% align:right size:35%'
                $times = explode(" --> ", $lines[$lineIndexWithTime]);
                if (count($times) < 2) {
                    die($block);
                }
                $startTimeStamp = trim($times[0]);
                $endTimeStamp = trim($times[1]);

                return new SubtitleEntry($block, $lineIndexWithTime, $startTimeStamp, $endTimeStamp);
            }

            return new SubtitleEntry($block);
        }, $blocks);
    }

    /**
     * Get Subtitles that are defined within the boundaries of a time frame.
     *
     * @param SubtitleEntry[] $subtitleEntries
     * @param float $timeFrameStart
     * @param float $timeFrameEnd
     *
     * @return SubtitleEntry[]
     */
    private function getRelevantSubtitlesForTimeFrame(
        array $subtitleEntries,
        float $timeFrameStart,
        float $timeFrameEnd
    ): array
    {
        return array_filter(
            $subtitleEntries,
            function (SubtitleEntry $subtitleEntry) use ($timeFrameStart, $timeFrameEnd) {
                // subEntry is a metadata block (e.g. WEBVTT header, 'NOTE' or 'STYLE')
                if ($subtitleEntry->start === null) {
                    return false;
                }

                $subtitleStart = TimeCode::fromTimeString($subtitleEntry->start)->toFloat();
                $subtitleEnd = TimeCode::fromTimeString($subtitleEntry->end)->toFloat();

                // subEntry is not relevant for time frame
                // (ends before time frame starts OR starts after time frame ends)
                if ($timeFrameStart > $subtitleEnd || $subtitleStart > $timeFrameEnd) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @param SubtitleEntry[] $subtitleEntries
     * @param float $timeFrameStart
     * @param float $timeFrameEnd
     * @param float $offset
     *
     * @return string[] new blocks
     */
    private function getSubtitleBlocksWithUpdatedTimeStampsBasedOnTimeFrameAndOffset(
        array $subtitleEntries,
        float $timeFrameStart,
        float $timeFrameEnd,
        float $offset
    ): array
    {
        return array_map(function (SubtitleEntry $subtitleEntry) use ($timeFrameStart, $timeFrameEnd, $offset) {
            $subtitleStart = TimeCode::fromTimeString($subtitleEntry->start)->toFloat();
            $subtitleEnd = TimeCode::fromTimeString($subtitleEntry->end)->toFloat();

            $newStart = TimeCode::fromSeconds(max($subtitleStart, $timeFrameStart) - $timeFrameStart + $offset)
                ->toTimeString();
            $newEnd = TimeCode::fromSeconds(min($subtitleEnd, $timeFrameEnd) - $timeFrameStart + $offset)
                ->toTimeString();

            // update time stamps in block
            $lines = explode("\n", $subtitleEntry->block);
            $lineWithTime = $lines[$subtitleEntry->indexOfLineTimeEntryInBlock];
            $lineWithTimeAsArray = explode(" ", $lineWithTime);
            $restOfLine = array_slice($lineWithTimeAsArray, 3);

            // replace start (first) and end (third) entries in line with time
            $newLineWithTime = implode(" ", [$newStart, "-->", $newEnd, ...$restOfLine]);

            // replace line
            $lines[$subtitleEntry->indexOfLineTimeEntryInBlock] = $newLineWithTime;

            return implode("\n", $lines);
        }, $subtitleEntries);
    }
}
