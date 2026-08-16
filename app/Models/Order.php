<?php

namespace App\Models;

use App\Filament\Resources\OrderResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'items' => 'array',
        'stock_deducted' => 'boolean',
        'payment_proof_analysis' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Notifikasi Otomatis saat Pesanan Baru Dibuat
        static::created(function (Order $order) {
            try {
                $admins = User::where('can_access_admin_panel', true)->orWhere('role', 'admin')->get();
                if ($admins->isEmpty()) {
                    $admins = User::all();
                }
                foreach ($admins as $admin) {
                    Notification::make()
                        ->title('Pesanan Baru Masuk! 🎉')
                        ->icon('heroicon-o-shopping-bag')
                        ->iconColor('warning')
                        ->body("Pesanan #{$order->id} dari {$order->customer_name} sebesar Rp ".number_format($order->total_amount, 0, ',', '.'))
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Detail')
                                ->url(OrderResource::getUrl('edit', ['record' => $order])),
                        ])
                        ->sendToDatabase($admin);
                }
            } catch (\Throwable $e) {
                // Ignore silent notification errors
            }
        });

        // When an order status changes to paid, shipped, or completed, update user's total spent & rank
        static::updated(function (Order $order) {
            if ($order->isDirty('status') && in_array($order->status, ['paid', 'shipped', 'completed']) && $order->user_id) {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->updateTotalSpent();
                }
            }

            // Notifikasi Otomatis saat Pesanan Lunas
            if ($order->isDirty('status') && $order->status === 'paid') {
                try {
                    $admins = User::where('can_access_admin_panel', true)->orWhere('role', 'admin')->get();
                    if ($admins->isEmpty()) {
                        $admins = User::all();
                    }
                    foreach ($admins as $admin) {
                        Notification::make()
                            ->title('Pembayaran Lunas! 💳')
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->body("Pesanan #{$order->id} dari {$order->customer_name} telah LUNAS.")
                            ->actions([
                                Action::make('view')
                                    ->label('Lihat Detail')
                                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
                            ])
                            ->sendToDatabase($admin);
                    }
                } catch (\Throwable $e) {
                    // Ignore silent notification errors
                }
            }

            // Deduct stock if paid, shipped, or completed and not yet deducted
            if ($order->isDirty('status') && in_array($order->status, ['paid', 'shipped', 'completed']) && ! $order->stock_deducted) {
                $items = $order->items ?? [];

                try {
                    DB::transaction(function () use ($items) {
                        foreach ($items as $item) {
                            $productId = $item['product_id'] ?? null;
                            $quantity = $item['quantity'] ?? 0;

                            if ($productId && $quantity > 0) {
                                // Mengambil data produk dengan Pessimistic Locking
                                $product = Product::where('id', $productId)
                                    ->lockForUpdate()
                                    ->first();

                                if (! $product) {
                                    throw new \Exception('Produk tidak ditemukan.');
                                }

                                if ($product->stock < $quantity) {
                                    throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi (Tersisa: {$product->stock} item).");
                                }

                                // Kurangi stok
                                $product->decrement('stock', $quantity);
                            }
                        }
                    });

                    // Update flag jika sukses
                    $order->updateQuietly(['stock_deducted' => true]);
                } catch (\Exception $e) {
                    // Batalkan perubahan status order jika stok tidak mencukupi (kembalikan ke status sebelumnya)
                    $order->status = $order->getOriginal('status');
                    $order->updateQuietly();

                    // Lempar exception ke controller/verifikator agar transaksi dibatalkan
                    throw $e;
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
