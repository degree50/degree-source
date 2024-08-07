<?php

namespace App\VideoEncoding;

/**
 * WHY: PHP uses unix time stamps wich are seconds. We need milliseconds.
 */
final readonly class TimeCode
{
    private function __construct(
        public int $unixTimeStamp,
        public int $milliseconds
    )
    {
    }

    // +hh:mm:ss.mss
    public static function fromTimeString(string $timeString): self
    {
        /**
         * WHY reverse array:
         * A TimeString can consist of 3 parts: hours, minutes, seconds ("12:23:42", "00:23:42.123") - seperated by a colon.
         * Hours and Minutes are optional ("23:42", "42.123").
         *
         * We reverse the array to make sure that the only fixed part (seconds) will always be at index "0".
         * If we would not do this we can not be sure that seconds are at index 0.
         * So the reversed array gives us
         *  - seconds at index "0"
         *  - minutes at index "1" if present
         *  - hours at index "2" if present
         */
        $timeStringAsArray = array_reverse(explode(":", $timeString));

        $seconds = array_key_exists(0, $timeStringAsArray) ? intval($timeStringAsArray[0]) : 0;
        $minutes = array_key_exists(1, $timeStringAsArray) ? intval($timeStringAsArray[1]) : 0;
        $hours = array_key_exists(2, $timeStringAsArray) ? intval($timeStringAsArray[2]) : 0;

        $unixTimeStamp = $hours * 3600 + $minutes * 60 + $seconds;

        $milliseconds = str_contains($timeString, ".")
            ? intval(explode(".", $timeString)[1])
            : 0;

        return new self($unixTimeStamp, $milliseconds);
    }

    public static function fromSeconds(float|int $time): self
    {
        if (is_int($time)) {
            // it's already just seconds.
            return new self($time, 0);
        }

        $unixTimeStamp = intval($time);
        // ceil because of float imprecision (0.123 -> 0.1229999999)
        $milliseconds = intval(ceil(($time - $unixTimeStamp) * 1000));

        return new self($unixTimeStamp, $milliseconds);
    }

    public static function equals(TimeCode $x, TimeCode $y): bool
    {
        return $x->unixTimeStamp === $y->unixTimeStamp && $x->milliseconds === $y->milliseconds;
    }

    public static function greaterThan(TimeCode $x, TimeCode $y): bool
    {
        if ($x->unixTimeStamp === $y->unixTimeStamp) {
            return $x->milliseconds > $y->milliseconds;
        } else {
            return $x->unixTimeStamp > $y->unixTimeStamp;
        }
    }

    public function toTimeString(): string
    {
        $dateString = date("H:i:s", $this->unixTimeStamp);

        $millisecondsAsString = str_split(strval($this->milliseconds));

        $hundredths = array_key_exists(0, $millisecondsAsString) ? $millisecondsAsString[0] : "0";
        $thousandths = array_key_exists(1, $millisecondsAsString) ? $millisecondsAsString[1] : "0";
        $millis = array_key_exists(2, $millisecondsAsString) ? $millisecondsAsString[2] : "0";

        return "$dateString.$hundredths$thousandths$millis";
    }

    public function toFloat(): float
    {
        return floatval("$this->unixTimeStamp.$this->milliseconds");
    }
}
