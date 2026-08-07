<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'        => 'boolean',
        'is_hidden'        => 'boolean',
        'expires_at'       => 'datetime',
        'value'            => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'limit_per_user'   => 'integer',
    ];

    /**
     * Check if the voucher is valid for a given order amount.
     */
    public function isValidForAmount($amount): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($amount < $this->min_order_amount) {
            return false;
        }

        if ($this->user_id !== null) {
            if (!auth()->check() || auth()->id() !== $this->user_id) {
                return false;
            }
        }

        if ($this->rank !== null) {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            $rankKeys = array_keys(\App\Models\User::$ranks);
            $userRankIndex = array_search($user->rank ?? 'reguler', $rankKeys);
            $voucherRankIndex = array_search($this->rank, $rankKeys);
            if ($userRankIndex === false || $voucherRankIndex === false || $userRankIndex < $voucherRankIndex) {
                return false;
            }
        }
        
        if (auth()->check()) {
            $limitPerUser = $this->limit_per_user;
            if ($limitPerUser === null && (str_starts_with($this->code, 'BARU-') || $this->user_id !== null)) {
                $limitPerUser = 1;
            }
            if ($limitPerUser !== null) {
                $userUsageCount = auth()->user()->orders()
                    ->where('voucher_code', $this->code)
                    ->where('status', '!=', 'cancelled')
                    ->count();
                if ($userUsageCount >= $limitPerUser) {
                    return false;
                }
            }
        }

        if ($this->usage_limit !== null && $this->orders()->count() >= $this->usage_limit) {
            return false;
        }

        // Syarat: Total belanja harus di atas nilai potongan voucher
        if ($amount <= $this->calculateDiscount($amount)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount based on order total.
     */
    public function calculateDiscount($amount): float
    {
        $discount = 0.0;

        if ($this->type === 'fixed') {
            $discount = (float) $this->value;
        } elseif ($this->type === 'percent') {
            $discount = $amount * ((float) $this->value / 100);
        }

        // Discount cannot exceed the actual amount
        return min($discount, $amount);
    }

    public function getNameAttribute(): string
    {
        $rankLabel = '';
        if ($this->rank) {
            $rankLabel = ' Member ' . (\App\Models\User::$ranks[$this->rank]['label'] ?? ucfirst($this->rank));
        }
        
        $prefix = '';
        if (str_starts_with($this->code, 'BARU-') || $this->user_id !== null) {
            $prefix = 'Voucher Pengguna Baru — ';
        }
        
        if ($this->type === 'fixed') {
            return $prefix . 'Potongan Rp ' . number_format($this->value, 0, ',', '.') . $rankLabel;
        } else {
            return $prefix . 'Diskon ' . number_format($this->value, 0) . '%' . $rankLabel;
        }
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'voucher_code', 'code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
