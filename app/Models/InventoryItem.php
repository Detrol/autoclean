<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit',
        'default_reorder_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_reorder_level' => 'integer',
    ];

    public function stationInventory(): HasMany
    {
        return $this->hasMany(StationInventory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedUnitAttribute(): string
    {
        return match ($this->unit) {
            'pcs' => 'st',
            'liters' => 'l',
            'meters' => 'm',
            'kg' => 'kg',
            default => $this->unit,
        };
    }
}
