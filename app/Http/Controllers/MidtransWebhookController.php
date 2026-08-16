<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        Log::info('Midtrans Webhook received:', $request->all());

        $setting = Setting::getGlobal();
        $serverKey = $setting->midtrans_server_key ?: config('services.midtrans.server_key');

        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $orderId = $request->input('order_id');
        $signatureKey = $request->input('signature_key');

        // 1. Verifikasi Signature Key untuk keamanan
        $localSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($localSignature !== $signatureKey) {
            Log::warning('Midtrans Webhook: Signature mismatch. Order ID: '.$orderId);

            return response()->json(['success' => false, 'message' => 'Signature mismatch'], 400);
        }

        // 2. Cari order berdasarkan order_id
        $order = Order::find($orderId);

        if (! $order) {
            Log::warning('Midtrans Webhook: Order not found: '.$orderId);

            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // 3. Tangani Status Transaksi
        $transactionStatus = $request->input('transaction_status');
        $paymentType = $request->input('payment_type');

        if ($transactionStatus == 'capture') {
            if ($paymentType == 'credit_card') {
                if ($request->input('fraud_status') == 'challenge') {
                    // Pembayaran butuh review manual
                    $order->update(['status' => 'pending']);
                } else {
                    $this->markAsPaid($order, $request);
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $this->markAsPaid($order, $request);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            if ($order->status === 'pending') {
                $order->update(['status' => 'cancelled']);
                Log::info("Midtrans Webhook: Order #{$order->id} successfully cancelled.");
            }
        }

        return response()->json(['success' => true]);
    }

    private function markAsPaid(Order $order, Request $request)
    {
        if ($order->status === 'pending') {
            $order->update([
                'status' => 'paid',
                'payment_proof_analysis' => [
                    'status' => 'success',
                    'method' => $request->input('payment_type', 'midtrans'),
                    'transaction_id' => $request->input('transaction_id'),
                    'completed_at' => $request->input('settlement_time') ?: now()->toDateTimeString(),
                    'verified_by' => 'Midtrans Payment Gateway Webhook',
                ],
            ]);
            Log::info("Midtrans Webhook: Order #{$order->id} successfully updated to paid.");
        }
    }
}
