<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Voucher')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Persentase (%)' : 'Nominal Tetap (Rp)')
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Nilai Potongan')
                    ->formatStateUsing(fn ($record) => $record->type === 'percent'
                        ? number_format($record->value, 0).'%'
                        : 'Rp '.number_format($record->value, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('min_order_amount')
                    ->label('Min. Belanja')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('rank')
                    ->label('Pangkat')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'perunggu' => '🥉 Perunggu',
                        'perak' => '🥈 Perak',
                        'emas' => '🥇 Emas',
                        'platinum' => '💎 Platinum',
                        'diamond' => '👑 VIP Diamond',
                        'reguler' => '⚪ Reguler',
                        default => 'Semua Pangkat',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'perunggu' => 'warning',
                        'perak' => 'gray',
                        'emas' => 'warning',
                        'platinum' => 'info',
                        'diamond' => 'primary',
                        'reguler' => 'gray',
                        default => 'success',
                    })
                    ->sortable()
                    ->placeholder('Semua Pangkat'),

                TextColumn::make('user.name')
                    ->label('Penerima Khusus')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->user_id
                        ? ($record->user ? $record->user->name : 'User #'.$record->user_id)
                        : 'Umum (Semua Pelanggan)')
                    ->color(fn ($record) => $record->user_id ? 'warning' : 'success')
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Digunakan')
                    ->formatStateUsing(fn ($record, $state) => $record->usage_limit
                        ? "{$state} / {$record->usage_limit}"
                        : "{$state}")
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->dateTime('d F Y, H:i')
                    ->sortable()
                    ->placeholder('Tidak Ada'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_hidden')
                    ->label('Tersembunyi')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_personal')
                    ->label('Jenis Penerima')
                    ->placeholder('Semua Jenis')
                    ->trueLabel('Hanya Khusus Pengguna / Pengguna Baru')
                    ->falseLabel('Hanya Umum (Semua Pengguna)')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
