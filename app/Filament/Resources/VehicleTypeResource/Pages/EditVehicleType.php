<?php

namespace App\Filament\Resources\VehicleTypeResource\Pages;

use App\Filament\Resources\VehicleTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleType extends EditRecord
{
    protected static string $resource = VehicleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->failureNotificationTitle('Data gagal dihapus')
                ->failureNotificationBody('Jenis kendaraan tidak dapat dihapus. Periksa apakah data masih digunakan.'),
        ];
    }
}
