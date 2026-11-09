<?php

namespace App\Filament\Resources\WasherResource\Pages;

use App\Filament\Resources\WasherResource;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\ListRecords;

class ListWashers extends ListRecords
{
    protected static string $resource = WasherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(Dashboard::getUrl(panel: 'admin')),
            Actions\CreateAction::make()->label('Tambah Pencuci Baru'),
        ];
    }
}
