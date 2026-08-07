<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class RealtimeOrderAlert extends Widget
{
    protected string $view = 'filament.widgets.realtime-order-alert';

    protected int | string | array $columnSpan = 'full';

    public ?int $lastKnownOrderId = null;

    public function mount()
    {
        $this->lastKnownOrderId = Order::max('id') ?? 0;
    }

    public function checkNewOrders()
    {
        $latestOrder = Order::latest('id')->first();

        if ($latestOrder && $this->lastKnownOrderId !== null && $latestOrder->id > $this->lastKnownOrderId) {
            $newOrdersCount = Order::where('id', '>', $this->lastKnownOrderId)->count();
            $this->lastKnownOrderId = $latestOrder->id;

            $this->dispatch('play-order-sound', [
                'id' => $latestOrder->id,
                'customer' => $latestOrder->customer_name,
                'amount' => 'Rp ' . number_format($latestOrder->total_amount, 0, ',', '.'),
                'count' => $newOrdersCount,
            ]);

            \Filament\Notifications\Notification::make()
                ->title('🎉 ORDERAN BARU MASUK!')
                ->icon('heroicon-o-shopping-bag')
                ->iconColor('success')
                ->body("Pesanan #{$latestOrder->id} dari {$latestOrder->customer_name} sebesar Rp " . number_format($latestOrder->total_amount, 0, ',', '.'))
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Lihat Pesanan')
                        ->url('/admin/orders/' . $latestOrder->id . '/edit'),
                ])
                ->persistent()
                ->send();

            // Also save to database notifications for all admins
            try {
                $admins = \App\Models\User::where('can_access_admin_panel', true)->orWhere('role', 'admin')->get();
                foreach ($admins as $admin) {
                    \Filament\Notifications\Notification::make()
                        ->title('Pesanan Baru Masuk! 🎉')
                        ->icon('heroicon-o-shopping-bag')
                        ->iconColor('success')
                        ->body("Pesanan #{$latestOrder->id} dari {$latestOrder->customer_name} sebesar Rp " . number_format($latestOrder->total_amount, 0, ',', '.'))
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('Lihat Pesanan')
                                ->url('/admin/orders/' . $latestOrder->id . '/edit'),
                        ])
                        ->sendToDatabase($admin);
                }
            } catch (\Throwable $e) {
                // Ignore silent database notification errors
            }
        }
    }
}
