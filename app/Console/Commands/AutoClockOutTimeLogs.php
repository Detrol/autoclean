<?php

namespace App\Console\Commands;

use App\Models\TimeLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoClockOutTimeLogs extends Command
{
    protected $signature = 'timelogs:auto-clock-out';

    protected $description = 'Automatically clock out users who forgot to clock out after the configured max hours';

    public function handle(): int
    {
        if (! settings('auto_clock_out_enabled', true)) {
            $this->info('Auto clock-out is disabled.');

            return 0;
        }

        $maxHours = (int) settings('auto_clock_out_hours', 12);
        $cutoff = now()->subHours($maxHours);

        $staleLogs = TimeLog::query()
            ->active()
            ->where('clock_in', '<=', $cutoff)
            ->get();

        if ($staleLogs->isEmpty()) {
            $this->info('No stale time logs found.');

            return 0;
        }

        $count = 0;
        foreach ($staleLogs as $log) {
            $clockOut = $log->clock_in->copy()->addHours($maxHours);
            $totalMinutes = $maxHours * 60;

            $log->update([
                'clock_out' => $clockOut,
                'total_minutes' => $totalMinutes,
                'notes' => "Automatisk utklocking (max {$maxHours}h)",
            ]);

            Log::info("Auto clock-out: TimeLog #{$log->id} for user #{$log->user_id}, clock_in={$log->clock_in}, clock_out={$clockOut}");
            $count++;
        }

        $this->info("Auto-clocked out {$count} stale time log(s).");

        return 0;
    }
}
