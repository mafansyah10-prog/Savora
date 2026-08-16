<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->description(fn ($record) => $record->category?->name ?? '—'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->html()
                    ->formatStateUsing(fn ($state, $record) => $record->hasDiscount()
                        ? '<span style="text-decoration: line-through; color: #888; margin-right: 5px;">Rp '.number_format($record->price, 0, ',', '.').'</span> <span style="font-weight: bold; color: #4CAF50;">Rp '.number_format($record->discount_price, 0, ',', '.').'</span>'
                        : 'Rp '.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_new_manual')
                    ->label('Baru')
                    ->boolean(),
                IconColumn::make('is_popular_manual')
                    ->label('Laris')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
