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
        $days = collect();
        $counts = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('d M'));

            $count = WashTransaction::whereDate('created_at', $date->toDateString())->count();
            $counts->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $counts->toArray(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
