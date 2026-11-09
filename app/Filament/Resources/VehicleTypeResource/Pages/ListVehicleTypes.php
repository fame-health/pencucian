<?php

namespace App\Filament\Resources\VehicleTypeResource\Pages;

use App\Filament\Resources\VehicleTypeResource;
use Filament\Actions;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\ListRecords;

class ListVehicleTypes extends ListRecords
{
    protected static string $resource = VehicleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(Dashboard::getUrl(panel: 'admin')),
            Actions\CreateAction::make()->label('Tambah Jenis Kendaraan'),
        ];
    }
}
