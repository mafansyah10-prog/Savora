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
    ];

    public function isStoreOpen(): bool
    {
        if (isset($this->attributes['is_store_open']) && !$this->is_store_open) {
            return false;
        }

        // Check special date schedules
        $todayDate = now()->format('Y-m-d');
        $specialSchedules = $this->special_schedules ?? [];
        $todaySpecial = collect($specialSchedules)->firstWhere('date', $todayDate);
        if ($todaySpecial) {
            if (!($todaySpecial['is_open'] ?? false)) {
                return false;
            }
            if (!empty($todaySpecial['open_time']) && !empty($todaySpecial['close_time'])) {
                return $this->isTimeWithinRange(now()->format('H:i:s'), $todaySpecial['open_time'], $todaySpecial['close_time']);
            }
            return true;
        }

        // Check weekly schedule
        $dayOfWeek = strtolower(now()->format('l'));
        $weeklySchedule = $this->weekly_schedule ?? [];
        if (isset($weeklySchedule[$dayOfWeek])) {
            $daySchedule = $weeklySchedule[$dayOfWeek];
            if (!($daySchedule['is_open'] ?? false)) {
                return false;
            }
            if (!empty($daySchedule['open_time']) && !empty($daySchedule['close_time'])) {
                return $this->isTimeWithinRange(now()->format('H:i:s'), $daySchedule['open_time'], $daySchedule['close_time']);
            }
            return true;
        }

        // Fallback to global setting
        if (!empty($this->store_open_time) && !empty($this->store_close_time)) {
            return $this->isTimeWithinRange(now()->format('H:i:s'), $this->store_open_time, $this->store_close_time);
        }

        return true;
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
        if (isset($this->attributes['is_store_open']) && !$this->is_store_open) {
            return 'Tutup (Manual)';
        }

        $todayDate = now()->format('Y-m-d');
        $specialSchedules = $this->special_schedules ?? [];
        $todaySpecial = collect($specialSchedules)->firstWhere('date', $todayDate);
        if ($todaySpecial) {
            if (!($todaySpecial['is_open'] ?? false)) {
                return !empty($todaySpecial['note']) ? 'Tutup: ' . $todaySpecial['note'] : 'Tutup (Hari Khusus)';
            }
            if (!empty($todaySpecial['open_time']) && !empty($todaySpecial['close_time'])) {
                $hours = substr($todaySpecial['open_time'], 0, 5) . ' - ' . substr($todaySpecial['close_time'], 0, 5);
                return !empty($todaySpecial['note']) ? 'Jam Buka Khusus: ' . $hours . ' (' . $todaySpecial['note'] . ')' : 'Jam Buka Khusus: ' . $hours;
            }
            return !empty($todaySpecial['note']) ? 'Buka (' . $todaySpecial['note'] . ')' : 'Buka';
        }

        $dayOfWeek = strtolower(now()->format('l'));
        $weeklySchedule = $this->weekly_schedule ?? [];
        if (isset($weeklySchedule[$dayOfWeek])) {
            $daySchedule = $weeklySchedule[$dayOfWeek];
            if (!($daySchedule['is_open'] ?? false)) {
                return 'Tutup (Hari ' . ucfirst($this->translateDay($dayOfWeek)) . ')';
            }
            if (!empty($daySchedule['open_time']) && !empty($daySchedule['close_time'])) {
                return 'Jam Operasional: ' . substr($daySchedule['open_time'], 0, 5) . ' - ' . substr($daySchedule['close_time'], 0, 5);
            }
        }

        if (!empty($this->store_open_time) && !empty($this->store_close_time)) {
            return 'Jam Operasional: ' . substr($this->store_open_time, 0, 5) . ' - ' . substr($this->store_close_time, 0, 5);
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
