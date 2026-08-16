<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Voucher;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'customer' => 'Customer',
                        default => ucfirst($state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'customer' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('can_access_admin_panel')
                    ->label('Akses Panel Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
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
                        default => '⚪ Reguler',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'perunggu' => 'warning',
                        'perak' => 'gray',
                        'emas' => 'warning',
                        'platinum' => 'info',
                        'diamond' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable(),

                IconColumn::make('is_blocked')
                    ->label('Status Akun')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_blocked')
                    ->label('Status Blokir')
                    ->placeholder('Semua Status')
                    ->trueLabel('Hanya Akun Diblokir')
                    ->falseLabel('Hanya Akun Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('send_voucher')
                    ->label('Kirim Voucher')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->form([
                        TextInput::make('code')
                            ->label('Kode Voucher')
                            ->default(fn ($record) => strtoupper('ADM-VCR-'.strtoupper(explode('@', $record->email)[0]).'-'.rand(100, 999)))
                            ->required()
                            ->unique(table: 'vouchers', column: 'code', ignoreRecord: false)
                            ->suffixAction(
                                Action::make('generateCode')
                                    ->icon('heroicon-m-sparkles')
                                    ->tooltip('Acak Kode Voucher')
                                    ->action(function ($set) {
                                        do {
                                            $code = 'ADM-VCR-'.strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
                                        } while (Voucher::where('code', $code)->exists());
                                        $set('code', $code);
                                    })
                            ),

                        Select::make('type')
                            ->label('Tipe Potongan')
                            ->options([
                                'fixed' => 'Nominal Tetap (Rupiah)',
                                'percent' => 'Persentase (%)',
                            ])
                            ->required()
                            ->default('fixed'),

                        TextInput::make('value')
                            ->label('Nilai Potongan')
                            ->required()
                            ->numeric()
                            ->minValue(1),

                        TextInput::make('min_order_amount')
                            ->label('Minimal Pembelian (Rupiah)')
                            ->required()
                            ->numeric()
                            ->default(0),

                        DatePicker::make('expires_at')
                            ->label('Tanggal Kadaluarsa')
                            ->default(now()->addDays(7))
                            ->native(false),
                    ])
                    ->action(function (array $data, $record) {
                        Voucher::create([
                            'code' => strtoupper($data['code']),
                            'type' => $data['type'],
                            'value' => $data['value'],
                            'min_order_amount' => $data['min_order_amount'],
                            'expires_at' => $data['expires_at'],
                            'is_active' => true,
                        ]);

                        Notification::make()
                            ->title('Voucher Terkirim!')
                            ->body('Voucher '.strtoupper($data['code']).' berhasil dibuat untuk '.$record->name.'.')
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_block')
                    ->label(fn ($record) => $record->is_blocked ? 'Buka Blokir' : 'Blokir')
                    ->icon(fn ($record) => $record->is_blocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn ($record) => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->visible(fn () => filament()->auth()->user()?->role === 'admin')
                    ->hidden(fn ($record) => $record->id === filament()->auth()->id())
                    ->action(function ($record) {
                        $record->update(['is_blocked' => ! $record->is_blocked]);

                        Notification::make()
                            ->title($record->is_blocked ? 'Akun Diblokir!' : 'Blokir Dibuka!')
                            ->body('Status akun '.$record->name.' telah berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->hidden(fn ($record) => $record->id === filament()->auth()->id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->id !== filament()->auth()->id()) {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ]);
    }
}
