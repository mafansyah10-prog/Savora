<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->required(),
                TextInput::make('customer_phone')
                    ->label('Nomor Telepon')
                    ->required(),
                TextEntry::make('created_at')
                    ->label('Tanggal & Waktu Pesanan')
                    ->state(fn ($record) => $record && $record->created_at ? $record->created_at->translatedFormat('d F Y H:i:s') : '—'),
                Textarea::make('shipping_address')
                    ->label('Alamat Pengiriman')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan Pelanggan')
                    ->placeholder('Tidak ada catatan')
                    ->columnSpanFull(),
                TextEntry::make('items_list')
                    ->label('Detail Menu Yang Dipesan')
                    ->columnSpanFull()
                    ->state(function ($record) {
                        if (! $record || empty($record->items)) {
                            return 'Tidak ada item';
                        }

                        $html = '<div class="space-y-4">';
                        foreach ($record->items as $item) {
                            $product = Product::find($item['product_id'] ?? null);
                            $basePrice = $product ? $product->selling_price : $item['price'];

                            $html .= '<div style="background-color: rgba(17, 24, 39, 0.5); padding: 1rem; border: 1px solid rgba(31, 41, 55, 0.8); border-radius: 0.75rem; margin-bottom: 0.75rem;">';
                            $html .= '  <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 0.875rem; color: #fff;">';
                            $html .= '    <span>'.e($item['name']).' ('.e($item['quantity']).'x)</span>';
                            $html .= '    <span>Rp '.number_format($item['price'] * $item['quantity'], 0, ',', '.').'</span>';
                            $html .= '  </div>';

                            $html .= '  <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">';
                            $html .= '    Harga Satuan: Rp '.number_format($basePrice, 0, ',', '.');
                            if ($product && $item['price'] > $product->selling_price) {
                                $html .= ' (+ Opsi: Rp '.number_format($item['price'] - $product->selling_price, 0, ',', '.').')';
                            }
                            $html .= '  </div>';

                            if (! empty($item['options'])) {
                                $opts = [];
                                if (! empty($item['options']['spiciness_level'])) {
                                    $spicinessName = $item['options']['spiciness_level'];
                                    $formattedSpiciness = (stripos($spicinessName, 'level') === false && is_numeric($spicinessName)) ? 'Level '.$spicinessName : $spicinessName;
                                    $opts[] = 'Pedas: '.e($formattedSpiciness);
                                }
                                if (! empty($item['options']['sauce'])) {
                                    $opts[] = 'Saus: '.e($item['options']['sauce']);
                                }
                                if (! empty($item['options']['toppings'])) {
                                    $opts[] = 'Topping: '.e(implode(', ', $item['options']['toppings']));
                                }
                                if (! empty($item['options']['additionals'])) {
                                    $opts[] = 'Tambahan: '.e(implode(', ', $item['options']['additionals']));
                                }

                                if (count($opts) > 0) {
                                    $html .= '  <div style="font-size: 11px; color: #fbbf24; font-weight: 600; font-style: italic; margin-top: 0.5rem; background-color: rgba(0, 0, 0, 0.3); border: 1px solid rgba(120, 53, 4, 0.3); padding: 0.25rem 0.5rem; border-radius: 0.25rem; display: inline-block;">';
                                    $html .= '    '.implode(' | ', $opts);
                                    $html .= '  </div>';
                                }
                            }
                            $html .= '</div>';
                        }
                        $html .= '</div>';

                        return new HtmlString($html);
                    }),
                TextInput::make('shipping_zone_name')
                    ->label('Wilayah Pengiriman')
                    ->placeholder('—')
                    ->disabled(),
                TextInput::make('shipping_cost')
                    ->label('Ongkos Kirim')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled(),
                TextInput::make('voucher_code')
                    ->label('Kode Voucher')
                    ->placeholder('—')
                    ->disabled(),
                TextInput::make('discount_amount')
                    ->label('Potongan Voucher')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled(),
                TextInput::make('total_amount')
                    ->label('Total Pembayaran')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Select::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas',
                        'shipped' => 'Sedang Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required(),
                Group::make([
                    TextEntry::make('payment_proof')
                        ->label('Bukti Pembayaran')
                        ->state(function ($record) {
                            if (! $record || ! $record->payment_proof) {
                                return 'Belum mengunggah bukti pembayaran.';
                            }

                            $proofUrl = asset('storage/'.$record->payment_proof);

                            return new HtmlString("
                                <div style='margin-top: 0.5rem;'>
                                    <a href='{$proofUrl}' target='_blank' style='display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #fbbf24; font-weight: bold; text-decoration: underline;'>
                                        <svg width='16' height='16' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'></path></svg>
                                        Buka Bukti Pembayaran di Tab Baru
                                    </a>
                                    <div style='margin-top: 0.75rem; border: 1px solid #374151; border-radius: 0.75rem; overflow: hidden; background-color: rgba(0,0,0,0.2); padding: 0.5rem; display: inline-block;'>
                                        <img src='{$proofUrl}' alt='Bukti Pembayaran' style='max-width: 280px; max-height: 350px; border-radius: 0.5rem; display: block; object-fit: contain;' />
                                    </div>
                                </div>
                            ");
                        }),
                    TextEntry::make('delivery_proof')
                        ->label('Bukti Penerimaan / Produk Sampai')
                        ->state(function ($record) {
                            if (! $record || ! $record->delivery_proof) {
                                return 'Belum mengunggah bukti penerimaan.';
                            }

                            $proofUrl = asset('storage/'.$record->delivery_proof);

                            return new HtmlString("
                                <div style='margin-top: 0.5rem;'>
                                    <a href='{$proofUrl}' target='_blank' style='display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #fbbf24; font-weight: bold; text-decoration: underline;'>
                                        <svg width='16' height='16' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'></path></svg>
                                        Buka Bukti Penerimaan di Tab Baru
                                    </a>
                                    <div style='margin-top: 0.75rem; border: 1px solid #374151; border-radius: 0.75rem; overflow: hidden; background-color: rgba(0,0,0,0.2); padding: 0.5rem; display: inline-block;'>
                                        <img src='{$proofUrl}' alt='Bukti Penerimaan' style='max-width: 280px; max-height: 350px; border-radius: 0.5rem; display: block; object-fit: contain;' />
                                    </div>
                                </div>
                            ");
                        }),
                ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
