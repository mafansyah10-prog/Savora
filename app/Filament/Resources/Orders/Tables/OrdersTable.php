<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\FiltersLayout;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('WhatsApp'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('shipping_zone_name')
                    ->label('Wilayah')
                    ->placeholder('—'),
                TextColumn::make('shipping_cost')
                    ->label('Ongkir')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('voucher_code')
                    ->label('Voucher')
                    ->placeholder('—')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'   => 'Pending',
                        'paid'      => 'Lunas',
                        'shipped'   => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default     => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'paid'      => 'success',
                        'shipped'   => 'info',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal Pesan')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('tanggal_pesanan')
                    ->label('Saring Waktu Transaksi')
                    ->form([
                        DatePicker::make('created_date')
                            ->label('Pilih Tanggal')
                            ->placeholder('Pilih tanggal...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_date'] ?? null) {
                            $indicators[] = 'Tanggal: ' . \Carbon\Carbon::parse($data['created_date'])->translatedFormat('d F Y');
                        }
                        return $indicators;
                    })
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('print')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (\App\Models\Order $record): string => route('orders.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
