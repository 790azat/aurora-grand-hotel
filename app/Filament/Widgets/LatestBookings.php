<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Filament\Support\Ui;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestBookings extends TableWidget
{
    protected static ?int $sort = 8;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $columns = collect(BookingsTable::columns(Ui::currency(), compact: true))
            ->reject(fn ($c) => in_array($c->getName(), ['check_out', 'amount_paid', 'adults', 'created_at', 'nights'], true))
            ->values()->all();

        return $table
            ->heading(__('admin.dashboard.latest_bookings'))
            ->query(fn () => Booking::query()->with(['roomType', 'room'])->latest('created_at'))
            ->columns($columns)
            ->recordUrl(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r]))
            ->headerActions([
                Action::make('all')->label(__('admin.dashboard.view_all'))->link()->url(BookingResource::getUrl('index')),
            ])
            ->paginated([8])
            ->defaultPaginationPageOption(8);
    }
}
