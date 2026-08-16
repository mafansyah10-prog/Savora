<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Filter Data Penjualan')
                    ->description('Pilih tanggal untuk memfilter statistik Penjualan Hari Ini, Bulan Ini, dan Tahun Ini.')
                    ->schema([
                        DatePicker::make('sales_date')
                            ->label('Tanggal Penjualan')
                            ->default(now()->toDateString())
                            ->maxDate(now()),
                    ])
                    ->collapsible()
                    ->compact()
                    ->columnSpan(2),

                Placeholder::make('greeting')
                    ->hiddenLabel()
                    ->content(view('filament.widgets.greeting-widget'))
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public function getColumns(): int|array
    {
        return 3;
    }
}
