<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportSessions\Pages\EditSupportSession;
use App\Filament\Resources\SupportSessions\Pages\ListSupportSessions;
use App\Filament\Resources\SupportSessions\Schemas\SupportSessionForm;
use App\Filament\Resources\SupportSessions\Tables\SupportSessionsTable;
use App\Models\SupportSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SupportSessionResource extends Resource
{
    protected static ?string $model = SupportSession::class;

    protected static ?string $navigationLabel = 'Live Support Chat';

    protected static ?string $modelLabel = 'Support Chat';

    protected static ?string $pluralModelLabel = 'Live Support Chat';

    protected static ?string $slug = 'support-chats';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return SupportSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupportSessions::route('/'),
            'edit' => EditSupportSession::route('/{record}/edit'),
        ];
    }
}
