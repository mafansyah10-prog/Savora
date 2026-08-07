<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Voucher')
                    ->required()
                    ->placeholder('Contoh: SAVORA10')
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state))
                    ->default(function () {
                        do {
                            $code = 'ADM-' . strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
                        } while (\App\Models\Voucher::where('code', $code)->exists());
                        return $code;
                    })
                    ->suffixAction(
                        \Filament\Actions\Action::make('generateCode')
                            ->icon('heroicon-m-sparkles')
                            ->tooltip('Acak Kode Voucher')
                            ->action(function ($set) {
                                do {
                                    $code = 'ADM-' . strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
                                } while (\App\Models\Voucher::where('code', $code)->exists());
                                $set('code', $code);
                            })
                    )
                    ->live(onBlur: true),

                Select::make('type')
                    ->label('Tipe Potongan')
                    ->options([
                        'fixed' => 'Nominal Tetap (Rupiah)',
                        'percent' => 'Persentase (%)',
                    ])
                    ->required()
                    ->default('fixed')
                    ->live(),

                TextInput::make('value')
                    ->label('Nilai Potongan')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->placeholder(fn ($get) => $get('type') === 'percent' ? 'Contoh: 10' : 'Contoh: 15000')
                    ->helperText(fn ($get) => $get('type') === 'percent' ? 'Masukkan angka persentase (1 - 100)' : 'Masukkan nominal rupiah potongan'),

                TextInput::make('min_order_amount')
                    ->label('Minimal Pembelian (Rupiah)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->placeholder('Contoh: 50000')
                    ->helperText('Batas minimum total belanja agar voucher ini dapat digunakan'),

                Select::make('rank')
                    ->label('Pangkat Loyalitas')
                    ->options([
                        'reguler'  => '⚪ Reguler',
                        'perunggu' => '🥉 Perunggu',
                        'perak'    => '🥈 Perak',
                        'emas'     => '🥇 Emas',
                        'platinum' => '💎 Platinum',
                        'diamond'  => '👑 VIP Diamond',
                    ])
                    ->nullable()
                    ->placeholder('Semua Pangkat')
                    ->helperText('Kosongkan jika voucher dapat digunakan oleh semua pangkat pelanggan'),

                \Filament\Forms\Components\Placeholder::make('receiver_type')
                    ->label('Penerima Voucher')
                    ->content(fn ($record) => $record && $record->user_id 
                        ? 'Pengguna Baru: ' . ($record->user ? $record->user->name . ' (' . $record->user->email . ')' : 'User ID #' . $record->user_id) 
                        : 'Umum (Semua Pelanggan)'),

                DateTimePicker::make('expires_at')
                    ->label('Tanggal & Waktu Kadaluarsa')
                    ->native(false)
                    ->placeholder('Pilih tanggal & waktu...')
                    ->displayFormat('d F Y, H:i')
                    ->seconds(false),

                TextInput::make('usage_limit')
                    ->label('Batas Maksimal Pemakaian')
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('Contoh: 100')
                    ->helperText('Jumlah maksimal voucher ini dapat digunakan secara keseluruhan (kosongkan jika tidak ada batas)'),

                TextInput::make('limit_per_user')
                    ->label('Batas Pemakaian Per Pengguna')
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('Contoh: 1')
                    ->helperText('Jumlah maksimal voucher ini dapat digunakan oleh setiap pengguna (kosongkan jika tidak ada batas)'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false),

                Toggle::make('is_hidden')
                    ->label('Sembunyikan Voucher (Voucher Khusus)')
                    ->helperText('Jika aktif, voucher ini tidak akan muncul di daftar pilihan voucher pelanggan dan hanya dapat digunakan dengan memasukkan kode secara manual.')
                    ->default(false)
                    ->inline(false),
            ]);
    }
}
