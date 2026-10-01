<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Rooms\RoomResource;
use App\Filament\Support\HotelStats;
use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HotelStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected function getColumns(): int|array|null
    {
        return ['default' => 1, 'sm' => 2, 'xl' => auth()->user()?->isManager() ? 4 : 3];
    }

    protected function getStats(): array
    {
        $today = CarbonImmutable::today();
        $sellable = HotelStats::sellableRooms();
        $occupied = HotelStats::occupiedOn($today);
        $occupancy = HotelStats::occupancyOn($today);

        $nextDays = collect(range(0, 13))->map(fn ($i) => HotelStats::occupancyOn($today->addDays($i)))->all();
        $arrivals = Booking::whereDate('check_in', $today)->whereIn('status', ['pending', 'confirmed', 'checked_in'])->get(['status']);
        $arrived = $arrivals->where('status', 'checked_in')->count();
        $departures = Booking::whereDate('check_out', $today)->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->get(['status']);
        $departed = $departures->where('status', 'checked_out')->count();
        $inHouse = Booking::where('status', 'checked_in')->get(['adults', 'children']);
        $pending = Booking::where('status', 'pending')->count();
        $arrivalsWeek = collect(range(0, 6))->map(fn ($i) => Booking::whereDate('check_in', $today->addDays($i))->whereIn('status', HotelStats::SOLD)->count())->all();

        $stats = [
            Stat::make(__('admin.dashboard.occupancy_tonight'), $occupancy.'%')
                ->description(__('admin.dashboard.rooms_of', ['occupied' => $occupied, 'total' => $sellable]))
                ->descriptionIcon(Heroicon::OutlinedBuildingOffice2)
                ->chart($nextDays)
                ->color($occupancy >= 70 ? 'success' : ($occupancy >= 40 ? 'primary' : 'warning'))
                ->icon(Heroicon::OutlinedChartPie),
            Stat::make(__('admin.dashboard.arrivals_today'), $arrivals->count())
                ->description(__('admin.dashboard.checked_in_of', ['done' => $arrived, 'total' => $arrivals->count()]))
                ->descriptionIcon(Heroicon::OutlinedArrowRightEndOnRectangle)
                ->chart($arrivalsWeek)
                ->color('success')
                ->icon(Heroicon::OutlinedArrowRightEndOnRectangle)
                ->url(BookingResource::getUrl('index', ['tab' => 'arriving'])),
            Stat::make(__('admin.dashboard.departures_today'), $departures->count())
                ->description(__('admin.dashboard.checked_out_of', ['done' => $departed, 'total' => $departures->count()]))
                ->descriptionIcon(Heroicon::OutlinedArrowRightStartOnRectangle)
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
                ->url(BookingResource::getUrl('index', ['tab' => 'departing'])),
            Stat::make(__('admin.dashboard.in_house'), $inHouse->sum(fn ($b) => $b->adults + $b->children))
                ->description(trans_choice('admin.dashboard.rooms_in_house', $inHouse->count(), ['count' => $inHouse->count()]))
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('info')
                ->icon(Heroicon::OutlinedHomeModern)
                ->url(BookingResource::getUrl('index', ['tab' => 'in_house'])),
        ];

        if (auth()->user()?->isManager()) {
            $this_ = HotelStats::month($today);
            $last = HotelStats::month($today->subMonth());
            $diff = $last['revenue'] > 0 ? round(($this_['revenue'] - $last['revenue']) / $last['revenue'] * 100, 1) : 0;
            $up = $diff >= 0;
            $monthly = array_values(HotelStats::monthlyRevenue(7));

            $stats[] = Stat::make(__('admin.dashboard.revenue_month'), money($this_['revenue']))
                ->description(__('admin.dashboard.vs_last_month', ['diff' => ($up ? '+' : '').$diff.'%', 'amount' => money($last['revenue'])]))
                ->descriptionIcon($up ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($up ? 'success' : 'danger')
                ->chart($monthly)
                ->icon(Heroicon::OutlinedBanknotes);
            $stats[] = Stat::make(__('admin.dashboard.adr'), money($this_['adr']))
                ->description(__('admin.dashboard.adr_hint', ['amount' => money($last['adr'])]))
                ->descriptionIcon($this_['adr'] >= $last['adr'] ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($this_['adr'] >= $last['adr'] ? 'success' : 'warning')
                ->icon(Heroicon::OutlinedTag);
            $stats[] = Stat::make(__('admin.dashboard.revpar'), money($this_['revpar']))
                ->description(__('admin.dashboard.revpar_hint', ['occ' => $this_['occupancy'].'%']))
                ->descriptionIcon(Heroicon::OutlinedPresentationChartLine)
                ->color('primary')
                ->icon(Heroicon::OutlinedPresentationChartLine);
        }

        if (! auth()->user()?->isManager()) {
            $dirty = Room::where('housekeeping', 'dirty')->count();
            $stats[] = Stat::make(__('admin.dashboard.to_clean'), $dirty)
                ->description(__('admin.dashboard.to_clean_hint'))
                ->descriptionIcon(Heroicon::OutlinedSparkles)
                ->color($dirty ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedSparkles)
                ->url(RoomResource::getUrl('index', ['filters' => ['housekeeping' => ['value' => 'dirty']]]));
        }

        $stats[] = Stat::make(__('admin.dashboard.pending'), $pending)
            ->description(__('admin.dashboard.pending_hint'))
            ->descriptionIcon(Heroicon::OutlinedClock)
            ->color($pending ? 'warning' : 'success')
            ->icon(Heroicon::OutlinedClock)
            ->url(BookingResource::getUrl('index', ['tab' => 'pending']));

        return $stats;
    }
}
