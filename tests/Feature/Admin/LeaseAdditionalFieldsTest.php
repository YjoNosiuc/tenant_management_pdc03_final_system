<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseAdditionalFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Unit, 2: Tenant}
     */
    private function setupOwnerUnitTenant(): array
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

        return [$owner, $unit, $tenant];
    }

    public function test_creating_lease_saves_notes_and_inclusions(): void
    {
        [$owner, $unit, $tenant] = $this->setupOwnerUnitTenant();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'monthly_rent' => 6500,
                'deposit_amount' => 13000,
                'deposit_status' => 'held',
                'status' => 'active',
                'notes' => 'Tenant allowed to have one pet.',
                'inclusions' => ['Water', 'Electricity', 'Internet/WiFi'],
            ]);

        $response->assertRedirect(route('admin.leases.index'));

        $lease = Lease::first();
        $this->assertEquals('Tenant allowed to have one pet.', $lease->notes);
        $this->assertContains('Water', $lease->inclusions);
        $this->assertContains('Electricity', $lease->inclusions);
        $this->assertContains('Internet/WiFi', $lease->inclusions);
    }

    public function test_lease_can_be_created_without_notes_and_inclusions(): void
    {
        [$owner, $unit, $tenant] = $this->setupOwnerUnitTenant();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'monthly_rent' => 6500,
                'deposit_amount' => 13000,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.leases.index'));
        $response->assertSessionHasNoErrors();

        $lease = Lease::first();
        $this->assertNull($lease->notes);
        $this->assertIsArray($lease->inclusions);
        $this->assertCount(0, $lease->inclusions);
    }

    public function test_lease_inclusions_are_stored_as_array(): void
    {
        [$owner, $unit, $tenant] = $this->setupOwnerUnitTenant();

        $this->actingAs($owner)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'monthly_rent' => 6500,
                'deposit_amount' => 13000,
                'deposit_status' => 'held',
                'status' => 'active',
                'inclusions' => ['Water', 'Parking'],
            ]);

        $lease = Lease::first();
        $this->assertIsArray($lease->inclusions);
        $this->assertCount(2, $lease->inclusions);
    }

    public function test_invalid_inclusion_is_rejected_by_validation(): void
    {
        [$owner, $unit, $tenant] = $this->setupOwnerUnitTenant();

        $response = $this->actingAs($owner)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'monthly_rent' => 6500,
                'deposit_amount' => 13000,
                'deposit_status' => 'held',
                'status' => 'active',
                'inclusions' => ['InvalidInclusion'],
            ]);

        $response->assertSessionHasErrors('inclusions.0');
    }

    public function test_inclusions_show_on_tenant_dashboard(): void
    {
        [$owner, $unit, $tenant] = $this->setupOwnerUnitTenant();

        $lease = Lease::create([
            'tenant_id' => $tenant->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 6500,
            'deposit_amount' => 13000,
            'deposit_status' => 'held',
            'status' => 'active',
            'inclusions' => ['Water', 'Electricity'],
            'notes' => 'Special agreement note.',
        ]);

        (new LeasePaymentService)->generatePayments($lease);

        $tenantUser = $tenant->user;

        $response = $this->actingAs($tenantUser)
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Water');
        $response->assertSee('Electricity');
        $response->assertSee('Special agreement note.');
    }
}
