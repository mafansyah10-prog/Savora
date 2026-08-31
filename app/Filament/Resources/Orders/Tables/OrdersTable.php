<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('WhatsApp'),
                TextColumn::make('shipping_method')
                    ->label('Layanan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pickup' => 'Pickup di Outlet',
                        'delivery' => 'Delivery',
                        default => 'Delivery',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pickup' => 'warning',
                        'delivery' => 'success',
                        default => 'success',
                    }),
                TextColumn::make('pickup_time')
                    ->label('Jam Ambil')
                    ->placeholder('—'),
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
                    ->formatStateUsing(fn (string $state, Order $record): string => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Lunas (Belum Ready)',
                        'shipped' => $record->shipping_method === 'pickup' ? 'Sudah Ready (Siap Diambil)' : 'Sudah Ready (Dikirim)',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'shipped' => 'info',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
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
                            $indicators[] = 'Tanggal: '.Carbon::parse($data['created_date'])->translatedFormat('d F Y');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->recordActions([
                EditAction::make(),
                Action::make('print')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Order $record): string => route('orders.print', $record))
                    ->openUrlInNewTab(),
                Action::make('mark_ready')
                    ->label(fn (Order $record): string => $record->shipping_method === 'pickup' ? 'Siap Diambil' : 'Kirim Pesanan')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === 'paid')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'shipped']);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
