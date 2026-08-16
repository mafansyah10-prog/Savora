<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'cost', 'is_active'];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope only active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all active zones for customer dropdown.
     */
    public static function getActiveZones(): array
    {
        return static::active()->orderBy('name')->get()->toArray();
    }

    /**
     * Format cost as Indonesian Rupiah.
     */
    public function getFormattedCostAttribute(): string
    {
        return 'Rp '.number_format($this->cost, 0, ',', '.');
    }
}
