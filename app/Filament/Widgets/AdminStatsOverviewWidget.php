<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AdminStatsOverviewWidget extends BaseWidget
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
        return $user && $user->hasRole('admin');
    }

    protected function getStats(): array
    {
        $todayWashes = WashTransaction::whereDate('created_at', today())->count();
        $todayCompleted = WashTransaction::whereDate('created_at', today())->where('status', 'completed')->count();
        $todayRevenue = WashTransaction::whereDate('created_at', today())->where('status', 'completed')->sum('business_revenue');
        $activeWashers = User::role('washer')->where('is_active', true)->count();

        return [
            Stat::make('Total Cucian Hari Ini', $todayWashes)
                ->description('Jumlah kendaraan yang masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Pekerjaan Selesai', $todayCompleted)
                ->description('Status completed hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pendapatan Usaha Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Laba bersih usaha hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Pencuci Aktif', $activeWashers)
                ->description('Jumlah staf pencuci aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
