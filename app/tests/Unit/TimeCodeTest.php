<?php

namespace App\Tests\Unit;

use App\VideoEncoding\TimeCode;
use PHPUnit\Framework\TestCase;

final class TimeCodeTest extends TestCase
{
    public function testCanBeInstantiatedFromString(): void
    {
        $timeString = "12:23:42.123";

        $timeCode = TimeCode::fromTimeString($timeString);

        $this->assertEquals(12 * 3600 + 23 * 60 + 42, $timeCode->unixTimeStamp);
        $this->assertEquals(123, $timeCode->milliseconds);
    }

    public function testCanBeInstantiatedFromStringWithoutHours(): void
    {
        $timeString = "23:42.123";

        $timeCode = TimeCode::fromTimeString($timeString);

        $this->assertEquals(23 * 60 + 42, $timeCode->unixTimeStamp);
        $this->assertEquals(123, $timeCode->milliseconds);
    }

    public function testCanBeInstantiatedFromFloatSeconds(): void
    {
        $seconds = 44622.123;

        $timeCode = TimeCode::fromSeconds($seconds);

        $this->assertEquals(12 * 3600 + 23 * 60 + 42, $timeCode->unixTimeStamp);
        $this->assertEquals(123, $timeCode->milliseconds);
    }

    public function testCanBeInstantiatedFromIntSeconds(): void
    {
        $seconds = 44622;

        $timeCode = TimeCode::fromSeconds($seconds);

        $this->assertEquals(12 * 3600 + 23 * 60 + 42, $timeCode->unixTimeStamp);
        $this->assertEquals(0, $timeCode->milliseconds);
    }

    public function testCanConvertToTimeString(): void
    {
        $fullTimeString = "12:23:42.123";
        $missingHoursTimeString = "23:42.12";
        $missingMillisecondsTimeString = "12:23:42";

        $timeCode1 = TimeCode::fromTimeString($fullTimeString);
        $timeCode2 = TimeCode::fromTimeString($missingHoursTimeString);
        $timeCode3 = TimeCode::fromTimeString($missingMillisecondsTimeString);

        $this->assertEquals("12:23:42.123", $timeCode1->toTimeString());
        $this->assertEquals("00:23:42.120", $timeCode2->toTimeString());
        $this->assertEquals("12:23:42.000", $timeCode3->toTimeString());
    }

    public function testCanConvertToFloat(): void
    {
        $seconds = 44622.123;

        $timeCode = TimeCode::fromSeconds($seconds);

        $this->assertEquals(44622.123, $timeCode->toFloat());
    }
}
