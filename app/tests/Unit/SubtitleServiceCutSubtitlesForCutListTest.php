<?php

namespace App\Tests\Unit;

use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use App\VideoEncoding\Service\SubtitleService;
use PHPUnit\Framework\TestCase;

final class SubtitleServiceCutSubtitlesForCutListTest extends TestCase
{
    public function testThatMetaDataBlocksAreAddedAtTheTopOfTheResult(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:00.000 --> 00:05.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:23.000 --> 00:26.000
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:22.123',
                'end' => '00:00:25.123',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 22.123,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:00.877 --> 00:00:03.000
            Lehrer: Mhm: 144, 141 und 139.
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }

    public function testThatABlockThatPartiallyOverlapsWithCutStartShouldHaveMatchingStartTime(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:03.000 --> 00:06.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:23.000 --> 00:26.100
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:4.000',
                'end' => '00:00:8.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 4.0,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:00.000 --> 00:00:02.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }

    public function testThatABlockThatPartiallyOverlapsWithCutEndShouldHaveMatchingEndTime(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:03.000 --> 00:06.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:23.000 --> 00:26.100
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:2.000',
                'end' => '00:00:5.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 2.0,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:01.000 --> 00:00:03.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }

    public function testThatABlockThatIsFullyContainedByCutShouldHaveAdjustedStartAndEndTime(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:00:03.000 --> 00:00:06.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:00:23.000 --> 00:00:26.100
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:2.000',
                'end' => '00:00:8.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 2.0,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:01.000 --> 00:00:04.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }

    public function testThatABlockThatFullyOverlapsCutShouldHaveAdjustedStartAndEndTime(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:01.000 --> 00:010.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:23.000 --> 00:26.100
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:2.000',
                'end' => '00:00:8.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 2.0,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:00.000 --> 00:00:06.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }

    public function testThatAllRelevantBlocksAreInTheNewSubtitles(): void
    {
        $subtitleService = new SubtitleService();

        $subtitles = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            1
            00:01.000 --> 00:010.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?

            1
            00:23.000 --> 00:26.000
            Lehrer: Mhm: 144, 141 und 139.

            NOTE test test
            END;

        $cutList = [
            ServerSideCut::fromArray([
                'start' => '00:00:25.000',
                'end' => '00:00:28.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 25.0,
                'url' => '',
                'playbackRate' => 1
            ]),
            ServerSideCut::fromArray([
                'start' => '00:00:2.000',
                'end' => '00:00:8.000',
                'text' => '',
                'memo' => '',
                'color' => null,
                'offset' => 2.0,
                'url' => '',
                'playbackRate' => 1
            ]),
        ];

        $expected = <<<END
            WEBVTT - test file

            NOTE
            This file was created for testing

            NOTE test test

            1
            00:00:00.000 --> 00:00:01.000
            Lehrer: Mhm: 144, 141 und 139.

            1
            00:00:03.000 --> 00:00:09.000
            Lehrer: Gut, welche Zahlen liegen zwischen 145 und 130?
            END;


        $this->assertEquals($expected, $subtitleService->cutSubtitlesForCutList($subtitles, $cutList));
    }
}
