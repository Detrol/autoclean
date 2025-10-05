<?php

namespace App\Support;

class TimeFormatter
{
    /**
     * Format minutes as Swedish time string
     * 
     * @param int|null $minutes
     * @return string
     */
    public function formatMinutesSv(?int $minutes): string
    {
        $m = max(0, (int)($minutes ?? 0));
        $h = intdiv($m, 60);
        $r = $m % 60;

        if ($h === 0) {
            return $r . ' min';
        }

        if ($r === 0) {
            return $h . ' tim';
        }

        return $h . ' tim ' . $r . ' min';
    }
}