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

        $query = WashTransaction::query()
            ->where('status', 'completed');

        if (!empty($data['from_date'])) {
            $query->whereDate('created_at', '>=', $data['from_date']);
        }

        if (!empty($data['until_date'])) {
            $query->whereDate('created_at', '<=', $data['until_date']);
        }

        if (!empty($data['washer_id'])) {
            $query->where('washer_id', $data['washer_id']);
        }

        $totalOmzet = (float) $query->sum('price');
        $totalCommission = (float) $query->sum('commission_amount');
        $totalRevenue = (float) $query->sum('business_revenue');
        $totalCount = $query->count();

        return [
            'total_omzet' => $totalOmzet,
            'total_commission' => $totalCommission,
            'total_revenue' => $totalRevenue,
            'total_count' => $totalCount,
        ];
    }

    public function getWasherBreakdownProperty()
    {
        $data = $this->form->getState();

        return User::role('washer')
            ->withCount(['washTransactions as total_washes' => function ($q) use ($data) {
                $q->where('status', 'completed');
                if (!empty($data['from_date'])) {
                    $q->whereDate('created_at', '>=', $data['from_date']);
                }
                if (!empty($data['until_date'])) {
                    $q->whereDate('created_at', '<=', $data['until_date']);
                }
            }])
            ->withSum(['washTransactions as total_omzet' => function ($q) use ($data) {
                $q->where('status', 'completed');
                if (!empty($data['from_date'])) {
                    $q->whereDate('created_at', '>=', $data['from_date']);
                }
                if (!empty($data['until_date'])) {
                    $q->whereDate('created_at', '<=', $data['until_date']);
                }
            }], 'price')
            ->withSum(['washTransactions as total_commission' => function ($q) use ($data) {
                $q->where('status', 'completed');
                if (!empty($data['from_date'])) {
                    $q->whereDate('created_at', '>=', $data['from_date']);
                }
                if (!empty($data['until_date'])) {
                    $q->whereDate('created_at', '<=', $data['until_date']);
                }
            }], 'commission_amount')
            ->get();
    }
}
