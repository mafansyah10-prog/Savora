<?php

namespace App\Filament\Resources\SupportSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer')
                    ->label('Pelanggan')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        })->orWhere('session_token', 'like', "%{$search}%");
                    })
                    ->getStateUsing(function ($record) {
                        $user = $record->user;
                        if ($user) {
                            return "{$user->name} ({$user->email})";
                        }
                        return "Tamu (" . substr($record->session_token, 0, 10) . "...)";
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'active' => 'warning',
                        'resolved' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu (Pending)',
                        'active' => 'Aktif (Active)',
                        'resolved' => 'Selesai (Resolved)',
                    }),

                TextColumn::make('updated_at')
                    ->label('Aktivitas Terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('Buka Chat'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
