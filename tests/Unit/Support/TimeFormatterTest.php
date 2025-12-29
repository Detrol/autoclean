<?php

namespace Tests\Unit\Support;

use App\Support\TimeFormatter;
use PHPUnit\Framework\TestCase;

class TimeFormatterTest extends TestCase
{
    private TimeFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new TimeFormatter();
    }

    public function test_formats_zero_minutes(): void
    {
        $this->assertEquals('0 min', $this->formatter->formatMinutesSv(0));
    }

    public function test_formats_single_minute(): void
    {
        $this->assertEquals('1 min', $this->formatter->formatMinutesSv(1));
    }

    public function test_formats_minutes_under_hour(): void
    {
        $this->assertEquals('59 min', $this->formatter->formatMinutesSv(59));
        $this->assertEquals('30 min', $this->formatter->formatMinutesSv(30));
        $this->assertEquals('45 min', $this->formatter->formatMinutesSv(45));
    }

    public function test_formats_exactly_one_hour(): void
    {
        $this->assertEquals('1 tim', $this->formatter->formatMinutesSv(60));
    }

    public function test_formats_hour_with_minutes(): void
    {
        $this->assertEquals('1 tim 1 min', $this->formatter->formatMinutesSv(61));
        $this->assertEquals('1 tim 30 min', $this->formatter->formatMinutesSv(90));
        $this->assertEquals('2 tim 15 min', $this->formatter->formatMinutesSv(135));
    }

    public function test_formats_multiple_hours(): void
    {
        $this->assertEquals('2 tim', $this->formatter->formatMinutesSv(120));
        $this->assertEquals('3 tim', $this->formatter->formatMinutesSv(180));
        $this->assertEquals('8 tim', $this->formatter->formatMinutesSv(480));
    }

    public function test_formats_multiple_hours_with_minutes(): void
    {
        $this->assertEquals('2 tim 1 min', $this->formatter->formatMinutesSv(121));
        $this->assertEquals('7 tim 30 min', $this->formatter->formatMinutesSv(450));
        $this->assertEquals('8 tim 15 min', $this->formatter->formatMinutesSv(495));
    }

    public function test_handles_null_input(): void
    {
        $this->assertEquals('0 min', $this->formatter->formatMinutesSv(null));
    }

    public function test_handles_negative_input(): void
    {
        $this->assertEquals('0 min', $this->formatter->formatMinutesSv(-5));
        $this->assertEquals('0 min', $this->formatter->formatMinutesSv(-100));
    }

    public function test_formats_large_minute_values(): void
    {
        // 10,005 minutes = 166 hours 45 minutes
        $this->assertEquals('166 tim 45 min', $this->formatter->formatMinutesSv(10005));
        
        // 1,440 minutes = 24 hours (1 day)
        $this->assertEquals('24 tim', $this->formatter->formatMinutesSv(1440));
        
        // 1,441 minutes = 24 hours 1 minute
        $this->assertEquals('24 tim 1 min', $this->formatter->formatMinutesSv(1441));
    }

    public function test_edge_cases(): void
    {
        // Test edge cases around hour boundaries
        $this->assertEquals('59 min', $this->formatter->formatMinutesSv(59));
        $this->assertEquals('1 tim', $this->formatter->formatMinutesSv(60));
        $this->assertEquals('1 tim 1 min', $this->formatter->formatMinutesSv(61));
        
        // Test two hour boundary
        $this->assertEquals('1 tim 59 min', $this->formatter->formatMinutesSv(119));
        $this->assertEquals('2 tim', $this->formatter->formatMinutesSv(120));
        $this->assertEquals('2 tim 1 min', $this->formatter->formatMinutesSv(121));
    }
}