<?php

namespace App\Filament\Resources\Seasons\Schemas;

use App\Models\RoomType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->columnSpanFull()->schema([
                TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(255)->columnSpanFull(),
                DatePicker::make('starts_on')->label(__('admin.fields.starts_on'))->native(false)->required(),
                DatePicker::make('ends_on')->label(__('admin.fields.ends_on'))->native(false)->required()->afterOrEqual('starts_on'),
                TextInput::make('price_modifier')->label(__('admin.fields.price_modifier'))->numeric()->suffix('%')->required()->default(0)
                    ->minValue(-90)->maxValue(500)->helperText(__('admin.season.modifier_hint')),
                TextInput::make('min_nights')->label(__('admin.fields.min_nights'))->numeric()->minValue(1),
                Select::make('room_type_id')->label(__('admin.fields.room_type'))
                    ->options(fn () => RoomType::orderBy('sort')->get()->pluck('name', 'id'))
                    ->placeholder(__('admin.season.all_types'))->native(false),
                Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true)->inline(false),
            ]),
        ]);
    }
}
