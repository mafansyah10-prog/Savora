<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Voucher;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;

class CustomerRankWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 Pelanggan Naik Pangkat';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '3s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('rank', '!=', 'reguler')
                    ->whereNull('rank_notified_at')
                    ->orderByDesc('rank_upgraded_at')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('rank')
                    ->label('Pangkat Baru')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'perunggu' => '🥉 Perunggu',
                        'perak'    => '🥈 Perak',
                        'emas'     => '🥇 Emas',
                        'platinum' => '💎 Platinum',
                        'diamond'  => '👑 VIP Diamond',
                        default    => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'perunggu' => 'warning',
                        'perak'    => 'gray',
                        'emas'     => 'warning',
                        'platinum' => 'info',
                        'diamond'  => 'primary',
                        default    => 'gray',
                    }),

                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('rank_upgraded_at')
                    ->label('Naik Pangkat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                 Action::make('send_voucher')
                    ->label('Kirim Voucher')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->form([
                        TextInput::make('code')
                            ->label('Kode Voucher')
                            ->default(fn ($record) => strtoupper('ADM-RNK-' . strtoupper(explode('@', $record->email)[0]) . '-' . rand(100, 999)))
                            ->required()
                            ->unique(table: 'vouchers', column: 'code', ignoreRecord: false)
                            ->suffixAction(
                                \Filament\Actions\Action::make('generateCode')
                                    ->icon('heroicon-m-sparkles')
                                    ->tooltip('Acak Kode Voucher')
                                    ->action(function ($set) {
                                        do {
                                            $code = 'ADM-RNK-' . strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
                                        } while (\App\Models\Voucher::where('code', $code)->exists());
                                        $set('code', $code);
                                    })
                            ),

                        Select::make('type')
                            ->label('Tipe Potongan')
                            ->options([
                                'fixed'   => 'Nominal Tetap (Rupiah)',
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
                            'code'             => strtoupper($data['code']),
                            'type'             => $data['type'],
                            'value'            => $data['value'],
                            'min_order_amount' => $data['min_order_amount'],
                            'expires_at'       => $data['expires_at'],
                            'is_active'        => true,
                        ]);

                        // Mark as notified so this customer is removed from the list
                        $record->update(['rank_notified_at' => now()]);

                        Notification::make()
                            ->title('Voucher Terkirim!')
                            ->body('Voucher ' . strtoupper($data['code']) . ' berhasil dibuat untuk ' . $record->name . '. Customer dapat melihatnya di halaman akun mereka.')
                            ->success()
                            ->send();
                    }),

                Action::make('mark_notified')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Sudah Diproses?')
                    ->modalDescription('Customer ini akan hilang dari daftar notifikasi tanpa mengirimkan voucher.')
                    ->action(fn ($record) => $record->update(['rank_notified_at' => now()])),
            ])
            ->emptyStateHeading('Semua sudah diproses! 🎉')
            ->emptyStateDescription('Tidak ada pelanggan baru yang naik pangkat saat ini.')
            ->emptyStateIcon('heroicon-o-trophy')
            ->paginated(false);
    }
}
