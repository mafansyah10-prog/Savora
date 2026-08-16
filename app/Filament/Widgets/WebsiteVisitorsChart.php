<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class WebsiteVisitorsChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Pesanan';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '3s';

    protected function getData(): array
    {
        $pending = Order::where('status', 'pending')->count();
        $paid = Order::where('status', 'paid')->count();
        $shipped = Order::where('status', 'shipped')->count();
        $completed = Order::where('status', 'completed')->count();
        $cancelled = Order::where('status', 'cancelled')->count();

        // Fallback sample data if database is empty
        if (($pending + $paid + $shipped + $completed + $cancelled) === 0) {
            $pending = 42;
            $paid = 27;
            $shipped = 15;
            $completed = 10;
            $cancelled = 6;
        }

        return [
            'datasets' => [
                [
                    'data' => [$pending, $paid, $shipped, $completed, $cancelled],
                    'backgroundColor' => ['#f97316', '#10b981', '#3b82f6', '#8b5cf6', '#ef4444'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['Pending', 'Dibayar', 'Dikirim', 'Selesai', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '70%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'boxWidth' => 12,
                        'font' => ['size' => 11],
                    ],
                ],
            ],
        ];
    }
}
