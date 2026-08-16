<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', now()->toDateString()))
                ->badge(fn () => static::$resource::getModel()::whereDate('created_at', now()->toDateString())->count())
                ->badgeColor('primary'),
            'pending' => Tab::make('Belum Terkonfirmasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => static::$resource::getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'confirmed' => Tab::make('Sudah Terkonfirmasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'pending'))
                ->badge(fn () => static::$resource::getModel()::where('status', '!=', 'pending')->count())
                ->badgeColor('success'),
            'all' => Tab::make('Semua Pesanan')
                ->badge(fn () => static::$resource::getModel()::count()),
        ];
    }
}
