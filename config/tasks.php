<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Task Rollover Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls whether overdue tasks from previous days should
    | be automatically moved to today's date. When enabled, non-daily tasks
    | that are overdue will be rescheduled to the current date.
    |
    */

    'rollover_enabled' => env('TASK_ROLLOVER_ENABLED', true),
];