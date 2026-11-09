<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\WasherCommission;
use App\Models\WashingService;
use App\Models\WashTransaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WasherCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_service_calculates_percentage_commission_correctly(): void
    {
        $washerRole = Role::create(['name' => 'washer']);
        $washer = User::factory()->create();
        $washer->assignRole($washerRole);

        WasherCommission::create([
            'user_id' => $washer->id,
            'commission_type' => 'percentage',
            'commission_value' => 30.00,
        ]);

        $vehicleType = VehicleType::create([
            'name' => 'Motor Matic',
            'category' => 'motor',
            'is_active' => true,
        ]);

        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Standard',
            'price' => 20000,
            'is_active' => true,
        ]);

        $transactionService = app(TransactionService::class);
        $transaction = $transactionService->createTransaction([
            'washer_id' => $washer->id,
            'washing_service_id' => $service->id,
            'status' => 'in_progress',
        ]);

        $this->assertEquals(20000, $transaction->price);
        $this->assertEquals(6000, $transaction->commission_amount); // 30% of 20000
        $this->assertEquals(14000, $transaction->business_revenue); // 20000 - 6000
        $this->assertStringStartsWith('TRX-', $transaction->transaction_number);
    }

    public function test_transaction_service_calculates_fixed_commission_correctly(): void
    {
        $washerRole = Role::create(['name' => 'washer']);
        $washer = User::factory()->create();
        $washer->assignRole($washerRole);

        WasherCommission::create([
            'user_id' => $washer->id,
            'commission_type' => 'fixed',
            'commission_value' => 15000.00,
        ]);

        $vehicleType = VehicleType::create([
            'name' => 'Mobil SUV',
            'category' => 'mobil',
            'is_active' => true,
        ]);

        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Salju SUV',
            'price' => 50000,
            'is_active' => true,
        ]);

        $transactionService = app(TransactionService::class);
        $transaction = $transactionService->createTransaction([
            'washer_id' => $washer->id,
            'washing_service_id' => $service->id,
            'status' => 'in_progress',
        ]);

        $this->assertEquals(50000, $transaction->price);
        $this->assertEquals(15000, $transaction->commission_amount);
        $this->assertEquals(35000, $transaction->business_revenue);
    }

    public function test_completed_transaction_records_completion_time(): void
    {
        $washerRole = Role::create(['name' => 'washer']);
        $washer = User::factory()->create();
        $washer->assignRole($washerRole);

        $vehicleType = VehicleType::create([
            'name' => 'Motor Matic',
            'category' => 'motor',
            'is_active' => true,
        ]);

        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Standard',
            'price' => 20000,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction([
            'washer_id' => $washer->id,
            'washing_service_id' => $service->id,
            'status' => 'completed',
        ]);

        $this->assertSame('completed', $transaction->status);
        $this->assertNotNull($transaction->completed_at);
    }
}
