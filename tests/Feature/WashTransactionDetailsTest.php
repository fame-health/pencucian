<?php

namespace Tests\Feature;

use App\Filament\Resources\WashTransactionResource;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\WashingService;
use App\Models\WashTransaction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WashTransactionDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_details_include_vehicle_service_financial_and_photo_information(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'washer']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $washer = User::factory()->create();
        $washer->assignRole('washer');

        $vehicleType = VehicleType::create([
            'name' => 'Matic kecil',
            'examples' => 'Beat, Supra',
            'category' => 'motor',
            'is_active' => true,
        ]);

        $service = WashingService::create([
            'vehicle_type_id' => $vehicleType->id,
            'name' => 'Cuci Reguler',
            'description' => 'Cuci bodi dan lap kering',
            'price' => 25000,
            'is_active' => true,
        ]);

        $transaction = WashTransaction::create([
            'transaction_number' => 'WP-TEST-0001',
            'washer_id' => $washer->id,
            'vehicle_type_id' => $vehicleType->id,
            'washing_service_id' => $service->id,
            'price' => 25000,
            'commission_amount' => 7500,
            'business_revenue' => 17500,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $livewire = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;
        };

        $schema = WashTransactionResource::infolist(Schema::make($livewire));
        $components = $schema->getComponents();
        $entries = [];

        foreach ($components as $component) {
            foreach ($component->getChildSchema()->getComponents() as $entry) {
                $entries[$entry->getName()] = $entry;
            }
        }

        $this->assertSame('Nomor transaksi', $entries['transaction_number']->getLabel());
        $this->assertSame('Contoh model', $entries['vehicleType.examples']->getLabel());
        $this->assertSame('Total pembayaran', $entries['price']->getLabel());
        $this->assertSame('Pendapatan usaha', $entries['business_revenue']->getLabel());
        $this->assertInstanceOf(ImageEntry::class, $entries['photoBefore.photo_path']);
        $this->assertInstanceOf(ImageEntry::class, $entries['photoAfter.photo_path']);
        $this->assertSame('WP-TEST-0001', $transaction->transaction_number);
    }
}
