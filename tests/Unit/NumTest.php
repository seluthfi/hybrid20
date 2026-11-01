<?php

namespace Tests\Unit;

use App\Support\Num;
use App\Support\PaceCalculator;
use PHPUnit\Framework\TestCase;

class NumTest extends TestCase
{
    public function test_to_integer_parsing(): void
    {
        $this->assertNull(Num::toInteger(null));
        $this->assertNull(Num::toInteger(''));
        $this->assertSame(2420, Num::toInteger('2.420'));
        $this->assertSame(10000, Num::toInteger('10,000'));
        $this->assertSame(10000, Num::toInteger('10.000'));
        $this->assertSame(500, Num::toInteger('500'));
        $this->assertSame(15, Num::toInteger(15));
    }

    public function test_to_decimal_parsing(): void
    {
        $this->assertNull(Num::toDecimal(null));
        $this->assertNull(Num::toDecimal(''));
        $this->assertSame(1234.5, Num::toDecimal('1.234,5'));
        $this->assertSame(1234.5, Num::toDecimal('1234.5'));
        $this->assertSame(74.5, Num::toDecimal('74,5'));
        $this->assertSame(74.0, Num::toDecimal('74'));
        $this->assertSame(2.5, Num::toDecimal('2,5'));
        $this->assertSame(10.5, Num::toDecimal(10.5));
    }

    public function test_number_formatting(): void
    {
        $this->assertSame('-', Num::format(null));
        $this->assertSame('10.000', Num::format(10000, 0));
        $this->assertSame('74,5', Num::format(74.5, 1));
        $this->assertSame('1.234,50', Num::format(1234.5, 2));
    }

    public function test_pace_calculation_and_formatting(): void
    {
        // 5 km in 1800 seconds (30 mins) -> 360 s/km -> 6'00"/km
        $paceSeconds = PaceCalculator::calculatePaceSeconds(1800, 5.0);
        $this->assertSame(360, $paceSeconds);
        $this->assertSame("6'00\"/km", PaceCalculator::formatPace($paceSeconds));

        // 2.1 km in 731 seconds (12:11) -> 731 / 2.1 = 348.095 -> 348 s/km -> 5'48"/km
        $paceSeconds2 = PaceCalculator::calculatePaceSeconds(731, 2.1);
        $this->assertSame(348, $paceSeconds2);
        $this->assertSame("5'48\"/km", PaceCalculator::formatPace($paceSeconds2));

        // Zero / null distance or duration returns null / '-'
        $this->assertNull(PaceCalculator::calculatePaceSeconds(0, 5.0));
        $this->assertNull(PaceCalculator::calculatePaceSeconds(1800, 0.0));
        $this->assertNull(PaceCalculator::calculatePaceSeconds(null, 5.0));
        $this->assertSame('-', PaceCalculator::formatPace(null));
    }

    public function test_duration_parsing_and_formatting(): void
    {
        // MM:SS
        $this->assertSame(731, PaceCalculator::parseDurationToSeconds('12:11'));
        // Minutes >= 60 (e.g. 75:30)
        $this->assertSame(4530, PaceCalculator::parseDurationToSeconds('75:30'));
        // HH:MM:SS
        $this->assertSame(3665, PaceCalculator::parseDurationToSeconds('1:01:05'));
        // Numeric minutes only
        $this->assertSame(3300, PaceCalculator::parseDurationToSeconds('55'));

        $this->assertSame('12:11', PaceCalculator::formatDuration(731));
        $this->assertSame('1:01:05', PaceCalculator::formatDuration(3665));
        $this->assertSame('-', PaceCalculator::formatDuration(null));
        $this->assertSame('-', PaceCalculator::formatDuration(0));
    }
}
