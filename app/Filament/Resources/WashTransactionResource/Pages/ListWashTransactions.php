<?php

namespace App\Filament\Resources\WashTransactionResource\Pages;

use App\Filament\Resources\WashTransactionResource;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\ListRecords;

class ListWashTransactions extends ListRecords
{
    protected static string $resource = WashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(Dashboard::getUrl(panel: 'admin')),
            Actions\CreateAction::make()->label('Input Transaksi Baru'),
        ];
    }
}
