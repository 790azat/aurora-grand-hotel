<?php

namespace App\Filament\Resources\Seasons\Tables;

use App\Models\Season;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->weight('semibold')
                    ->description(fn (Season $s) => $s->starts_on->lte(today()) && $s->ends_on->gte(today()) ? __('admin.season.running') : null),
                TextColumn::make('starts_on')->label(__('admin.fields.starts_on'))->date('d M Y')->sortable(),
                TextColumn::make('ends_on')->label(__('admin.fields.ends_on'))->date('d M Y')->sortable(),
                TextColumn::make('price_modifier')->label(__('admin.fields.price_modifier'))
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').$state.'%')
                    ->badge()->color(fn ($state) => $state > 0 ? 'warning' : ($state < 0 ? 'success' : 'gray'))->sortable(),
                TextColumn::make('min_nights')->label(__('admin.fields.min_nights'))->placeholder('—')->alignCenter(),
                TextColumn::make('roomType.name')->label(__('admin.fields.room_type'))->placeholder(__('admin.season.all_types')),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->filters([TernaryFilter::make('is_active')->label(__('admin.fields.is_active'))])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
