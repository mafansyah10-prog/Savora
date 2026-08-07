<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('icon')
                    ->label('Pilih Icon')
                    ->options([
                        'utensils' => 'Alat Makan (Utensils)',
                        'beef' => 'Daging (Beef)',
                        'leaf' => 'Sayuran/Organik (Leaf)',
                        'carrot' => 'Wortel/Sayur (Carrot)',
                        'glass-water' => 'Minuman (Glass)',
                        'coffee' => 'Kopi (Coffee)',
                        'milk' => 'Susu/Dairy (Milk)',
                        'croissant' => 'Roti/Bakery (Croissant)',
                        'sandwich' => 'Roti Isi (Sandwich)',
                        'pizza' => 'Pizza (Pizza)',
                        'cake' => 'Kue/Dessert (Cake)',
                        'ice-cream' => 'Eskrim (Ice Cream)',
                        'gift' => 'Hadiah (Gift)',
                        'package' => 'Paket (Package)',
                        'shopping-bag' => 'Belanja (Shopping Bag)',
                        'cooking-pot' => 'Masakan (Cooking Pot)',
                        'fish' => 'Ikan/Seafood (Fish)',
                        'apple' => 'Buah-buahan (Apple)',
                        'cherry' => 'Ceri/Buah (Cherry)',
                        'wine' => 'Minuman Berkelas (Wine)',
                        'beer' => 'Minuman Dingin (Beer)',
                    ])
                    ->searchable()
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
