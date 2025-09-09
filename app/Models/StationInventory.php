<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StationInventory extends Model
{
    use HasFactory;

    protected $table = 'station_inventory';

    protected $fillable = [
        'station_id',
        'inventory_item_id',
        'current_quantity',
        'minimum_quantity',
        'last_checked',
        'notes',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'last_checked' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_item_id', 'inventory_item_id')
            ->where('station_id', $this->station_id);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_quantity <= minimum_quantity');
    }

    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_quantity <= $this->minimum_quantity;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}
