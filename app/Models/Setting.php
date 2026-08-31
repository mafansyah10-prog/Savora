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
        'weekly_schedule' => 'array',
        'special_schedules' => 'array',
        'birthday_voucher_is_active' => 'boolean',
        'birthday_voucher_value' => 'decimal:2',
        'birthday_voucher_min_order_amount' => 'decimal:2',
        'birthday_voucher_expires_in_days' => 'integer',
        'promo_popup_is_active' => 'boolean',
        'promo_popup_product_id' => 'integer',
        'promo_popup_duration_seconds' => 'integer',
    ];

    public function isStoreOpen(): bool
    {
        if (isset($this->attributes['is_store_open']) && ! $this->is_store_open) {
            return false;
        }

        // 1. Check special date schedules (Always takes priority if today matches a special schedule)
        $todayDate = now()->format('Y-m-d');
        $specialSchedules = $this->special_schedules ?? [];
        $todaySpecial = collect($specialSchedules)->firstWhere('date', $todayDate);
        if ($todaySpecial) {
            if (! ($todaySpecial['is_open'] ?? false)) {
                return false;
            }
            if (! empty($todaySpecial['open_time']) && ! empty($todaySpecial['close_time'])) {
                return $this->isTimeWithinRange(now()->format('H:i:s'), $todaySpecial['open_time'], $todaySpecial['close_time']);
            }

            return true;
        }

        $mode = $this->store_hours_mode ?? 'global';

        // 2. Check weekly schedule
        if ($mode === 'weekly') {
            $dayOfWeek = strtolower(now()->format('l'));
            $weeklySchedule = $this->weekly_schedule ?? [];
            if (isset($weeklySchedule[$dayOfWeek])) {
                $daySchedule = $weeklySchedule[$dayOfWeek];
                if (! ($daySchedule['is_open'] ?? false)) {
                    return false;
                }
                if (! empty($daySchedule['open_time']) && ! empty($daySchedule['close_time'])) {
                    return $this->isTimeWithinRange(now()->format('H:i:s'), $daySchedule['open_time'], $daySchedule['close_time']);
                }

                return true;
            }
        }

        // 3. Fallback to global setting (used in 'global' mode or if weekly has no match)
        if (! empty($this->store_open_time) && ! empty($this->store_close_time)) {
            return $this->isTimeWithinRange(now()->format('H:i:s'), $this->store_open_time, $this->store_close_time);
        }

        return true;
    }

    public function getTodayHours(): array
    {
        $todayDate = now()->format('Y-m-d');
        $specialSchedules = $this->special_schedules ?? [];
        $todaySpecial = collect($specialSchedules)->firstWhere('date', $todayDate);
        if ($todaySpecial) {
            return [
                'open_time' => !empty($todaySpecial['open_time']) ? $todaySpecial['open_time'] : '00:00:00',
                'close_time' => !empty($todaySpecial['close_time']) ? $todaySpecial['close_time'] : '23:59:59',
            ];
        }

        $mode = $this->store_hours_mode ?? 'global';
        if ($mode === 'weekly') {
            $dayOfWeek = strtolower(now()->format('l'));
            $weeklySchedule = $this->weekly_schedule ?? [];
            if (isset($weeklySchedule[$dayOfWeek])) {
                $daySchedule = $weeklySchedule[$dayOfWeek];
                return [
                    'open_time' => !empty($daySchedule['open_time']) ? $daySchedule['open_time'] : '00:00:00',
                    'close_time' => !empty($daySchedule['close_time']) ? $daySchedule['close_time'] : '23:59:59',
                ];
            }
        }

        return [
            'open_time' => !empty($this->store_open_time) ? $this->store_open_time : '00:00:00',
            'close_time' => !empty($this->store_close_time) ? $this->store_close_time : '23:59:59',
        ];
    }

    private function isTimeWithinRange(string $now, string $open, string $close): bool
    {
        if ($open <= $close) {
            return $now >= $open && $now <= $close;
        } else {
            return $now >= $open || $now <= $close;
        }
    }

    public function getStoreStatusText(): string
    {
        if (isset($this->attributes['is_store_open']) && ! $this->is_store_open) {
            return 'Tutup (Manual)';
        }

        // 1. Check special date schedules
        $todayDate = now()->format('Y-m-d');
        $specialSchedules = $this->special_schedules ?? [];
        $todaySpecial = collect($specialSchedules)->firstWhere('date', $todayDate);
        if ($todaySpecial) {
            if (! ($todaySpecial['is_open'] ?? false)) {
                return ! empty($todaySpecial['note']) ? 'Tutup: '.$todaySpecial['note'] : 'Tutup (Hari Khusus)';
            }
            if (! empty($todaySpecial['open_time']) && ! empty($todaySpecial['close_time'])) {
                $hours = substr($todaySpecial['open_time'], 0, 5).' - '.substr($todaySpecial['close_time'], 0, 5);

                return ! empty($todaySpecial['note']) ? 'Jam Buka Khusus: '.$hours.' ('.$todaySpecial['note'].')' : 'Jam Buka Khusus: '.$hours;
            }

            return ! empty($todaySpecial['note']) ? 'Buka ('.$todaySpecial['note'].')' : 'Buka';
        }

        $mode = $this->store_hours_mode ?? 'global';

        // 2. Check weekly schedule
        if ($mode === 'weekly') {
            $dayOfWeek = strtolower(now()->format('l'));
            $weeklySchedule = $this->weekly_schedule ?? [];
            if (isset($weeklySchedule[$dayOfWeek])) {
                $daySchedule = $weeklySchedule[$dayOfWeek];
                if (! ($daySchedule['is_open'] ?? false)) {
                    return 'Tutup (Hari '.ucfirst($this->translateDay($dayOfWeek)).')';
                }
                if (! empty($daySchedule['open_time']) && ! empty($daySchedule['close_time'])) {
                    return 'Jam Operasional: '.substr($daySchedule['open_time'], 0, 5).' - '.substr($daySchedule['close_time'], 0, 5);
                }
            }
        }

        // 3. Fallback to global
        if (! empty($this->store_open_time) && ! empty($this->store_close_time)) {
            return 'Jam Operasional: '.substr($this->store_open_time, 0, 5).' - '.substr($this->store_close_time, 0, 5);
        }

        return 'Buka';
    }

    private function translateDay(string $day): string
    {
        $days = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        return $days[$day] ?? $day;
    }

    // Clear cache whenever setting is updated
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('global_settings');
        });
    }

    public function promoProduct()
    {
        return $this->belongsTo(Product::class, 'promo_popup_product_id');
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
