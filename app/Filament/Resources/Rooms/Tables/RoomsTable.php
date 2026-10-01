<?php

namespace App\Filament\Resources\Rooms\Tables;

use App\Filament\Support\Ui;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class RoomsTable
{
    public static function configure(Table $table, bool $withType = true): Table
    {
        $isManager = fn () => (bool) auth()->user()?->isManager();

        return $table
            ->defaultSort('number')
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn ($query) => $query->with('roomType'))
            ->columns(array_values(array_filter([
                TextColumn::make('number')->label(__('admin.fields.number'))->searchable()->sortable()->weight('bold')->size('lg'),
                TextColumn::make('floor')->label(__('admin.fields.floor'))->sortable()->alignCenter(),
                $withType ? TextColumn::make('roomType.name')->label(__('admin.fields.room_type'))->badge()->color('gray') : null,
                TextColumn::make('occupant')
                    ->label(__('admin.fields.tonight'))
                    ->state(function (Room $r) {
                        $b = Booking::where('room_id', $r->id)->whereIn('status', ['confirmed', 'checked_in'])
                            ->whereDate('check_in', '<=', today())->whereDate('check_out', '>', today())->first();

                        return $b ? $b->guest_name : null;
                    })
                    ->placeholder(__('admin.room.vacant'))
                    ->icon(fn ($state) => $state ? 'heroicon-m-user' : null)
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
                SelectColumn::make('status')
                    ->label(__('admin.fields.room_status'))
                    ->options(Ui::options('room_status', array_keys(Ui::ROOM_STATUS_COLORS)))
                    ->selectablePlaceholder(false)
                    ->disabled(fn () => ! $isManager())
                    ->sortable(),
                SelectColumn::make('housekeeping')
                    ->label(__('admin.fields.housekeeping'))
                    ->options(Ui::options('housekeeping', array_keys(Ui::HOUSEKEEPING_COLORS)))
                    ->selectablePlaceholder(false)
                    ->sortable(),
                TextColumn::make('notes')->label(__('admin.fields.notes'))->limit(40)->placeholder('—')->toggleable(),
            ])))
            ->filters(array_values(array_filter([
                $withType ? SelectFilter::make('room_type_id')->label(__('admin.fields.room_type'))
                    ->options(fn () => RoomType::orderBy('sort')->get()->pluck('name', 'id')) : null,
                SelectFilter::make('status')->label(__('admin.fields.room_status'))->options(Ui::options('room_status', array_keys(Ui::ROOM_STATUS_COLORS))),
                SelectFilter::make('housekeeping')->label(__('admin.fields.housekeeping'))->options(Ui::options('housekeeping', array_keys(Ui::HOUSEKEEPING_COLORS))),
                SelectFilter::make('floor')->label(__('admin.fields.floor'))->options(fn () => Room::distinct()->orderBy('floor')->pluck('floor', 'floor')),
            ])))
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...collect(['clean', 'dirty', 'inspected'])->map(fn ($hk) => BulkAction::make('hk_'.$hk)
                        ->label(__('admin.room.mark_as', ['status' => __('admin.housekeeping.'.$hk)]))
                        ->icon(Heroicon::OutlinedSparkles)
                        ->action(function (Collection $records) use ($hk) {
                            Room::whereIn('id', $records->modelKeys())->update(['housekeeping' => $hk]);
                            Notification::make()->title(__('admin.notify.updated'))->success()->send();
                        })
                        ->deselectRecordsAfterCompletion())->all(),
                    DeleteBulkAction::make()->visible($isManager),
                ]),
            ]);
    }
}
