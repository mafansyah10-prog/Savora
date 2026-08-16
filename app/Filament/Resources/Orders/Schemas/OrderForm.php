<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                Placeholder::make('created_at')
                    ->label('Tanggal & Waktu Pesanan')
                    ->content(fn ($record) => $record && $record->created_at ? $record->created_at->translatedFormat('d F Y H:i:s') : '—'),
                Textarea::make('shipping_address')
                    ->label('Alamat Pengiriman')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan Pelanggan')
                    ->placeholder('Tidak ada catatan')
                    ->columnSpanFull(),
                Placeholder::make('items_list')
                    ->label('Detail Menu Yang Dipesan')
                    ->columnSpanFull()
                    ->content(function ($record) {
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
            ]);
    }
}
