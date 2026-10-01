<?php

namespace App\Filament\Widgets;

use App\Filament\Support\HotelStats;
use Carbon\CarbonImmutable;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public function getHeading(): string
    {
        return __('admin.dashboard.revenue_chart');
    }

    public function getDescription(): ?string
    {
        return __('admin.dashboard.revenue_chart_hint');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $data = HotelStats::monthlyRevenue(12);
        $labels = collect(array_keys($data))->map(fn ($k) => CarbonImmutable::createFromFormat('Y-m-d', $k.'-01')->locale(app()->getLocale())->isoFormat('MMM YY'))->all();

        return [
            'datasets' => [[
                'label' => __('admin.dashboard.revenue'),
                'data' => array_values($data),
                'borderColor' => HotelStats::GOLD,
                'backgroundColor' => 'rgba(168,128,58,0.12)',
                'pointBackgroundColor' => HotelStats::GOLD,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): RawJs
    {
        $symbol = "'".addslashes((string) setting('currency_symbol', '$'))."'";

        return RawJs::make(<<<JS
        {
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => ' ' + {$symbol} + Math.round(c.parsed.y).toLocaleString() } },
            },
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => {$symbol} + (v >= 1000 ? Math.round(v / 1000) + 'k' : v) }, grid: { color: 'rgba(127,127,127,0.12)' } },
                x: { grid: { display: false } },
            },
        }
        JS);
    }
}
