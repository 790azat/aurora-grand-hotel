<?php

namespace App\Filament\Resources\Amenities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AmenitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->modifyQueryUsing(fn ($query) => $query->withCount('roomTypes'))
            ->columns([
                TextColumn::make('name_en')->label(__('admin.fields.name').' (EN)')->searchable()->weight('semibold'),
                TextColumn::make('name_ru')->label(__('admin.fields.name').' (RU)')->searchable(),
                TextColumn::make('name_hy')->label(__('admin.fields.name').' (HY)')->searchable()->toggleable(),
                TextColumn::make('icon')->label(__('admin.fields.icon'))->badge()->color('gray')->fontFamily('mono'),
                TextColumn::make('room_types_count')->label(__('admin.models.room_type.plural'))->badge(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
