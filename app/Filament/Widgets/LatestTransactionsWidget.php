<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WashTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestTransactionsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transaksi Pencucian Terbaru';

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('admin');

        return $table
            ->query(function (): Builder {
                /** @var User $user */
                $user = Auth::user();
                $query = WashTransaction::query()->latest()->limit(10);

                if ($user && $user->hasRole('washer')) {
                    $query->where('washer_id', $user->id);
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('washer.name')
                    ->label('Pencuci')
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('vehicleType.name')
                    ->label('Kendaraan')
                    ->badge(),

                Tables\Columns\TextColumn::make('washingService.name')
                    ->label('Layanan'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Komisi')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in_progress' => 'Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i'),
            ])
            ->paginated(false);
    }
}
