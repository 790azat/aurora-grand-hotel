<?php

namespace App\Filament\Widgets;

use App\Filament\Support\HotelStats;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingSourcesChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public function getHeading(): string
    {
        return __('admin.dashboard.sources_chart');
    }

    public function getDescription(): ?string
    {
        return __('admin.dashboard.sources_chart_hint');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Booking::query()
            ->whereDate('created_at', '>=', now()->subYear())
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('source, count(*) as c')
            ->groupBy('source')
            ->pluck('c', 'source');

        // Colour follows the source (fixed order), never its rank.
        $sources = collect(Booking::SOURCES)->filter(fn ($s) => ($counts[$s] ?? 0) > 0)->values();

        return [
            'datasets' => [[
                'data' => $sources->map(fn ($s) => (int) $counts[$s])->all(),
                'backgroundColor' => $sources->map(fn ($s) => HotelStats::PALETTE[array_search($s, Booking::SOURCES)])->all(),
                'borderWidth' => 2,
                'hoverOffset' => 6,
            ]],
            'labels' => $sources->map(fn ($s) => __('admin.source.'.$s))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '62%',
            'plugins' => ['legend' => ['position' => 'right', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8]]],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
