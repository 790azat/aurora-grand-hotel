<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Filament\Support\Ui;
use App\Models\RoomType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema, bool $withType = true): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(['default' => 1, 'sm' => 2])
                ->schema(array_values(array_filter([
                    TextInput::make('number')->label(__('admin.fields.number'))->required()->unique(ignoreRecord: true)->maxLength(10),
                    TextInput::make('floor')->label(__('admin.fields.floor'))->numeric()->minValue(0)->default(1)->required(),
                    $withType ? Select::make('room_type_id')->label(__('admin.fields.room_type'))
                        ->options(fn () => RoomType::orderBy('sort')->get()->pluck('name', 'id'))
                        ->required()->native(false)->columnSpanFull() : null,
                    ToggleButtons::make('status')->label(__('admin.fields.room_status'))
                        ->options(Ui::options('room_status', array_keys(Ui::ROOM_STATUS_COLORS)))
                        ->colors(Ui::ROOM_STATUS_COLORS)
                        ->icons(['available' => 'heroicon-m-check-circle', 'maintenance' => 'heroicon-m-wrench-screwdriver', 'out_of_order' => 'heroicon-m-no-symbol'])
                        ->default('available')->inline()->required()->columnSpanFull()
                        ->disabled(fn () => ! auth()->user()?->isManager()),
                    ToggleButtons::make('housekeeping')->label(__('admin.fields.housekeeping'))
                        ->options(Ui::options('housekeeping', array_keys(Ui::HOUSEKEEPING_COLORS)))
                        ->colors(Ui::HOUSEKEEPING_COLORS)
                        ->icons(['clean' => 'heroicon-m-sparkles', 'dirty' => 'heroicon-m-trash', 'inspected' => 'heroicon-m-check-badge'])
                        ->default('clean')->inline()->required()->columnSpanFull(),
                    Textarea::make('notes')->label(__('admin.fields.notes'))->rows(3)->columnSpanFull(),
                ]))),
        ]);
    }
}
