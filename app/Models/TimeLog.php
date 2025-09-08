<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'station_id',
        'clock_in',
        'clock_out',
        'date',
        'total_minutes',
        'notes',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('clock_out');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('clock_out');
    }

    public function clockOut($notes = null)
    {
        $clockOut = now();
        // Beräkna minuter från clock_in till clock_out
        $clockIn = $this->clock_in;
        
        // Säkerställ att vi alltid får positiva minuter
        if ($clockOut->greaterThan($clockIn)) {
            $totalMinutes = $clockIn->diffInMinutes($clockOut);
        } else {
            // Om något är fel med tiderna, sätt 0
            $totalMinutes = 0;
        }

        $this->update([
            'clock_out' => $clockOut,
            'total_minutes' => $totalMinutes,
            'notes' => $notes,
        ]);

        return $this;
    }

    public function getTotalHoursAttribute()
    {
        if (!$this->total_minutes) {
            return 0;
        }

        return round($this->total_minutes / 60, 2);
    }

    public function isActive()
    {
        return is_null($this->clock_out);
    }
}
