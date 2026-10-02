<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')->label('')->imageWidth(72)->imageHeight(48)
                    ->extraImgAttributes(['style' => 'border-radius:.5rem;object-fit:cover', 'loading' => 'lazy']),
                TextColumn::make('title')->label(__('admin.fields.title'))->state(fn (Offer $o) => $o->title)->weight('semibold')->wrap()
                    ->searchable(['title_en', 'title_ru', 'title_hy']),
                TextColumn::make('badge')->label(__('admin.fields.badge'))->state(fn (Offer $o) => $o->badge)->badge()->placeholder('—'),
                TextColumn::make('promo_code')->label(__('admin.fields.promo_code'))->fontFamily('mono')->placeholder('—'),
                TextColumn::make('valid_until')->label(__('admin.fields.valid_until'))->date('d M Y')->placeholder('—')->sortable(),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
