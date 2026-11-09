<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\WashingService;
use App\Models\WasherCommission;
use App\Models\WashTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $washerRole = Role::firstOrCreate(['name' => 'washer']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);

        // 2. Create Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@pencucian.com'],
            [
                'name' => 'Pemilik / Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '08111111111',
            ]
        );
        $admin->assignRole($adminRole);

        // 3. Create Washer Accounts
        $washer1 = User::firstOrCreate(
            ['email' => 'budi@pencucian.com'],
            [
                'name' => 'Budi Pencuci',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '081234567890',
            ]
        );
        $washer1->assignRole($washerRole);

        WasherCommission::firstOrCreate(
            ['user_id' => $washer1->id],
            [
                'commission_type' => 'percentage',
                'commission_value' => 30.00,
            ]
        );

        $washer2 = User::firstOrCreate(
            ['email' => 'andi@pencucian.com'],
            [
                'name' => 'Andi Pencuci',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '081298765432',
            ]
        );
        $washer2->assignRole($washerRole);

        WasherCommission::firstOrCreate(
            ['user_id' => $washer2->id],
            [
                'commission_type' => 'percentage',
                'commission_value' => 25.00,
            ]
        );

        // 4. Create Vehicle Types
        $vtMotorMatic = VehicleType::firstOrCreate(
            ['name' => 'Motor Matic / Bebek'],
            ['category' => 'motor', 'is_active' => true]
        );
        $vtMotorGede = VehicleType::firstOrCreate(
            ['name' => 'Motor Sport / Big Bike'],
            ['category' => 'motor', 'is_active' => true]
        );
        $vtMobilSmall = VehicleType::firstOrCreate(
            ['name' => 'Mobil Small / Hatchback'],
            ['category' => 'mobil', 'is_active' => true]
        );
        $vtMobilSUV = VehicleType::firstOrCreate(
            ['name' => 'Mobil SUV / MPV / Large'],
            ['category' => 'mobil', 'is_active' => true]
        );

        // 5. Create Washing Services
        WashingService::firstOrCreate(
            ['name' => 'Cuci Standard Motor', 'vehicle_type_id' => $vtMotorMatic->id],
            ['description' => 'Pencucian bodi luar dan pengeringan motor matic.', 'price' => 15000, 'is_active' => true]
        );
        WashingService::firstOrCreate(
            ['name' => 'Cuci Detail Motor Gede', 'vehicle_type_id' => $vtMotorGede->id],
            ['description' => 'Pencucian bodi, rantai, dan pengilap ban motor sport.', 'price' => 30000, 'is_active' => true]
        );
        WashingService::firstOrCreate(
            ['name' => 'Cuci Express Mobil', 'vehicle_type_id' => $vtMobilSmall->id],
            ['description' => 'Cuci salju bodi luar dan vacum interior dasar.', 'price' => 45000, 'is_active' => true]
        );
        WashingService::firstOrCreate(
            ['name' => 'Cuci Salju & Premium Wax SUV', 'vehicle_type_id' => $vtMobilSUV->id],
            ['description' => 'Cuci salju hidrolik, vacum interior total, dan pemolesan wax bodi.', 'price' => 70000, 'is_active' => true]
        );

        // 6. Create Initial Sample Transactions for Today
        $service1 = WashingService::where('vehicle_type_id', $vtMobilSmall->id)->first();
        if ($service1) {
            $price = $service1->price;
            $commPercent = 30;
            $commAmount = ($price * $commPercent) / 100;
            $revenue = $price - $commAmount;

            WashTransaction::firstOrCreate(
                ['transaction_number' => 'TRX-' . date('Ymd') . '-0001'],
                [
                    'washer_id' => $washer1->id,
                    'vehicle_type_id' => $vtMobilSmall->id,
                    'washing_service_id' => $service1->id,
                    'price' => $price,
                    'commission_amount' => $commAmount,
                    'business_revenue' => $revenue,
                    'status' => 'completed',
                    'started_at' => now()->subHours(2),
                    'completed_at' => now()->subHour(),
                ]
            );
        }
    }
}
