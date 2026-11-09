<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class WasherStatsOverviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = [
        'default' => 2,
        'lg' => 4,
    ];

    public static function canView(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole('washer');
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $washerId = $user ? $user->id : 0;
        $todayStart = today()->startOfDay();
        $monthStart = now()->startOfMonth();
        $tomorrowStart = $todayStart->copy()->addDay();
        $transactions = WashTransaction::query()
            ->where('washer_id', $washerId)
            ->where('created_at', '>=', $monthStart)
            ->where('created_at', '<', $tomorrowStart)
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as today_washes, SUM(CASE WHEN created_at >= ? AND status = ? THEN 1 ELSE 0 END) as completed_washes, SUM(CASE WHEN created_at >= ? AND status = ? THEN commission_amount ELSE 0 END) as earnings_today, SUM(CASE WHEN status = ? THEN commission_amount ELSE 0 END) as earnings_month',
                [$todayStart, $todayStart, 'completed', $todayStart, 'completed', 'completed'],
            )
            ->first();

        $todayWashes = (int) $transactions->today_washes;
        $completedWashes = (int) $transactions->completed_washes;
        $earningsToday = (float) $transactions->earnings_today;
        $earningsMonth = (float) $transactions->earnings_month;

        return [
            Stat::make('Cucian Saya Hari Ini', $todayWashes)
                ->description('Total kendaraan yang diinput')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Pekerjaan Selesai', $completedWashes)
                ->description('Cucian selesai hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pendapatan Saya Hari Ini', 'Rp ' . number_format($earningsToday, 0, ',', '.'))
                ->description('Komisi bersih hari ini')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Pendapatan Saya Bulan Ini', 'Rp ' . number_format($earningsMonth, 0, ',', '.'))
                ->description('Komisi akumulasi bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
