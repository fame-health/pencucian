<?php

namespace App\Filament\Resources\WashingServiceResource\Pages;

use App\Filament\Resources\WashingServiceResource;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\ListRecords;

class ListWashingServices extends ListRecords
{
    protected static string $resource = WashingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(Dashboard::getUrl(panel: 'admin')),
            Actions\CreateAction::make()->label('Tambah Layanan Baru'),
        ];
    }
}
