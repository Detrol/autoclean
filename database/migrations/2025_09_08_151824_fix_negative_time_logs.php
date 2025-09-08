<?php

use App\Models\TimeLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fixa alla negativa time logs
        $timeLogs = TimeLog::whereNotNull('clock_out')
            ->where('total_minutes', '<', 0)
            ->get();
        
        foreach ($timeLogs as $timeLog) {
            // Räkna om korrekt antal minuter
            if ($timeLog->clock_out && $timeLog->clock_in) {
                $minutes = $timeLog->clock_in->diffInMinutes($timeLog->clock_out);
                $timeLog->total_minutes = abs($minutes);
                $timeLog->save();
            }
        }
        
        // Fixa alla time logs som har clock_out men 0 eller null minuter
        TimeLog::whereNotNull('clock_out')
            ->where(function($query) {
                $query->where('total_minutes', 0)
                      ->orWhereNull('total_minutes');
            })
            ->each(function($timeLog) {
                if ($timeLog->clock_out && $timeLog->clock_in) {
                    $minutes = $timeLog->clock_in->diffInMinutes($timeLog->clock_out);
                    $timeLog->total_minutes = abs($minutes);
                    $timeLog->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Inget att göra här
    }
};
