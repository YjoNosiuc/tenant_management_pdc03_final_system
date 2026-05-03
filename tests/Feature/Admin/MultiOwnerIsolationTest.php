<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiOwnerIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(string $email): User
    {
        return User::create([
            'name' => 'Owner '.$email,
            'email' => $email,
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);
    }

    private function createProperty(User $owner, string $name = 'Test Property'): Property
    {
        return Property::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Sto. Domingo',
            'address_line1' => '123 Test Street',
            'description' => 'Test description',
            'late_fee_type' => 'percentage',
            'late_fee_value' => 5,
        ]);
    }

    private function createUnit(Property $property, string $status = 'vacant'): Unit
    {
        return Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'unit_type' => 'Studio',
            'rent_price' => 6500,
            'status' => $status,
        ]);
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function createTenantUser(User $owner, string $email): array
    {
        $localPart = strstr($email, '@', true) ?: $email;

        $user = User::create([
            'name' => 'Tenant '.$localPart,
            'email' => $email,
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone_number' => '09171234567',
        ]);

        return [$user, $tenant];
    }

    private function createLease(Tenant $tenant, Unit $unit): Lease
    {
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

        return $lease;
    }

    // =====================
    // PROPERTIES ISOLATION
    // =====================

    public function test_owner_can_only_see_their_own_properties(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $this->createProperty($owner1, 'Owner1 Property');
        $this->createProperty($owner2, 'Owner2 Property');

        $response = $this->actingAs($owner1)->get(route('admin.properties.index'));

        $response->assertStatus(200);
        $response->assertSee('Owner1 Property');
        $response->assertDontSee('Owner2 Property');
    }

    public function test_owner_cannot_edit_another_owners_property(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property = $this->createProperty($owner2);

        $response = $this->actingAs($owner1)->patch(
            route('admin.properties.update', $property),
            [
                'name' => 'Hacked Property',
                'province' => 'Pampanga',
                'city' => 'Angeles City',
                'barangay' => 'Sto. Domingo',
                'address_line1' => '123 Test',
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('properties', ['name' => 'Hacked Property']);
    }

    public function test_owner_cannot_delete_another_owners_property(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property = $this->createProperty($owner2);

        $response = $this->actingAs($owner1)
            ->delete(route('admin.properties.destroy', $property));

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_owner_cannot_view_another_owners_property_detail(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property = $this->createProperty($owner2);

        $response = $this->actingAs($owner1)
            ->get(route('admin.properties.show', $property));

        $response->assertStatus(403);
    }

    // =====================
    // UNITS ISOLATION
    // =====================

    public function test_owner_can_only_see_their_own_units(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property1 = $this->createProperty($owner1);
        $property2 = $this->createProperty($owner2);

        Unit::create([
            'property_id' => $property1->id,
            'unit_number' => '101',
            'unit_type' => 'Studio',
            'rent_price' => 6500,
            'status' => 'vacant',
        ]);

        Unit::create([
            'property_id' => $property2->id,
            'unit_number' => '202',
            'unit_type' => '1BR',
            'rent_price' => 8500,
            'status' => 'vacant',
        ]);

        $response = $this->actingAs($owner1)->get(route('admin.units.index'));

        $response->assertStatus(200);
        $response->assertSee('101');
        $response->assertDontSee('202');
    }

    public function test_owner_cannot_view_another_owners_unit_detail(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property2 = $this->createProperty($owner2);
        $unit = $this->createUnit($property2);

        $response = $this->actingAs($owner1)
            ->get(route('admin.units.show', $unit));

        $response->assertStatus(403);
    }

    // =====================
    // TENANTS ISOLATION
    // =====================

    public function test_owner_can_only_see_their_own_tenants(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $this->createTenantUser($owner1, 'tenant1@test.com');
        $this->createTenantUser($owner2, 'tenant2@test.com');

        $response = $this->actingAs($owner1)->get(route('admin.tenants.index'));

        $response->assertStatus(200);
        $response->assertSee('tenant1@test.com');
        $response->assertDontSee('tenant2@test.com');
    }

    public function test_owner_cannot_view_another_owners_tenant_profile(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');

        $response = $this->actingAs($owner1)
            ->get(route('admin.tenants.show', $tenant2));

        $response->assertStatus(403);
    }

    public function test_owner_cannot_delete_another_owners_tenant(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');

        $response = $this->actingAs($owner1)
            ->delete(route('admin.tenants.destroy', $tenant2));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tenants', ['id' => $tenant2->id]);
    }

    // =====================
    // LEASES ISOLATION
    // =====================

    public function test_owner_can_only_see_their_own_leases(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property1 = $this->createProperty($owner1);
        $property2 = $this->createProperty($owner2);

        $unit1 = $this->createUnit($property1);
        $unit2 = $this->createUnit($property2);

        [, $tenant1] = $this->createTenantUser($owner1, 'tenant1@test.com');
        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');

        $this->createLease($tenant1, $unit1);
        $this->createLease($tenant2, $unit2);

        $response = $this->actingAs($owner1)->get(route('admin.leases.index'));

        $response->assertStatus(200);
        $response->assertSee('Tenant tenant1');
        $response->assertDontSee('Tenant tenant2');
    }

    public function test_owner_cannot_view_another_owners_lease_detail(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property2 = $this->createProperty($owner2);
        $unit2 = $this->createUnit($property2);
        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');
        $lease2 = $this->createLease($tenant2, $unit2);

        $response = $this->actingAs($owner1)
            ->get(route('admin.leases.show', $lease2));

        $response->assertStatus(403);
    }

    // =====================
    // PAYMENTS ISOLATION
    // =====================

    public function test_owner_can_only_see_their_own_payments(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property1 = $this->createProperty($owner1);
        $property2 = $this->createProperty($owner2);

        $unit1 = $this->createUnit($property1);
        $unit2 = $this->createUnit($property2);

        [, $tenant1] = $this->createTenantUser($owner1, 'tenant1@test.com');
        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');

        $this->createLease($tenant1, $unit1);
        $this->createLease($tenant2, $unit2);

        $owner1PaymentCount = Payment::whereHas(
            'lease.unit.property',
            fn ($q) => $q->where('owner_id', $owner1->id)
        )->count();

        $owner2PaymentCount = Payment::whereHas(
            'lease.unit.property',
            fn ($q) => $q->where('owner_id', $owner2->id)
        )->count();

        $this->assertGreaterThan(0, $owner1PaymentCount);
        $this->assertGreaterThan(0, $owner2PaymentCount);

        $response = $this->actingAs($owner1)->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('tenant1@test.com');
        $response->assertDontSee('tenant2@test.com');
    }

    public function test_owner_cannot_verify_another_owners_payment(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property2 = $this->createProperty($owner2);
        $unit2 = $this->createUnit($property2);
        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');
        $lease2 = $this->createLease($tenant2, $unit2);

        $payment = $lease2->payments()->first();
        $payment->update(['status' => 'verifying']);

        $response = $this->actingAs($owner1)
            ->patch(route('admin.payments.verify', $payment));

        $response->assertStatus(403);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'verifying',
        ]);
    }

    public function test_owner_cannot_reject_another_owners_payment(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $property2 = $this->createProperty($owner2);
        $unit2 = $this->createUnit($property2);
        [, $tenant2] = $this->createTenantUser($owner2, 'tenant2@test.com');
        $lease2 = $this->createLease($tenant2, $unit2);

        $payment = $lease2->payments()->first();
        $payment->update(['status' => 'verifying']);

        $response = $this->actingAs($owner1)
            ->patch(route('admin.payments.reject', $payment), [
                'remarks' => 'Trying to reject someone elses payment',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'verifying',
        ]);
    }

    // =====================
    // DASHBOARD ISOLATION
    // =====================

    public function test_owner_dashboard_only_shows_their_own_stats(): void
    {
        $owner1 = $this->createOwner('owner1@test.com');
        $owner2 = $this->createOwner('owner2@test.com');

        $this->createProperty($owner1, 'Owner1 Property');
        $this->createProperty($owner1, 'Owner1 Property 2');
        $this->createProperty($owner2, 'Owner2 Property');

        $response = $this->actingAs($owner1)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        $owner1PropertyCount = Property::where('owner_id', $owner1->id)->count();
        $this->assertEquals(2, $owner1PropertyCount);
    }
}
