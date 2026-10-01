<?php

namespace App\Filament\Widgets;

use App\Filament\Support\HotelStats;
use App\Models\Booking;
use App\Models\RoomType;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RoomTypePopularityChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public function getHeading(): string
    {
        return __('admin.dashboard.room_types_chart');
    }

    public function getDescription(): ?string
    {
        return __('admin.dashboard.room_types_chart_hint');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $nights = Booking::query()
            ->whereIn('status', HotelStats::SOLD)
            ->whereDate('check_in', '>=', now()->subYear())
            ->selectRaw('room_type_id, sum(nights) as n')
            ->groupBy('room_type_id')
            ->pluck('n', 'room_type_id');

        $types = RoomType::orderBy('sort')->get()->sortByDesc(fn ($t) => (int) ($nights[$t->id] ?? 0))->values();

        return [
            'datasets' => [[
                'label' => __('admin.dashboard.room_nights'),
                'data' => $types->map(fn ($t) => (int) ($nights[$t->id] ?? 0))->all(),
                'backgroundColor' => HotelStats::GOLD,
                'borderRadius' => 4,
                'maxBarThickness' => 22,
            ]],
            'labels' => $types->map(fn ($t) => $t->name)->all(),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(127,127,127,0.12)' } },
                y: { grid: { display: false } },
            },
        }
        JS);
    }
}
