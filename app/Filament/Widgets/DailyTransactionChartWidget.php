<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyTransactionChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Transaksi Harian (7 Hari Terakhir)';

    public static function canView(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today()->addDay();
        $dailyCounts = WashTransaction::query()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate)
            ->selectRaw('DATE(created_at) as transaction_date, COUNT(*) as transaction_count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('transaction_count', 'transaction_date');

        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo): Carbon => Carbon::today()->subDays($daysAgo));
        $days = $dates->map(fn (Carbon $date): string => $date->format('d M'));
        $counts = $dates->map(fn (Carbon $date): int => (int) ($dailyCounts[$date->toDateString()] ?? 0));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $counts->all(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => $days->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
