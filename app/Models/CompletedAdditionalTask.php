<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompletedAdditionalTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'user_id',
        'task_template_id',
        'task_name',
        'completed_date',
        'notes',
    ];

    protected $casts = [
        'completed_date' => 'date',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('completed_date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
