<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rollover overdue tasks first (at midnight)
// This command will always run, but will check the config internally
app(Schedule::class)->command('tasks:rollover-overdue')->dailyAt('00:00');

// Then generate new tasks (5 minutes later to avoid race conditions)
app(Schedule::class)->command('tasks:generate --date=tomorrow')->dailyAt('00:05')->withoutOverlapping();

// Auto clock-out users who forgot to clock out
app(Schedule::class)->command('timelogs:auto-clock-out')->hourly();
