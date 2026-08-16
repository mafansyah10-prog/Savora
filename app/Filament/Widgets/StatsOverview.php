<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '3s';

    protected function getStats(): array
    {
        // Get the selected date from filters, or default to today
        $dateString = $this->filters['sales_date'] ?? now()->toDateString();
        $selectedDate = Carbon::parse($dateString);
        $dateLabel = $selectedDate->isToday() ? 'Hari Ini' : $selectedDate->translatedFormat('d M Y');

        // Total Revenue (Cumulative overall, ignoring date filter)
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        // Pending orders (overall count)
        $pendingOrders = Order::where('status', 'pending')->count();

        // Successful orders count on the selected date
        $totalOrders = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $selectedDate)
            ->count();

        // New customers registered on the selected date
        $newCustomers = User::where('email', '!=', 'admin@gmail.com')
            ->whereDate('created_at', $selectedDate)
            ->count();

        // Today's revenue (specifically for calculating average order value on the selected date)
        $todayRevenue = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $selectedDate)
            ->sum('total_amount');

        // Average order value on the selected date
        $avgRevenue = $totalOrders > 0 ? $todayRevenue / $totalOrders : 0;

        // Generate a mini trend chart for the 7 days leading up to the selected date
        $dailyRevenue = collect(range(6, 0))->map(function ($daysAgo) use ($selectedDate) {
            return Order::whereDate('created_at', $selectedDate->copy()->subDays($daysAgo))
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
        })->toArray();

        return [
            Stat::make('Total Pendapatan', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description($pendingOrders.' pesanan menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($dailyRevenue ?: [0, 0, 0, 0, 0, 0, 0])
                ->color('success'),

            Stat::make("Pelanggan Baru ({$dateLabel})", number_format($newCustomers, 0, ',', '.'))
                ->description('Pengguna terdaftar pada tanggal ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make("Rata-rata Nilai Order ({$dateLabel})", 'Rp '.number_format($avgRevenue, 0, ',', '.'))
                ->description('Per transaksi dari '.$totalOrders.' pesanan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
