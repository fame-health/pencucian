<?php

namespace App\Filament\Resources\WasherResource\Pages;

use App\Filament\Resources\WasherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWashers extends ListRecords
{
    protected static string $resource = WasherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pencuci Baru'),
        ];
    }
}
