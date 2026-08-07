<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('products')
                    ->image(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('discount_price')
                    ->label('Harga Diskon')
                    ->numeric()
                    ->prefix('Rp')
                    ->helperText('Isi untuk memberikan harga promo/diskon. Jika diisi dan lebih kecil dari harga asli, harga asli akan dicoret.'),
                TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->suffix('pcs')
                    ->helperText('Jumlah stok produk yang tersedia. Isi 0 jika stok habis.'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required(),
                Toggle::make('is_new_manual')
                    ->label('Set sebagai Produk Baru')
                    ->helperText('Jika aktif, produk akan muncul di filter "Produk Baru" secara manual.'),
                Toggle::make('is_popular_manual')
                    ->label('Set sebagai Terlaris')
                    ->helperText('Jika aktif, produk akan muncul di filter "Terlaris" secara manual.'),
                Section::make('Kustomisasi Menu')
                    ->description('Kelola pilihan tambahan, level pedas, topping, dan varian saos untuk produk ini.')
                    ->collapsible()
                    ->schema([
                        Toggle::make('enable_spiciness')
                            ->label('Aktifkan Level Kepedasan')
                            ->live(),
                        Repeater::make('spiciness_levels')
                            ->label('Tingkat Kepedasan')
                            ->visible(fn (callable $get) => $get('enable_spiciness'))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Level')
                                    ->required()
                                    ->placeholder('Contoh: Level 0 (Tidak Pedas)'),
                                TextInput::make('price')
                                    ->label('Harga Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->nullable()
                                    ->default(0)
                                    ->helperText('Kosongkan atau isi 0 jika gratis (Free).'),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                        Toggle::make('enable_toppings')
                            ->label('Aktifkan Topping')
                            ->live(),
                        Repeater::make('toppings')
                            ->label('Daftar Topping')
                            ->visible(fn (callable $get) => $get('enable_toppings'))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Topping')
                                    ->required()
                                    ->columnSpan(12),
                                TextInput::make('price')
                                    ->label('Harga Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->nullable()
                                    ->default(0)
                                    ->helperText('Kosongkan atau isi 0 jika gratis.')
                                    ->columnSpan(8),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->columnSpan(4),
                            ])
                            ->columns(12)
                            ->defaultItems(0),
                        Toggle::make('enable_sauces')
                            ->label('Aktifkan Varian Saos')
                            ->live(),
                        Repeater::make('sauces')
                            ->label('Daftar Varian Saos')
                            ->visible(fn (callable $get) => $get('enable_sauces'))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Saos')
                                    ->required()
                                    ->columnSpan(12),
                                TextInput::make('price')
                                    ->label('Harga Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->nullable()
                                    ->default(0)
                                    ->helperText('Kosongkan atau isi 0 jika gratis.')
                                    ->columnSpan(8),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->columnSpan(4),
                            ])
                            ->columns(12)
                            ->defaultItems(0),
                        Toggle::make('enable_additionals')
                            ->label('Aktifkan Additional (Add-ons)')
                            ->live(),
                        Repeater::make('additionals')
                            ->label('Daftar Additional')
                            ->visible(fn (callable $get) => $get('enable_additionals'))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Item')
                                    ->required()
                                    ->columnSpan(12),
                                TextInput::make('price')
                                    ->label('Harga Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->nullable()
                                    ->default(0)
                                    ->helperText('Kosongkan atau isi 0 jika gratis.')
                                    ->columnSpan(8),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->columnSpan(4),
                            ])
                            ->columns(12)
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
