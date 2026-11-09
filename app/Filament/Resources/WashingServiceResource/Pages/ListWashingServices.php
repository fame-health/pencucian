<?php

namespace App\Filament\Resources\WashingServiceResource\Pages;

use App\Filament\Resources\WashingServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWashingServices extends ListRecords
{
    protected static string $resource = WashingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Layanan Baru'),
        ];
    }
}
