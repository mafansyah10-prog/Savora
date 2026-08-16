<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 7;

    protected static ?string $heading = '⚠️ Peringatan Stok Menipis';

    protected ?string $pollingInterval = '5s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->where('stock', '<=', 5)
                    ->orderBy('stock', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->category?->name ?? 'Tanpa Kategori'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Sisa')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning')
                    ->alignCenter(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Semua stok aman! 🎉')
            ->emptyStateDescription('Tidak ada produk dengan stok kritis saat ini.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
