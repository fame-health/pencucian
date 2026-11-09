<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\WashingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WashingServiceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_cannot_access_washing_service_pages(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'washer']);

        $washer = User::factory()->create();
        $washer->assignRole('washer');

        $vehicleType = VehicleType::create([
            'name' => 'Motor',
            'category' => 'motor',
            'is_active' => true,
        ]);
        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Standard',
            'price' => 15000,
            'is_active' => true,
        ]);

        $this->actingAs($washer)
            ->get('/admin/washing-services')
            ->assertForbidden();

        $this->get('/admin/washing-services/create')
            ->assertForbidden();

        $this->get("/admin/washing-services/{$service->id}/edit")
            ->assertForbidden();
    }

    public function test_admin_can_access_washing_service_pages(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        Role::create(['name' => 'washer']);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $vehicleType = VehicleType::create([
            'name' => 'Motor',
            'category' => 'motor',
            'is_active' => true,
        ]);
        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Standard',
            'price' => 15000,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/washing-services')
            ->assertOk();

        $this->get('/admin/washing-services/create')
            ->assertOk();

        $this->get("/admin/washing-services/{$service->id}/edit")
            ->assertOk();
    }
}
