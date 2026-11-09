<?php

namespace App\Filament\Resources\WasherResource\Pages;

use App\Filament\Resources\WasherResource;
use App\Models\WasherCommission;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWasher extends EditRecord
{
    protected static string $resource = WasherResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $comm = WasherCommission::where('user_id', $this->record->id)->first();
        if ($comm) {
            $data['commission_type'] = $comm->commission_type;
            $data['commission_value'] = $comm->commission_value;
        } else {
            $data['commission_type'] = null;
            $data['commission_value'] = null;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $commissionType = $data['commission_type'] ?? null;
        $commissionValue = $data['commission_value'] ?? null;

        unset($data['commission_type'], $data['commission_value']);

        $record->update($data);

        if (filled($commissionType) && filled($commissionValue)) {
            WasherCommission::updateOrCreate(
                ['user_id' => $record->id],
                [
                    'commission_type' => $commissionType,
                    'commission_value' => $commissionValue,
                ]
            );
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->failureNotificationTitle('Data gagal dihapus')
                ->failureNotificationBody('Data pencuci tidak dapat dihapus. Periksa apakah data masih digunakan.'),
        ];
    }
}
