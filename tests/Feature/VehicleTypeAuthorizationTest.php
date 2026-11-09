<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VehicleTypeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_cannot_access_vehicle_type_pages(): void
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

        $this->actingAs($washer)
            ->get('/admin/vehicle-types')
            ->assertForbidden();

        $this->get('/admin/vehicle-types/create')
            ->assertForbidden();

        $this->get("/admin/vehicle-types/{$vehicleType->id}/edit")
            ->assertForbidden();
    }

    public function test_admin_can_access_vehicle_type_pages(): void
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

        $this->actingAs($admin)
            ->get('/admin/vehicle-types')
            ->assertOk();

        $this->get('/admin/vehicle-types/create')
            ->assertOk();

        $this->get("/admin/vehicle-types/{$vehicleType->id}/edit")
            ->assertOk();
    }
}
