<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Lease}
     */
    private function setupOwnerAndLease(): array
    {
        $owner = User::create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $property = Property::create([
            'owner_id' => $owner->id,
            'name' => 'Test Property',
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Sto. Domingo',
            'address_line1' => '123 Test Street',
            'description' => 'Test',
            'late_fee_type' => 'percentage',
            'late_fee_value' => 5,
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'unit_type' => 'Studio',
            'rent_price' => 6500,
            'status' => 'vacant',
        ]);

        $tenantUser = User::create([
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);

        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'owner_id' => $owner->id,
            'phone_number' => '09171234567',
        ]);

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 6500,
            'deposit_amount' => 13000,
            'deposit_status' => 'held',
            'status' => 'active',
        ]);

        (new LeasePaymentService)->generatePayments($lease);

        return [$owner, $lease];
    }

    public function test_owner_can_upload_contract_to_lease(): void
    {
        Storage::fake('public');
        [$owner, $lease] = $this->setupOwnerAndLease();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.show', $lease))
            ->post(route('admin.leases.contract.upload', $lease), [
                'contract' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $lease->refresh();
        $this->assertNotNull($lease->contract_path);
        $this->assertNotNull($lease->contract_uploaded_at);
    }

    public function test_owner_can_replace_existing_contract(): void
    {
        Storage::fake('public');
        [$owner, $lease] = $this->setupOwnerAndLease();

        $this->actingAs($owner)
            ->from(route('admin.leases.show', $lease))
            ->post(route('admin.leases.contract.upload', $lease), [
                'contract' => UploadedFile::fake()->create('contract1.pdf', 100, 'application/pdf'),
            ]);

        $lease->refresh();
        $firstPath = $lease->contract_path;

        $this->actingAs($owner)
            ->from(route('admin.leases.show', $lease))
            ->post(route('admin.leases.contract.upload', $lease), [
                'contract' => UploadedFile::fake()->create('contract2.pdf', 100, 'application/pdf'),
            ]);

        $lease->refresh();
        $this->assertNotNull($lease->contract_path);
        $this->assertNotSame($firstPath, $lease->contract_path);
    }

    public function test_owner_cannot_upload_contract_to_another_owners_lease(): void
    {
        Storage::fake('public');
        [, $lease] = $this->setupOwnerAndLease();

        $owner2 = User::create([
            'name' => 'Owner 2',
            'email' => 'owner2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($owner2)
            ->post(route('admin.leases.contract.upload', $lease), [
                'contract' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ]);

        $response->assertStatus(403);

        $lease->refresh();
        $this->assertNull($lease->contract_path);
    }

    public function test_contract_upload_requires_a_file(): void
    {
        Storage::fake('public');
        [$owner, $lease] = $this->setupOwnerAndLease();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.show', $lease))
            ->post(route('admin.leases.contract.upload', $lease), []);

        $response->assertSessionHasErrors('contract');
    }

    public function test_contract_upload_accepts_image_files(): void
    {
        Storage::fake('public');
        [$owner, $lease] = $this->setupOwnerAndLease();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.show', $lease))
            ->post(route('admin.leases.contract.upload', $lease), [
                'contract' => UploadedFile::fake()->image('contract_photo.jpg'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $lease->refresh();
        $this->assertNotNull($lease->contract_path);
    }

    public function test_tenant_can_view_contract_on_their_dashboard(): void
    {
        Storage::fake('public');
        [, $lease] = $this->setupOwnerAndLease();

        $lease->update([
            'contract_path' => 'contracts/test.pdf',
            'contract_uploaded_at' => now(),
        ]);

        $tenantUser = $lease->tenant->user;

        $response = $this->actingAs($tenantUser)
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }
}
