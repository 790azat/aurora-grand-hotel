<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingActions;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Support\Ui;
use App\Models\Booking;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TodayDepartures extends TableWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.departures_table'))
            ->description(__('admin.dashboard.checkout_until', ['time' => setting('check_out_time')]))
            ->query(fn () => Booking::query()->with(['room', 'roomType'])
                ->whereDate('check_out', today())
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->orderByRaw("case when status = 'checked_out' then 1 else 0 end"))
            ->columns([
                ...array_slice(TodayArrivals::columns(), 0, 2),
                TextColumn::make('balance')
                    ->label(__('admin.fields.balance'))
                    ->money(Ui::currency())
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => __('admin.status.'.$state))
                    ->color(fn ($state) => Ui::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->recordUrl(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r]))
            ->recordActions([
                BookingActions::recordPayment()->iconButton(),
                BookingActions::checkOut()->button()->size('sm'),
            ])
            ->emptyStateHeading(__('admin.dashboard.no_departures'))
            ->emptyStateIcon(Heroicon::OutlinedArrowRightStartOnRectangle)
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
