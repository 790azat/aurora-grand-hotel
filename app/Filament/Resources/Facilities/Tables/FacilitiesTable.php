<?php

namespace App\Filament\Resources\Facilities\Tables;

use App\Models\Facility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FacilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')->label('')->imageWidth(72)->imageHeight(48)
                    ->extraImgAttributes(['style' => 'border-radius:.5rem;object-fit:cover', 'loading' => 'lazy']),
                TextColumn::make('name')->label(__('admin.fields.name'))->state(fn (Facility $f) => $f->name)->weight('semibold')
                    ->description(fn (Facility $f) => Str::limit($f->description, 80))->wrap()
                    ->searchable(['name_en', 'name_ru']),
                TextColumn::make('hours')->label(__('admin.fields.hours'))->icon('heroicon-o-clock')->placeholder('—'),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
