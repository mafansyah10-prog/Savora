<?php

namespace App\Filament\Resources\SupportSessions\Pages;

use App\Filament\Resources\SupportSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListSupportSessions extends ListRecords
{
    protected static string $resource = SupportSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No custom create action needed since sessions are initiated by customer
        ];
    }
}
