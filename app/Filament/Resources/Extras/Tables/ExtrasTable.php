<?php

namespace App\Filament\Resources\Extras\Tables;

use App\Filament\Support\Ui;
use App\Models\Extra;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ExtrasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->state(fn (Extra $e) => $e->name)
                    ->description(fn (Extra $e) => $e->description)->weight('semibold')->searchable(['name_en', 'name_ru', 'name_hy']),
                TextColumn::make('price')->label(__('admin.fields.price'))->money(Ui::currency())->sortable(),
                TextColumn::make('pricing')->label(__('admin.fields.pricing'))->formatStateUsing(fn ($state) => __('admin.pricing.'.$state))->badge()->color('gray'),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
