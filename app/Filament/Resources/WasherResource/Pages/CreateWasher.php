<?php

namespace App\Filament\Resources\WasherResource\Pages;

use App\Filament\Resources\WasherResource;
use App\Models\WasherCommission;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class CreateWasher extends CreateRecord
{
    protected static string $resource = WasherResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $commissionType = $data['commission_type'] ?? 'percentage';
        $commissionValue = $data['commission_value'] ?? 30;

        unset($data['commission_type'], $data['commission_value']);

        // Default random password if null
        if (empty($data['password'])) {
            $data['password'] = bcrypt(\Illuminate\Support\Str::random(16));
        }

        /** @var \App\Models\User $user */
        $user = static::getModel()::create($data);

        // Assign 'washer' role
        $washerRole = Role::firstOrCreate(['name' => 'washer']);
        $user->assignRole($washerRole);

        // Create commission setting
        WasherCommission::updateOrCreate(
            ['user_id' => $user->id],
            [
                'commission_type' => $commissionType,
                'commission_value' => $commissionValue,
            ]
        );

        return $user;
    }
}
