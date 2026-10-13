<?php

namespace App\Filament\Widgets;

use App\Support\Sales;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales — last 30 days';

    protected ?string $description = 'Card/UPI and cash, per day (₹)';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $daily = Sales::daily(30);

        return [
            'datasets' => [[
                'label' => 'Sales (₹)',
                'data' => array_map(fn ($paise) => $paise / 100, array_values($daily)),
                'borderColor' => '#8c1c2b',
                'backgroundColor' => 'rgba(140, 28, 43, 0.08)',
                'fill' => true,
                'tension' => 0.3,
                'pointRadius' => 2,
            ]],
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('d M'), array_keys($daily)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true]]];
    }
}
