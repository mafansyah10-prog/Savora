<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Setelah admin menyimpan pesanan, periksa apakah status berubah ke 'paid'.
     * Jika ya dan stok belum pernah dikurangi, kurangi stok setiap produk dalam pesanan.
     */
    protected function afterSave(): void
    {
        $order = $this->record;

        // Hanya kurangi stok jika:
        // 1. Status sekarang adalah 'paid', 'shipped', atau 'completed' (pembayaran disetujui/diproses oleh admin)
        // 2. Stok belum pernah dikurangi sebelumnya
        if (in_array($order->status, ['paid', 'shipped', 'completed']) && ! $order->stock_deducted) {
            $items = $order->items ?? [];

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $quantity = $item['quantity'] ?? 0;

                if ($productId && $quantity > 0) {
                    Product::where('id', $productId)
                        ->where('stock', '>', 0)
                        ->decrement('stock', $quantity);
                }
            }

            // Tandai bahwa stok sudah dikurangi agar tidak terjadi duplikasi
            $order->updateQuietly(['stock_deducted' => true]);
        }
    }
}
