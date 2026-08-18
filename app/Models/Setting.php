<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'manual_payment_methods' => 'array',
        'is_store_open' => 'boolean',
    ];

    public function isStoreOpen(): bool
    {
        if (isset($this->attributes['is_store_open']) && !$this->is_store_open) {
            return false;
        }

        if (!empty($this->store_open_time) && !empty($this->store_close_time)) {
            $now = now()->format('H:i:s');
            $open = $this->store_open_time;
            $close = $this->store_close_time;

            if ($open <= $close) {
                // Same day operation
                return $now >= $open && $now <= $close;
            } else {
                // Overnight operation (e.g. 21:00 to 02:00)
                return $now >= $open || $now <= $close;
            }
        }

        return true;
    }

    // Clear cache whenever setting is updated
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('global_settings');
        });
    }

    /**
     * Get global settings safely via cache
     */
    public static function getGlobal()
    {
        if (app()->environment('testing')) {
            $setting = self::first() ?? new self;

            return $setting;
        }

        $cached = Cache::rememberForever('global_settings', function () {
            $setting = self::first() ?? new self;

            return [
                'attributes' => $setting->getAttributes(),
                'exists' => $setting->exists,
            ];
        });

        $setting = new self;
        $setting->setRawAttributes($cached['attributes']);
        $setting->exists = $cached['exists'];

        return $setting;
    }
}
