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
        $months = collect();
        $revenues = collect();

        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create(now()->year, $m, 1)->format('M');
            $months->push($monthName);

            $rev = WashTransaction::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $m)
                ->where('status', 'completed')
                ->sum('business_revenue');

            $revenues->push((float) $rev);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Usaha (Rp)',
                    'data' => $revenues->toArray(),
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#4BC0C0',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
