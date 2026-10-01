<?php

namespace App\Filament\Widgets;

use App\Filament\Support\HotelStats;
use Carbon\CarbonImmutable;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class OccupancyForecastChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '280px';

    public function getHeading(): string
    {
        return __('admin.dashboard.occupancy_chart');
    }

    public function getDescription(): ?string
    {
        return __('admin.dashboard.occupancy_chart_hint');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $today = CarbonImmutable::today();
        $days = collect(range(0, 29))->map(fn ($i) => $today->addDays($i));
        $values = $days->map(fn ($d) => HotelStats::occupancyOn($d))->all();

        return [
            'datasets' => [[
                'label' => __('admin.dashboard.occupancy'),
                'data' => $values,
                'backgroundColor' => $days->map(fn ($d) => $d->isWeekend() ? '#7a5fc9' : HotelStats::GOLD)->all(),
                'borderRadius' => 4,
                'borderSkipped' => 'start',
                'maxBarThickness' => 18,
            ]],
            'labels' => $days->map(fn ($d) => $d->locale(app()->getLocale())->isoFormat('D MMM'))->all(),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => ' ' + c.parsed.y + '%' } },
            },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: (v) => v + '%', stepSize: 25 }, grid: { color: 'rgba(127,127,127,0.12)' } },
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
            },
        }
        JS);
    }
}
