<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::OutlinedPlus)->label(__('admin.booking.new')),
        ];
    }

    public static function arrivingToday(Builder $q): Builder
    {
        return $q->whereDate('check_in', today())->whereIn('status', ['pending', 'confirmed', 'checked_in']);
    }

    public static function inHouse(Builder $q): Builder
    {
        return $q->where('status', 'checked_in');
    }

    public static function departingToday(Builder $q): Builder
    {
        return $q->whereDate('check_out', today())->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);
    }

    public function getTabs(): array
    {
        $count = fn (callable $scope) => $scope(Booking::query())->count();

        return [
            'all' => Tab::make(__('admin.tabs.all'))->icon(Heroicon::OutlinedQueueList),
            'arriving' => Tab::make(__('admin.tabs.arriving_today'))
                ->icon(Heroicon::OutlinedArrowRightEndOnRectangle)
                ->badge($count([static::class, 'arrivingToday']) ?: null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $q) => static::arrivingToday($q)),
            'in_house' => Tab::make(__('admin.tabs.in_house'))
                ->icon(Heroicon::OutlinedHomeModern)
                ->badge($count([static::class, 'inHouse']) ?: null)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $q) => static::inHouse($q)),
            'departing' => Tab::make(__('admin.tabs.departing_today'))
                ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
                ->badge($count([static::class, 'departingToday']) ?: null)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $q) => static::departingToday($q)),
            'upcoming' => Tab::make(__('admin.tabs.upcoming'))
                ->icon(Heroicon::OutlinedCalendar)
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('check_in', '>', today())->whereIn('status', ['pending', 'confirmed'])),
            'pending' => Tab::make(__('admin.tabs.pending'))
                ->icon(Heroicon::OutlinedClock)
                ->badge(Booking::where('status', 'pending')->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending')),
        ];
    }
}
