<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\NavGroup;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Resources\Bookings\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Schemas\BookingInfolist;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Reservations;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static int $globalSearchResultsLimit = 8;

    public static function getModelLabel(): string
    {
        return __('admin.booking.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.booking.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Booking::where('status', 'pending')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('admin.booking.pending_badge');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'first_name', 'last_name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->reference.' · '.$record->guest_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('admin.fields.dates') => $record->check_in->isoFormat('D MMM').' → '.$record->check_out->isoFormat('D MMM YYYY'),
            __('admin.fields.room_type') => $record->roomType?->name,
            __('admin.fields.status') => __('admin.status.'.$record->status),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('roomType');
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
