<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RevenueReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Pendapatan';

    protected static ?string $title = 'Laporan Pendapatan & Komisi';

    protected string $view = 'filament.pages.revenue-report';

    public ?string $period = 'this_month';

    public ?string $from_date = null;

    public ?string $until_date = null;

    public ?int $washer_id = null;

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user && $user->hasRole('admin');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'from_date' => now()->startOfMonth()->format('Y-m-d'),
            'until_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('period')
                    ->label('Periode Laporan')
                    ->options([
                        'today' => 'Hari Ini',
                        'this_week' => 'Minggu Ini',
                        'this_month' => 'Bulan Ini',
                        'custom' => 'Kustom Tanggal',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state === 'today') {
                            $set('from_date', now()->format('Y-m-d'));
                            $set('until_date', now()->format('Y-m-d'));
                        } elseif ($state === 'this_week') {
                            $set('from_date', now()->startOfWeek()->format('Y-m-d'));
                            $set('until_date', now()->endOfWeek()->format('Y-m-d'));
                        } elseif ($state === 'this_month') {
                            $set('from_date', now()->startOfMonth()->format('Y-m-d'));
                            $set('until_date', now()->endOfMonth()->format('Y-m-d'));
                        }
                    }),

                DatePicker::make('from_date')
                    ->label('Dari Tanggal')
                    ->reactive(),

                DatePicker::make('until_date')
                    ->label('Sampai Tanggal')
                    ->reactive(),

                Select::make('washer_id')
                    ->label('Filter Pencuci')
                    ->options(fn () => User::role('washer')->pluck('name', 'id'))
                    ->placeholder('Semua Pencuci')
                    ->reactive(),
            ])->columns(4);
    }

    public function getReportSummaryProperty(): array
    {
        $data = $this->form->getState();

        $query = $this->applyReportDateRange(
            WashTransaction::query()->where('status', 'completed'),
            $data,
        );

        if (!empty($data['washer_id'])) {
            $query->where('washer_id', $data['washer_id']);
        }

        $summary = $query
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(price), 0) as total_omzet, COALESCE(SUM(commission_amount), 0) as total_commission, COALESCE(SUM(business_revenue), 0) as total_revenue')
            ->first();

        return [
            'total_omzet' => (float) $summary->total_omzet,
            'total_commission' => (float) $summary->total_commission,
            'total_revenue' => (float) $summary->total_revenue,
            'total_count' => (int) $summary->total_count,
        ];
    }

    public function getWasherBreakdownProperty()
    {
        $data = $this->form->getState();
        $summaries = $this->applyReportDateRange(
            WashTransaction::query()->where('status', 'completed'),
            $data,
        )
            ->select('washer_id')
            ->selectRaw('COUNT(*) as total_washes, COALESCE(SUM(price), 0) as total_omzet, COALESCE(SUM(commission_amount), 0) as total_commission')
            ->groupBy('washer_id')
            ->get()
            ->keyBy('washer_id');

        return User::role('washer')
            ->get(['id', 'name', 'email'])
            ->each(function (User $washer) use ($summaries): void {
                $summary = $summaries->get($washer->id);
                $washer->setAttribute('total_washes', (int) ($summary->total_washes ?? 0));
                $washer->setAttribute('total_omzet', (float) ($summary->total_omzet ?? 0));
                $washer->setAttribute('total_commission', (float) ($summary->total_commission ?? 0));
            });
    }

    /**
     * Keep date filtering index-friendly by comparing timestamp boundaries.
     *
     * @param  array<string, mixed>  $data
     */
    private function applyReportDateRange(Builder $query, array $data): Builder
    {
        if (filled($data['from_date'] ?? null)) {
            $query->where('created_at', '>=', Carbon::parse($data['from_date'])->startOfDay());
        }

        if (filled($data['until_date'] ?? null)) {
            $query->where('created_at', '<', Carbon::parse($data['until_date'])->addDay()->startOfDay());
        }

        return $query;
    }
}
