<?php

namespace App\Filament\Resources\Guests\RelationManagers;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Filament\Support\Ui;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.booking.plural');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $columns = collect(BookingsTable::columns(Ui::currency(), compact: true))
            ->reject(fn ($c) => in_array($c->getName(), ['guest_name', 'created_at'], true))->values()->all();

        return $table
            ->recordTitleAttribute('reference')
            ->modifyQueryUsing(fn ($query) => $query->with(['roomType', 'room']))
            ->columns($columns)
            ->recordUrl(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r]))
            ->recordActions([
                Action::make('open')->label(__('admin.actions.open'))->icon('heroicon-o-arrow-right')->iconButton()
                    ->url(fn (Booking $r) => BookingResource::getUrl('view', ['record' => $r])),
            ]);
    }
}
