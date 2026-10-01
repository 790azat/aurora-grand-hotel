<?php

namespace App\Filament\Support;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Hotel KPIs for the dashboard. Revenue is pro-rated per night, so a stay that spans
 * two months contributes to both (standard "revenue on the books" accounting).
 */
class HotelStats
{
    /** Validated categorical palette (passes CVD + contrast checks in light and dark). */
    public const PALETTE = ['#a8803a', '#2a78d6', '#d95926', '#199e70', '#7a5fc9', '#d55181'];

    public const GOLD = '#a8803a';

    /** Statuses that count as sold room nights. */
    public const SOLD = ['pending', 'confirmed', 'checked_in', 'checked_out'];

    protected static array $memo = [];

    public static function sellableRooms(): int
    {
        return static::$memo['sellable'] ??= Room::where('status', 'available')->count();
    }

    public static function totalRooms(): int
    {
        return static::$memo['rooms'] ??= Room::count();
    }

    /** Bookings that occupy a room during [from, to). */
    public static function staysBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $key = 'stays'.$from->toDateString().$to->toDateString();

        return static::$memo[$key] ??= Booking::query()
            ->whereIn('status', self::SOLD)
            ->whereDate('check_in', '<', $to->toDateString())
            ->whereDate('check_out', '>', $from->toDateString())
            ->get(['id', 'room_type_id', 'check_in', 'check_out', 'nights', 'room_total', 'total', 'status', 'source']);
    }

    /** Rooms occupied on the night of $date. */
    public static function occupiedOn(CarbonImmutable $date): int
    {
        return Booking::query()
            ->whereIn('status', self::SOLD)
            ->whereDate('check_in', '<=', $date->toDateString())
            ->whereDate('check_out', '>', $date->toDateString())
            ->count();
    }

    public static function occupancyOn(CarbonImmutable $date): float
    {
        $rooms = max(1, static::sellableRooms());

        return round(min(100, static::occupiedOn($date) / $rooms * 100), 1);
    }

    /**
     * Pro-rated metrics for [from, to).
     *
     * @return array{room_nights:int, room_revenue:float, revenue:float, adr:float, revpar:float, occupancy:float}
     */
    public static function period(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $days = max(1, (int) $from->diffInDays($to));
        $nightsSold = 0;
        $roomRevenue = 0.0;
        $revenue = 0.0;

        foreach (static::staysBetween($from, $to) as $b) {
            $in = CarbonImmutable::parse($b->check_in)->max($from);
            $out = CarbonImmutable::parse($b->check_out)->min($to);
            $n = max(0, (int) $in->diffInDays($out));
            $total = max(1, (int) $b->nights);
            $nightsSold += $n;
            $roomRevenue += (float) $b->room_total * $n / $total;
            $revenue += (float) $b->total * $n / $total;
        }

        $capacity = max(1, static::sellableRooms() * $days);

        return [
            'room_nights' => $nightsSold,
            'room_revenue' => round($roomRevenue, 2),
            'revenue' => round($revenue, 2),
            'adr' => $nightsSold ? round($roomRevenue / $nightsSold, 2) : 0.0,
            'revpar' => round($roomRevenue / $capacity, 2),
            'occupancy' => round(min(100, $nightsSold / $capacity * 100), 1),
        ];
    }

    public static function month(CarbonImmutable $month): array
    {
        $start = $month->startOfMonth();

        return static::period($start, $start->addMonth());
    }

    /** Revenue for the last N months (oldest first) keyed by "Y-m". */
    public static function monthlyRevenue(int $months = 12): array
    {
        $out = [];
        $current = CarbonImmutable::today()->startOfMonth();
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = $current->subMonths($i);
            $out[$m->format('Y-m')] = static::month($m)['revenue'];
        }

        return $out;
    }
}
