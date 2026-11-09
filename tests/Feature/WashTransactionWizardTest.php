<?php

namespace Tests\Feature;

use App\Filament\Resources\WashTransactionResource\Pages\CreateWashTransaction;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\WasherCommission;
use App\Models\WashingService;
use App\Models\WashTransaction;
use App\Services\TransactionService;
use Filament\Http\Middleware\Authenticate;
use Filament\Schemas\Components\Wizard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WashTransactionWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_guides_the_user_through_vehicle_selection_and_camera_photo(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        Role::create(['name' => 'washer']);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $washer = User::factory()->create();
        $washer->assignRole('washer');

        $vehicleType = VehicleType::create([
            'name' => 'Matic kecil',
            'examples' => 'Beat, Supra',
            'category' => 'motor',
            'is_active' => true,
        ]);

        WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Standard',
            'description' => 'Cuci bodi dan lap kering',
            'price' => 20000,
            'is_active' => true,
        ]);

        $response = $this->withoutMiddleware(Authenticate::class)
            ->actingAs($admin)
            ->get('/admin/wash-transactions/create');

        $response
            ->assertOk()
            ->assertSee('Pilih Kendaraan')
            ->assertSee('Foto & Simpan')
            ->assertSee('Motor')
            ->assertSee('Mobil')
            ->assertSee('Matic kecil')
            ->assertSee('Contoh model: Beat, Supra')
            ->assertSee('Cuci Standard')
            ->assertSee('Cuci bodi dan lap kering')
            ->assertSee('Harga Rp 20.000')
            ->assertSee('Komisi Rp')
            ->assertSee('navigator.mediaDevices', escape: false)
            ->assertSee('cameraRequested', escape: false)
            ->assertSee('MutationObserver', escape: false)
            ->assertSee('Simpan Transaksi')
            ->assertSee('Ambil Foto')
            ->assertDontSee('<input type="file"', escape: false)
            ->assertDontSee('Tambah Pencucian')
            ->assertDontSee('Foto Sebelum Dicuci');
    }

    public function test_commission_preview_uses_the_same_calculation_as_transaction_creation(): void
    {
        $service = new TransactionService;
        $percentageCommission = new WasherCommission([
            'commission_type' => 'percentage',
            'commission_value' => 25,
        ]);
        $fixedCommission = new WasherCommission([
            'commission_type' => 'fixed',
            'commission_value' => 5000,
        ]);

        $this->assertSame(7500.0, $service->calculateCommissionAmount(30000, $percentageCommission));
        $this->assertSame(5000.0, $service->calculateCommissionAmount(30000, $fixedCommission));
        $this->assertSame(9000.0, $service->calculateCommissionAmount(30000, null));
    }

    public function test_database_session_and_cache_lock_tables_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('cache_locks'));
    }

    public function test_washer_can_save_a_transaction_with_an_uploaded_after_photo(): void
    {
        Role::create(['name' => 'washer']);

        $washer = User::factory()->create();
        $washer->assignRole('washer');

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

        $component = Livewire::actingAs($washer)
            ->test(CreateWashTransaction::class)
            ->set('data.vehicle_category', 'motor')
            ->set('data.vehicle_type_id', $vehicleType->id)
            ->assertSet('data.washing_service_id', $service->id);

        $wizard = $component->instance()->form->getComponent(
            fn ($child): bool => $child instanceof Wizard,
        );

        $component
            ->call('callSchemaComponentMethod', $wizard->getKey(), 'nextStep', [0])
            ->set('data.photo_after_capture', UploadedFile::fake()->image('after.jpg'))
            ->call('create')
            ->assertHasNoErrors();

        $transaction = WashTransaction::query()->sole();

        $this->assertSame('completed', $transaction->status);
        $this->assertSame((string) $service->price, $transaction->price);
        $this->assertDatabaseHas('transaction_photos', [
            'wash_transaction_id' => $transaction->id,
            'photo_type' => 'after',
        ]);
    }
}
