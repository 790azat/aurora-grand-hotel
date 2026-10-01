<?php

namespace App\Filament\Pages;

use App\Filament\NavGroup;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Models\RoomType;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use UnitEnum;

/** Reservation grid ("Шахматка"): rooms × days with booking bars. */
class TapeChart extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::TableCells;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Reservations;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'tape-chart';

    protected string $view = 'filament.pages.tape-chart';

    protected Width|string|null $maxContentWidth = Width::Full;

    public const DAY_OPTIONS = [7, 14, 21, 30, 45];

    #[Url(as: 'from')]
    public string $start = '';

    #[Url(as: 'days')]
    public int $days = 21;

    public static function getNavigationLabel(): string
    {
        return __('admin.tape.title');
    }

    public function getTitle(): string
    {
        return __('admin.tape.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.tape.subheading');
    }

    public function mount(): void
    {
        $this->normalize();
    }

    public function updatedStart(): void
    {
        $this->normalize();
    }

    public function updatedDays(): void
    {
        $this->normalize();
    }

    protected function normalize(): void
    {
        try {
            $this->start = CarbonImmutable::parse($this->start ?: 'today')->toDateString();
        } catch (\Throwable) {
            $this->start = CarbonImmutable::today()->toDateString();
        }
        if (! in_array($this->days, self::DAY_OPTIONS, true)) {
            $this->days = 21;
        }
    }

    public function previous(): void
    {
        $this->start = CarbonImmutable::parse($this->start)->subDays(7)->toDateString();
    }

    public function next(): void
    {
        $this->start = CarbonImmutable::parse($this->start)->addDays(7)->toDateString();
    }

    public function goToday(): void
    {
        $this->start = CarbonImmutable::today()->toDateString();
    }

    protected function getViewData(): array
    {
        $start = CarbonImmutable::parse($this->start)->startOfDay();
        $end = $start->addDays($this->days);
        $today = CarbonImmutable::today();
        $locale = app()->getLocale();

        $dates = collect(range(0, $this->days - 1))->map(fn ($i) => $start->addDays($i)->locale($locale));

        $types = RoomType::orderBy('sort')->with(['rooms' => fn ($q) => $q->orderBy('number')])->get();

        $bookings = Booking::query()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'checked_out'])
            ->whereDate('check_in', '<', $end->toDateString())
            ->whereDate('check_out', '>', $start->toDateString())
            ->get();

        $bars = $bookings->map(function (Booking $b) use ($start) {
            $in = CarbonImmutable::parse($b->check_in)->startOfDay();
            $out = CarbonImmutable::parse($b->check_out)->startOfDay();
            // Bars run from mid check-in day to mid check-out day.
            $from = max(0, $start->diffInDays($in, false) + 0.5);
            $to = min($this->days, $start->diffInDays($out, false) + 0.5);

            return [
                'id' => $b->id,
                'room_id' => $b->room_id,
                'room_type_id' => $b->room_type_id,
                'left' => $from,
                'width' => max(0.4, $to - $from),
                'cut_left' => $in->lt($start),
                'cut_right' => $out->gt($start->addDays($this->days - 1)),
                'status' => $b->status,
                'guest' => $b->guest_name,
                'ref' => $b->reference,
                'dates' => $in->locale(app()->getLocale())->isoFormat('D MMM').' → '.$out->locale(app()->getLocale())->isoFormat('D MMM'),
                'nights' => $b->nights,
                'guests' => $b->adults + $b->children,
                'total' => money($b->total, true),
                'unpaid' => $b->balance > 0 && $b->status !== 'checked_out',
                'url' => BookingResource::getUrl('view', ['record' => $b]),
            ];
        });

        $byRoom = $bars->whereNotNull('room_id')->groupBy('room_id');
        $unassigned = $bars->whereNull('room_id')->groupBy('room_type_id');

        // Free rooms per type per day (for the type header row).
        $free = [];
        foreach ($types as $type) {
            $sellable = $type->rooms->where('status', 'available')->count();
            foreach ($dates as $i => $d) {
                $ds = $d->toDateString();
                $busy = $bookings->filter(fn ($b) => $b->room_type_id === $type->id
                    && $b->check_in->toDateString() <= $ds && $b->check_out->toDateString() > $ds)->count();
                $free[$type->id][$i] = max(0, $sellable - $busy);
            }
        }

        $occupancy = $dates->map(function ($d) use ($bookings, $types) {
            $ds = $d->toDateString();
            $rooms = max(1, $types->sum(fn ($t) => $t->rooms->where('status', 'available')->count()));
            $busy = $bookings->filter(fn ($b) => $b->check_in->toDateString() <= $ds && $b->check_out->toDateString() > $ds)->count();

            return (int) round(min(100, $busy / $rooms * 100));
        });

        return [
            'dates' => $dates,
            'today' => $today,
            'types' => $types,
            'byRoom' => $byRoom,
            'unassigned' => $unassigned,
            'free' => $free,
            'occupancy' => $occupancy,
            'createUrl' => BookingResource::getUrl('create'),
            'canCreate' => BookingResource::canCreate(),
            'rangeLabel' => $start->locale($locale)->isoFormat('D MMM').' – '.$end->subDay()->locale($locale)->isoFormat('D MMM YYYY'),
        ];
    }
}
