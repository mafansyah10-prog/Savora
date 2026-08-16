<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public static function getSnapToken(Order $order): ?string
    {
        $setting = Setting::getGlobal();

        if (! $setting->midtrans_is_active || $order->status !== 'pending') {
            return null;
        }

        try {
            Config::$serverKey = $setting->midtrans_server_key ?: config('services.midtrans.server_key');
            Config::$isProduction = (bool) ($setting->midtrans_is_production);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name ?: (auth()->check() ? auth()->user()->name : 'Customer'),
                    'email' => auth()->check() ? auth()->user()->email : 'customer@savora.com',
                    'phone' => $order->customer_phone ?: (auth()->check() ? auth()->user()->whatsapp_number : null),
                ],
            ];

            return Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap Token Error for Order #'.$order->id.': '.$e->getMessage());

            return null;
        }
    }
}
