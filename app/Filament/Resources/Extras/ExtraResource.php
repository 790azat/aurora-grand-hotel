<?php

namespace App\Filament\Resources\Extras;

use App\Filament\Concerns\ManagerOnly;
use App\Filament\NavGroup;
use App\Filament\Resources\Extras\Pages\CreateExtra;
use App\Filament\Resources\Extras\Pages\EditExtra;
use App\Filament\Resources\Extras\Pages\ListExtras;
use App\Filament\Resources\Extras\Schemas\ExtraForm;
use App\Filament\Resources\Extras\Tables\ExtrasTable;
use App\Models\Extra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExtraResource extends Resource
{
    use ManagerOnly;

    protected static ?string $model = Extra::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Pricing;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public static function getModelLabel(): string
    {
        return __('admin.models.extra.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.extra.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ExtraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtrasTable::configure($table);
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
            'index' => ListExtras::route('/'),
            'create' => CreateExtra::route('/create'),
            'edit' => EditExtra::route('/{record}/edit'),
        ];
    }
}
