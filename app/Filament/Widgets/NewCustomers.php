<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NewCustomers extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 9;
    protected static ?string $heading = 'Pelanggan Baru';
    protected ?string $pollingInterval = '3s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('')
                    ->description(fn (User $record): string => $record->email)
                    ->weight('bold')
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('')
                    ->since()
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
