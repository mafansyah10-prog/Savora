<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PakasirWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        Log::info('Pakasir Webhook received:', $request->all());

        $setting = Setting::getGlobal();

        // 1. Verifikasi project slug (case-insensitive)
        if (strcasecmp(trim($request->input('project')), trim($setting->pakasir_project)) !== 0) {
            Log::warning('Pakasir Webhook: Project mismatch. Expected ' . $setting->pakasir_project . ', got ' . $request->input('project'));
            return response()->json(['success' => false, 'message' => 'Project mismatch'], 400);
        }

        // 2. Cari order berdasarkan order_id
        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        if (!$order) {
            Log::warning('Pakasir Webhook: Order not found: ' . $orderId);
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // 3. Verifikasi nominal pembayaran (toleransi Rp 2.000 untuk kode unik pembayaran jika ada)
        $amount = (int) $request->input('amount');
        if (abs((int) $order->total_amount - $amount) > 2000) {
            Log::warning("Pakasir Webhook: Amount mismatch for order #{$order->id}. Expected {$order->total_amount}, got {$amount}");
            return response()->json(['success' => false, 'message' => 'Amount mismatch'], 400);
        }

        // 4. Update status pesanan jika status pembayaran sukses
        $incomingStatus = strtolower($request->input('status'));
        if (in_array($incomingStatus, ['completed', 'success', 'paid'])) {
            if ($order->status === 'pending') {
                $order->update([
                    'status' => 'paid',
                    'payment_proof_analysis' => [
                        'status' => 'success',
                        'method' => $request->input('payment_method', 'pakasir'),
                        'completed_at' => $request->input('completed_at'),
                        'verified_by' => 'Pakasir Payment Gateway Webhook'
                    ]
                ]);
                Log::info("Pakasir Webhook: Order #{$order->id} successfully updated to paid.");

                // Kirim notifikasi ke lonceng Admin Panel
                try {
                    $admins = \App\Models\User::where('can_access_admin_panel', true)->orWhere('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        \Filament\Notifications\Notification::make()
                            ->title('Pembayaran Lunas via Pakasir! ⚡')
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->body("Pesanan #{$order->id} dari {$order->customer_name} telah dibayar via Pakasir.")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('Lihat Pesanan')
                                    ->url('/admin/orders/' . $order->id . '/edit'),
                            ])
                            ->sendToDatabase($admin);
                    }
                } catch (\Throwable $e) {
                    // Ignore if notification fails silently
                }
            } else {
                Log::info("Pakasir Webhook: Order #{$order->id} was already in status: {$order->status}. No change applied.");
            }
        }

        return response()->json(['success' => true]);
    }
}
