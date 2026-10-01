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

class TodayArrivals extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.arrivals_table'))
            ->description(now()->locale(app()->getLocale())->isoFormat('dddd, D MMMM'))
            ->query(fn () => Booking::query()->with(['room', 'roomType'])
                ->whereDate('check_in', today())
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->orderByRaw("case when status = 'checked_in' then 1 else 0 end")
                ->orderBy('arrival_time'))
            ->columns(static::columns())
            ->recordUrl(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r]))
            ->recordActions([
                BookingActions::checkIn()->button()->size('sm'),
            ])
            ->emptyStateHeading(__('admin.dashboard.no_arrivals'))
            ->emptyStateIcon(Heroicon::OutlinedArrowRightEndOnRectangle)
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('guest_name')
                ->label(__('admin.fields.guest'))
                ->weight('semibold')
                ->description(fn (Booking $r) => $r->reference.' · '.trans_choice('admin.booking.nights_x', $r->nights, ['count' => $r->nights])),
            TextColumn::make('room.number')
                ->label(__('admin.fields.room'))
                ->placeholder('—')
                ->description(fn (Booking $r) => $r->roomType?->name),
            TextColumn::make('arrival_time')
                ->label(__('admin.fields.eta'))
                ->placeholder('—'),
            TextColumn::make('status')
                ->label(__('admin.fields.status'))
                ->badge()
                ->formatStateUsing(fn ($state) => __('admin.status.'.$state))
                ->color(fn ($state) => Ui::STATUS_COLORS[$state] ?? 'gray'),
        ];
    }
}
