<?php

namespace App\Filament\Resources\WashingServiceResource\Pages;

use App\Filament\Resources\WashingServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWashingService extends EditRecord
{
    protected static string $resource = WashingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
