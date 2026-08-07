<?php

namespace App\Filament\Resources\ShippingZones\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Wilayah')
                    ->required()
                    ->placeholder('Contoh: Jakarta Selatan')
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->helperText('Nama kota atau area pengiriman'),

                TextInput::make('cost')
                    ->label('Biaya Ongkir (Rp)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->placeholder('Contoh: 15000')
                    ->helperText('Tarif ongkos kirim flat untuk wilayah ini'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Nonaktifkan untuk menyembunyikan wilayah ini dari pilihan customer')
                    ->default(true)
                    ->inline(false),
            ]);
    }
}
