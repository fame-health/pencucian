<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Pendapatan Usaha Bulanan (Tahun Ini)';

    public static function canView(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    protected function getData(): array
    {
        $yearStart = Carbon::now()->startOfYear();
        $nextYearStart = $yearStart->copy()->addYear();
        $monthExpression = match (WashTransaction::query()->getConnection()->getDriverName()) {
            'mysql', 'mariadb', 'sqlsrv' => 'MONTH(created_at)',
            'pgsql' => 'EXTRACT(MONTH FROM created_at)',
            default => "CAST(strftime('%m', created_at) AS INTEGER)",
        };
        $monthlyRevenue = WashTransaction::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->selectRaw("{$monthExpression} as transaction_month, SUM(business_revenue) as monthly_revenue")
            ->groupByRaw($monthExpression)
            ->pluck('monthly_revenue', 'transaction_month');

        $months = collect(range(1, 12));
        $labels = $months->map(fn (int $month): string => Carbon::create($yearStart->year, $month, 1)->format('M'));
        $revenues = $months->map(fn (int $month): float => (float) ($monthlyRevenue[$month] ?? 0));

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Usaha (Rp)',
                    'data' => $revenues->all(),
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#4BC0C0',
                ],
            ],
            'labels' => $labels->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
