<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\NavGroup;
use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Hotel;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'number';

    protected static bool $isGloballySearchable = false;

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function getNavigationBadge(): ?string
    {
        $dirty = Room::where('housekeeping', 'dirty')->count();

        return $dirty ? (string) $dirty : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('admin.room.dirty_badge');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.room.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.room.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}
