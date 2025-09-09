<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'name',
        'description',
        'interval_type',
        'interval_value',
        'start_date',
        'recurrence_pattern',
        'end_date',
        'occurrences',
        'default_due_time',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'recurrence_pattern' => 'array',
        'end_date' => 'date',
        'default_due_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TaskSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Få startdatum för schemaläggning (fallback till idag om inte satt)
     */
    public function getEffectiveStartDate(): \Carbon\Carbon
    {
        return $this->start_date ? $this->start_date->copy() : now()->startOfDay();
    }

    /**
     * Kontrollera om uppgiften har ett slutdatum/begränsning
     */
    public function hasEndCondition(): bool
    {
        return $this->end_date || $this->occurrences;
    }

    /**
     * Få en läsbar beskrivning av intervallet
     */
    public function getIntervalDescription(): string
    {
        $pattern = $this->recurrence_pattern;
        
        switch ($this->interval_type) {
            case 'daily':
                if ($pattern && isset($pattern['weekdaysOnly']) && $pattern['weekdaysOnly']) {
                    return 'Vardagar';
                }
                return $this->interval_value == 1 ? 'Dagligen' : "Varje {$this->interval_value} dagar";
                
            case 'weekly':
                $interval = $this->interval_value == 1 ? '' : "varannan " . ($this->interval_value == 2 ? '' : "var {$this->interval_value}:e ");
                if ($pattern && isset($pattern['daysOfWeek'])) {
                    $days = $this->formatWeekdays($pattern['daysOfWeek']);
                    return "Varje {$interval}vecka på {$days}";
                }
                return "Varje {$interval}vecka";
                
            case 'monthly':
                $interval = $this->interval_value == 1 ? '' : "var {$this->interval_value}:e ";
                if ($pattern && isset($pattern['dayOfMonth'])) {
                    return "Den {$pattern['dayOfMonth']} {$interval}månad";
                }
                if ($pattern && isset($pattern['weekdayOfMonth'])) {
                    $ordinal = ['första', 'andra', 'tredje', 'fjärde', 'sista'][$pattern['weekdayOfMonth']['ordinal'] - 1] ?? '';
                    $day = $pattern['weekdayOfMonth']['day'];
                    return "{$ordinal} {$day}en {$interval}månad";
                }
                return "Månadsvis";
                
            case 'yearly':
                return 'Årligen';
                
            case 'custom':
                return 'Anpassat schema';
                
            default:
                return 'Okänt intervall';
        }
    }

    /**
     * Formatera veckodagar för visning
     */
    private function formatWeekdays(array $days): string
    {
        $dayNames = [
            'monday' => 'måndag',
            'tuesday' => 'tisdag', 
            'wednesday' => 'onsdag',
            'thursday' => 'torsdag',
            'friday' => 'fredag',
            'saturday' => 'lördag',
            'sunday' => 'söndag'
        ];
        
        $translatedDays = array_map(fn($day) => $dayNames[$day] ?? $day, $days);
        
        if (count($translatedDays) > 1) {
            $last = array_pop($translatedDays);
            return implode(', ', $translatedDays) . ' och ' . $last;
        }
        
        return $translatedDays[0] ?? '';
    }
}
