<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellingProducts extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 6;
    protected static ?string $heading = 'Top Selling Product';
    protected ?string $pollingInterval = '3s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->orderBy('sales_count', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Product&background=f97316&color=fff'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->description(fn (Product $record): string => $record->category?->name ?? 'Tanpa Kategori')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Pesanan')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->default('∞')
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated(false);
    }
}
