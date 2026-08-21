<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;

class BirthdayVoucherService
{
    public function issueVoucherIfEligible(User $user): ?Voucher
    {
        $setting = Setting::getGlobal();
        if (!$setting->birthday_voucher_is_active) {
            return null;
        }

        if (!$user->birth_date) {
            return null;
        }

        // Check if today matches birthday (month and day in Asia/Jakarta)
        $today = now()->timezone('Asia/Jakarta');
        $birthDate = $user->birth_date;

        if ($today->format('m-d') !== $birthDate->format('m-d')) {
            return null;
        }

        $currentYear = $today->format('Y');
        $voucherCode = "HBD-{$user->id}-{$currentYear}";

        // Check if this voucher was already issued
        $alreadyIssued = Voucher::where('code', $voucherCode)->exists();
        if ($alreadyIssued) {
            return null;
        }

        try {
            return Voucher::create([
                'code' => $voucherCode,
                'type' => $setting->birthday_voucher_type ?? 'fixed',
                'value' => $setting->birthday_voucher_value ?? 25000,
                'min_order_amount' => $setting->birthday_voucher_min_order_amount ?? 50000,
                'is_active' => true,
                'is_hidden' => false,
                'expires_at' => now()->addDays((int) ($setting->birthday_voucher_expires_in_days ?? 7)),
                'user_id' => $user->id,
                'usage_limit' => 1,
                'limit_per_user' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to generate birthday voucher for User ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }
}
