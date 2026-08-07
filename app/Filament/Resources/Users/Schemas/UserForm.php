<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord),

                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn ($livewire) => $livewire instanceof CreateRecord),

                Toggle::make('is_blocked')
                    ->label('Akun Diblokir')
                    ->helperText('Jika diaktifkan, customer ini tidak akan bisa masuk/login ke website.')
                    ->disabled(fn ($record) => $record && $record->id === filament()->auth()->id())
                    ->default(false)
                    ->live(),

                \Filament\Forms\Components\DateTimePicker::make('blocked_until')
                    ->label('Blokir Sampai Waktu')
                    ->helperText('Biarkan kosong jika ingin memblokir secara permanen.')
                    ->placeholder('Pilih tanggal dan waktu...')
                    ->visible(fn ($get) => $get('is_blocked') === true)
                    ->disabled(fn ($record) => $record && $record->id === filament()->auth()->id())
                    ->native(false),

                Select::make('role')
                    ->label('Peran')
                    ->options(function ($record) {
                        $options = [
                            'admin' => 'Admin',
                            'manager' => 'Manager',
                            'customer' => 'Customer',
                        ];
                        if ($record && $record->role && !array_key_exists($record->role, $options)) {
                            $options[$record->role] = ucfirst($record->role);
                        }
                        $options['custom'] = '+ Peran Baru (Kustom)...';
                        return $options;
                    })
                    ->default('customer')
                    ->required()
                    ->live()
                    ->dehydrateStateUsing(fn ($state, $get) => $state === 'custom' ? strtolower($get('custom_role')) : $state)
                    ->disabled(fn ($record) => $record && $record->id === filament()->auth()->id()),

                TextInput::make('custom_role')
                    ->label('Nama Peran Baru')
                    ->placeholder('Contoh: kasir, staff, dll')
                    ->required()
                    ->visible(fn ($get) => $get('role') === 'custom')
                    ->dehydrated(false)
                    ->live()
                    ->disabled(fn ($record) => $record && $record->id === filament()->auth()->id()),

                Toggle::make('can_access_admin_panel')
                    ->label('Akses Panel Admin')
                    ->helperText('Jika diaktifkan, user ini diizinkan untuk login ke admin panel.')
                    ->default(false)
                    ->disabled(fn ($record) => $record && $record->id === filament()->auth()->id()),
            ]),
        ]);
    }
}
