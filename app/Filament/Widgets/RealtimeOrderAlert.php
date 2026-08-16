<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class RealtimeOrderAlert extends Widget
{
    protected string $view = 'filament.widgets.realtime-order-alert';

    protected int|string|array $columnSpan = 'full';

    public ?int $lastKnownOrderId = null;

    public ?int $lastKnownPaidCount = null;

    public function mount()
    {
        $this->lastKnownOrderId = Order::max('id') ?? 0;
        $this->lastKnownPaidCount = Order::where('status', 'paid')->count();
    }

    public function checkNewOrders()
    {
        // 1. Cek Pesanan Baru Masuk
        $latestOrder = Order::latest('id')->first();

        if ($latestOrder && $this->lastKnownOrderId !== null && $latestOrder->id > $this->lastKnownOrderId) {
            $newOrdersCount = Order::where('id', '>', $this->lastKnownOrderId)->count();
            $this->lastKnownOrderId = $latestOrder->id;

            $this->dispatch('play-order-sound', [
                'id' => $latestOrder->id,
                'customer' => $latestOrder->customer_name,
                'amount' => 'Rp '.number_format($latestOrder->total_amount, 0, ',', '.'),
                'count' => $newOrdersCount,
            ]);

            Notification::make()
                ->title('🎉 ORDERAN BARU MASUK!')
                ->icon('heroicon-o-shopping-bag')
                ->iconColor('warning')
                ->body("Pesanan #{$latestOrder->id} dari {$latestOrder->customer_name} sebesar Rp ".number_format($latestOrder->total_amount, 0, ',', '.'))
                ->actions([
                    Action::make('view')
                        ->label('Lihat Pesanan')
                        ->url('/admin/orders/'.$latestOrder->id.'/edit'),
                ])
                ->persistent()
                ->send();
        }

        // 2. Cek Pembayaran Lunas Masuk
        $currentPaidCount = Order::where('status', 'paid')->count();
        if ($this->lastKnownPaidCount !== null && $currentPaidCount > $this->lastKnownPaidCount) {
            $latestPaidOrder = Order::where('status', 'paid')->latest('updated_at')->first();
            $this->lastKnownPaidCount = $currentPaidCount;

            if ($latestPaidOrder) {
                $this->dispatch('play-order-sound', [
                    'id' => $latestPaidOrder->id,
                    'customer' => $latestPaidOrder->customer_name,
                ]);

                Notification::make()
                    ->title('💳 PEMBAYARAN LUNAS MASUK!')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->body("Pesanan #{$latestPaidOrder->id} dari {$latestPaidOrder->customer_name} telah LUNAS.")
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Pesanan')
                            ->url('/admin/orders/'.$latestPaidOrder->id.'/edit'),
                    ])
                    ->persistent()
                    ->send();
            }
        }
    }
}
