<?php

namespace App\Filament\Resources\RoomTypes\Tables;

use App\Filament\Support\Ui;
use App\Models\RoomType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RoomTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->modifyQueryUsing(fn ($query) => $query->withCount('rooms'))
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->state(fn (RoomType $r) => $r->cover)
                    ->imageWidth(72)->imageHeight(48)
                    ->extraImgAttributes(['style' => 'border-radius:.5rem;object-fit:cover', 'loading' => 'lazy']),
                TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->state(fn (RoomType $r) => $r->name)
                    ->description(fn (RoomType $r) => $r->short)
                    ->weight('semibold')
                    ->searchable(['name_en', 'name_ru', 'name_hy']),
                TextColumn::make('base_price')->label(__('admin.fields.base_price'))->money(Ui::currency())->sortable(),
                TextColumn::make('weekend_price')->label(__('admin.fields.weekend_price'))->money(Ui::currency())->toggleable(),
                TextColumn::make('max_adults')->label(__('admin.fields.capacity'))
                    ->formatStateUsing(fn (RoomType $r) => $r->max_adults.' + '.$r->max_children)
                    ->icon('heroicon-o-users'),
                TextColumn::make('size_m2')->label(__('admin.fields.size_m2'))->suffix(' m²')->sortable()->toggleable(),
                TextColumn::make('rooms_count')->label(__('admin.models.room.plural'))->badge()->color('gray'),
                ToggleColumn::make('is_featured')->label(__('admin.fields.is_featured')),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
