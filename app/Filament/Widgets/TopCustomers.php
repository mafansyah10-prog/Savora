<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopCustomers extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 8;
    protected static ?string $heading = 'Pelanggan Teraktif';
    protected ?string $pollingInterval = '5s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('orders', function ($query) {
                        $query->whereNotIn('status', ['cancelled']);
                    })
                    ->withCount(['orders' => function ($query) {
                        $query->whereNotIn('status', ['cancelled']);
                    }])
                    ->withSum(['orders' => function ($query) {
                        $query->whereNotIn('status', ['cancelled']);
                    }], 'total_amount')
                    ->orderBy('orders_count', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Pelanggan')
                    ->description(fn (User $record): string => $record->email)
                    ->weight('bold')
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Transaksi')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Total Belanja')
                    ->money('IDR')
                    ->placeholder('Rp 0'),
            ])
            ->paginated(false);
    }
}
