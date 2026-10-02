<?php

namespace App\Filament\Resources\GalleryImages\Tables;

use App\Filament\Resources\GalleryImages\Schemas\GalleryImageForm;
use App\Filament\Support\Ui;
use App\Models\GalleryImage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->contentGrid(['default' => 2, 'md' => 3, 'xl' => 4])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->label('')->height(150)->width('100%')
                        ->extraImgAttributes(['style' => 'border-radius:.6rem;object-fit:cover;width:100%;background:linear-gradient(135deg,#1e2a44,#b8914a)', 'loading' => 'lazy']),
                    TextColumn::make('caption')->state(fn (GalleryImage $g) => $g->caption)->weight('semibold')->searchable(['caption_en', 'caption_ru', 'caption_hy']),
                    TextColumn::make('category')->formatStateUsing(fn ($state) => __('admin.gallery_category.'.$state))->badge()->color('gray'),
                ])->space(2),
            ])
            ->filters([
                SelectFilter::make('category')->label(__('admin.fields.category'))->options(Ui::options('gallery_category', GalleryImageForm::CATEGORIES)),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->paginationPageOptions([12, 24, 48]);
    }
}
