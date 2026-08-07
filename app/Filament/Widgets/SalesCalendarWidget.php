<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class SalesCalendarWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '3s';

    protected function getStats(): array
    {
        // Get the selected date from filters, or default to today
        $dateString = $this->filters['sales_date'] ?? now()->toDateString();
        $selectedDate = Carbon::parse($dateString);

        // Check if selected date is today
        $isToday = $selectedDate->isToday();
        
        // Define labeling helper based on date
        if ($isToday) {
            $dayLabel = 'Hari Ini';
            $monthLabel = 'Bulan Ini';
            $yearLabel = 'Tahun Ini';
        } else {
            $dayLabel = $selectedDate->translatedFormat('d F Y');
            $monthLabel = $selectedDate->translatedFormat('F Y');
            $yearLabel = $selectedDate->translatedFormat('Y');
        }

        $salesToday = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $selectedDate)
            ->sum('total_amount');

        $salesMonth = Order::where('status', '!=', 'cancelled')
            ->whereMonth('created_at', $selectedDate->month)
            ->whereYear('created_at', $selectedDate->year)
            ->sum('total_amount');

        $salesYear = Order::where('status', '!=', 'cancelled')
            ->whereYear('created_at', $selectedDate->year)
            ->sum('total_amount');

        // Today's total count
        $countToday = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $selectedDate)
            ->count();

        // This Month's total count
        $countMonth = Order::where('status', '!=', 'cancelled')
            ->whereMonth('created_at', $selectedDate->month)
            ->whereYear('created_at', $selectedDate->year)
            ->count();

        // This Year's total count
        $countYear = Order::where('status', '!=', 'cancelled')
            ->whereYear('created_at', $selectedDate->year)
            ->count();

        return [
            Stat::make("Penjualan {$dayLabel}", 'Rp ' . number_format($salesToday, 0, ',', '.'))
                ->description($countToday . " transaksi sukses ({$dayLabel})")
                ->descriptionIcon('heroicon-m-calendar')
                ->icon('heroicon-m-calendar')
                ->color('success'),

            Stat::make("Penjualan {$monthLabel}", 'Rp ' . number_format($salesMonth, 0, ',', '.'))
                ->description($countMonth . " transaksi sukses ({$monthLabel})")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make("Penjualan {$yearLabel}", 'Rp ' . number_format($salesYear, 0, ',', '.'))
                ->description($countYear . " transaksi sukses ({$yearLabel})")
                ->descriptionIcon('heroicon-m-calendar')
                ->icon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
