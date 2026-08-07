<?php

namespace App\Filament\Resources\ShippingZones\Pages;

use App\Filament\Resources\ShippingZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingZones extends ListRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Wilayah'),
        ];
    }
}
