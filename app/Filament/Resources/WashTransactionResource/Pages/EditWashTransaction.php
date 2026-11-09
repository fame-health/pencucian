<?php

namespace App\Filament\Resources\WashTransactionResource\Pages;

use App\Filament\Resources\WashTransactionResource;
use App\Services\TransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWashTransaction extends EditRecord
{
    protected static string $resource = WashTransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photo_before_path'] = $this->record->photoBefore?->photo_path;
        $data['photo_after_path'] = $this->record->photoAfter?->photo_path;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $photoBefore = $data['photo_before_path'] ?? null;
        $photoAfter = $data['photo_after_path'] ?? null;

        unset($data['photo_before_path'], $data['photo_after_path']);

        $service = app(TransactionService::class);

        // Update status or basic attributes if status completed set completed_at
        if (isset($data['status']) && $data['status'] === 'completed' && !$record->completed_at) {
            $data['completed_at'] = now();
        }

        $record->update($data);

        if ($photoBefore) {
            $service->savePhoto($record, $photoBefore, 'before');
        }

        if ($photoAfter) {
            $service->savePhoto($record, $photoAfter, 'after');
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->failureNotificationTitle('Transaksi gagal dihapus')
                ->failureNotificationBody('Transaksi tidak dapat dihapus. Silakan coba kembali.'),
        ];
    }
}
