<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'inventory_item_id',
        'user_id',
        'type',
        'quantity',
        'balance_after',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('inventory_item_id', $itemId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'add' => 'Tillagt',
            'remove' => 'Förbrukat',
            'adjust' => 'Justerat',
            'check' => 'Kontrollerat',
            default => $this->type,
        };
    }

    public function getSignedQuantityAttribute(): string
    {
        $prefix = match ($this->type) {
            'add' => '+',
            'remove' => '-',
            'adjust' => $this->quantity >= 0 ? '+' : '',
            default => '',
        };

        return $prefix.$this->quantity;
    }
}
