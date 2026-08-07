<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $guarded = [];

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
            $setting = self::first() ?? new self();
            return $setting;
        }

        $cached = Cache::rememberForever('global_settings', function () {
            $setting = self::first() ?? new self();
            return [
                'attributes' => $setting->getAttributes(),
                'exists' => $setting->exists,
            ];
        });

        $setting = new self();
        $setting->setRawAttributes($cached['attributes']);
        $setting->exists = $cached['exists'];
        return $setting;
    }
}
