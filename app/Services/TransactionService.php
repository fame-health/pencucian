<?php

namespace App\Services;

use App\Models\TransactionPhoto;
use App\Models\WasherCommission;
use App\Models\WashingService;
use App\Models\WashTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new wash transaction with auto-calculated price and commission in a DB transaction.
     */
    public function createTransaction(array $data): WashTransaction
    {
        return DB::transaction(function () use ($data) {
            $service = WashingService::with('vehicleType')->findOrFail($data['washing_service_id']);
            $washerId = $data['washer_id'];

            // Price snapshot from backend DB
            $price = (float) $service->price;

            $commissionSetting = WasherCommission::where('user_id', $washerId)->first();
            $commAmount = $this->calculateCommissionAmount($price, $commissionSetting);

            $businessRevenue = max(0, $price - $commAmount);

            // Unique Transaction Number: TRX-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $todayStart = today()->startOfDay();
            $todayCount = WashTransaction::query()
                ->where('created_at', '>=', $todayStart)
                ->where('created_at', '<', $todayStart->copy()->addDay())
                ->count() + 1;
            $transactionNumber = 'TRX-'.$datePrefix.'-'.str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            /** @var WashTransaction $transaction */
            $transaction = WashTransaction::create([
                'transaction_number' => $transactionNumber,
                'washer_id' => $washerId,
                'vehicle_type_id' => $service->vehicle_type_id,
                'washing_service_id' => $service->id,
                'price' => $price,
                'commission_amount' => $commAmount,
                'business_revenue' => $businessRevenue,
                'status' => $data['status'] ?? 'in_progress',
                'started_at' => now(),
                'completed_at' => ($data['status'] ?? 'in_progress') === 'completed' ? now() : null,
            ]);

            // Handle photo uploads if passed directly
            if (! empty($data['photo_before'])) {
                $this->savePhoto($transaction, $data['photo_before'], 'before');
            }

            if (! empty($data['photo_after'])) {
                $this->savePhoto($transaction, $data['photo_after'], 'after');
            }

            return $transaction;
        });
    }

    public function calculateCommissionAmount(float $price, ?WasherCommission $commissionSetting): float
    {
        if (! $commissionSetting) {
            return ($price * 30) / 100;
        }

        if ($commissionSetting->commission_type === 'percentage') {
            return ($price * (float) $commissionSetting->commission_value) / 100;
        }

        return min($price, (float) $commissionSetting->commission_value);
    }

    /**
     * Save transaction photo to storage and database.
     */
    public function savePhoto(WashTransaction $transaction, UploadedFile|string $file, string $type): TransactionPhoto
    {
        $filePath = is_string($file) ? $file : $file->store('wash-photos/'.$type, 'public');

        return TransactionPhoto::updateOrCreate(
            [
                'wash_transaction_id' => $transaction->id,
                'photo_type' => $type,
            ],
            [
                'photo_path' => $filePath,
            ]
        );
    }

    /**
     * Mark a transaction as completed.
     */
    public function completeTransaction(WashTransaction $transaction, ?UploadedFile $photoAfter = null): WashTransaction
    {
        return DB::transaction(function () use ($transaction, $photoAfter) {
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            if ($photoAfter) {
                $this->savePhoto($transaction, $photoAfter, 'after');
            }

            return $transaction;
        });
    }
}
