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

        $todayWashes = WashTransaction::where('washer_id', $washerId)->whereDate('created_at', today())->count();
        $completedWashes = WashTransaction::where('washer_id', $washerId)->whereDate('created_at', today())->where('status', 'completed')->count();
        $earningsToday = WashTransaction::where('washer_id', $washerId)->whereDate('created_at', today())->where('status', 'completed')->sum('commission_amount');
        $earningsMonth = WashTransaction::where('washer_id', $washerId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status', 'completed')->sum('commission_amount');

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
