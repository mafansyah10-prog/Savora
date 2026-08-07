<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Banner')
                    ->required(),
                FileUpload::make('image')
                    ->label('Gambar Banner')
                    ->disk('public')
                    ->directory('banners')
                    ->image()
                    ->required(),
                Select::make('product_id')
                    ->label('Tautkan ke Produk')
                    ->relationship('product', 'name')
                    ->nullable()
                    ->placeholder('Pilih produk untuk ditautkan...')
                    ->helperText('Jika diisi, banner akan otomatis mengarah ke halaman detail produk ini.'),
                TextInput::make('link')
                    ->label('Link URL Kustom')
                    ->placeholder('Contoh: /keranjang atau https://google.com')
                    ->helperText('Kosongkan jika Anda sudah memilih Tautkan ke Produk di atas.'),
                TextInput::make('button_text')
                    ->label('Teks Tombol Banner')
                    ->placeholder('Contoh: Pesan Sekarang')
                    ->helperText('Teks tombol (default: "Jelajahi Menu").'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
