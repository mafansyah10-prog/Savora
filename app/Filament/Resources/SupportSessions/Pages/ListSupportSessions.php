<?php

namespace App\Filament\Resources\SupportSessions\Pages;

use App\Filament\Resources\SupportSessionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSupportSessions extends ListRecords
{
    protected static string $resource = SupportSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Chat')
                ->icon('heroicon-m-chat-bubble-left-right'),
                
            'pending' => Tab::make('Belum Dibalas / Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => static::$resource::getModel()::where('status', 'pending')->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-exclamation-circle'),

            'active' => Tab::make('Aktif / Dibalas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->icon('heroicon-m-clock'),

            'resolved' => Tab::make('Selesai / Ditutup')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'resolved'))
                ->icon('heroicon-m-check-circle'),
        ];
    }
}
