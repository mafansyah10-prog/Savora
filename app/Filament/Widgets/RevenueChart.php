<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 2;
    protected ?string $pollingInterval = '3s';

    protected function getData(): array
    {
        $labels = [];
        $onlineData = [];
        $offlineData = [];

        foreach (range(7, 0) as $daysAgo) {
            $date = now()->subDays($daysAgo);
            $labels[] = $date->format('d M');

            // Online orders (e_wallet)
            $onlineData[] = Order::whereDate('created_at', $date)
                ->where('payment_method', 'e_wallet')
                ->sum('total_amount');

            // Offline / bank transfer orders
            $offlineData[] = Order::whereDate('created_at', $date)
                ->where('payment_method', 'transfer_bank')
                ->sum('total_amount');
        }

        // If all zeros (no real data), use sample data for visual demo
        $hasData = array_sum($onlineData) > 0 || array_sum($offlineData) > 0;
        if (!$hasData) {
            $onlineData  = [60000, 110000, 210000, 150000, 250000, 100000, 180000, 120000];
            $offlineData = [90000, 200000, 100000, 180000, 80000,  240000, 130000, 200000];
        }

        return [
            'datasets' => [
                [
                    'label' => 'E-Wallet',
                    'data' => $onlineData,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#f97316',
                    'pointRadius' => 4,
                ],
                [
                    'label' => 'Transfer Bank',
                    'data' => $offlineData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#10b981',
                    'pointRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
