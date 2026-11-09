<?php

namespace App\Filament\Resources\WashTransactionResource\Pages;

use App\Filament\Resources\WashTransactionResource;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\WashingService;
use App\Services\TransactionService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateWashTransaction extends CreateRecord
{
    protected static string $resource = WashTransactionResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->hasRole('washer')) {
            $data['washer_id'] = $user->id;
        }

        $photoAfter = $data['photo_after_capture'] ?? null;

        if (! VehicleType::query()
            ->whereKey($data['vehicle_type_id'] ?? null)
            ->where('category', $data['vehicle_category'] ?? null)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'data.vehicle_type_id' => 'Pilih tipe kendaraan yang sesuai.',
            ]);
        }

        $washingServiceId = $data['washing_service_id'] ?? null;

        if (blank($washingServiceId)) {
            $availableServiceIds = WashingService::query()
                ->where('vehicle_type_id', $data['vehicle_type_id'])
                ->where('is_active', true)
                ->limit(2)
                ->pluck('id');

            if ($availableServiceIds->count() === 1) {
                $washingServiceId = $availableServiceIds->first();
                $data['washing_service_id'] = $washingServiceId;
            }
        }

        if (! WashingService::query()
            ->whereKey($washingServiceId)
            ->where('vehicle_type_id', $data['vehicle_type_id'])
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'data.washing_service_id' => 'Pilih layanan yang sesuai dengan tipe kendaraan.',
            ]);
        }

        if (! $photoAfter instanceof UploadedFile || ! $photoAfter->isValid()) {
            throw ValidationException::withMessages([
                'data.photo_after_capture' => 'Ambil foto setelah dicuci menggunakan kamera sebelum menyelesaikan transaksi.',
            ]);
        }

        if ($photoAfter->getSize() > 5 * 1024 * 1024 || $photoAfter->getMimeType() !== 'image/jpeg') {
            throw ValidationException::withMessages([
                'data.photo_after_capture' => 'Foto harus berformat JPEG dan berukuran maksimal 5 MB.',
            ]);
        }

        unset(
            $data['photo_before_path'],
            $data['photo_after_path'],
            $data['photo_after_capture'],
            $data['vehicle_category'],
            $data['vehicle_type_id'],
            $data['status'],
        );
        $data['status'] = 'completed';

        $service = app(TransactionService::class);
        $transaction = $service->createTransaction($data);

        $service->savePhoto($transaction, $photoAfter, 'after');

        return $transaction;
    }

    /**
     * The wizard renders its submit action only on the final step.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [$this->getCancelFormAction()];
    }
}
