<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label(__('admin.fields.code'))->searchable()->copyable()->fontFamily('mono')->weight('bold')->color('primary'),
                TextColumn::make('value')->label(__('admin.fields.discount'))
                    ->formatStateUsing(fn (PromoCode $r) => $r->type === 'percent' ? (float) $r->value.'%' : money($r->value))
                    ->badge()->color('success'),
                TextColumn::make('valid_until')->label(__('admin.fields.valid_until'))->date('d M Y')->placeholder(__('admin.promo.no_expiry'))->sortable()
                    ->color(fn (PromoCode $r) => $r->valid_until?->isPast() ? 'danger' : null),
                TextColumn::make('used_count')->label(__('admin.fields.used_count'))
                    ->formatStateUsing(fn (PromoCode $r) => $r->used_count.($r->max_uses ? ' / '.$r->max_uses : ''))->sortable(),
                TextColumn::make('min_nights')->label(__('admin.fields.min_nights'))->placeholder('—')->alignCenter()->toggleable(),
                TextColumn::make('usable')->label(__('admin.promo.usable'))
                    ->state(fn (PromoCode $r) => $r->isUsable(max(1, (int) $r->min_nights)))
                    ->formatStateUsing(fn ($state) => $state ? __('admin.misc.yes') : __('admin.misc.no'))
                    ->badge()->color(fn ($state) => $state ? 'success' : 'gray'),
                ToggleColumn::make('is_active')->label(__('admin.fields.is_active')),
            ])
            ->filters([TernaryFilter::make('is_active')->label(__('admin.fields.is_active'))])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
