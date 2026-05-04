<?php

namespace Tests\Feature\Tenant;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class DashboardTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_tenant_can_view_their_dashboard(): void
    {
        $tenantUser = $this->createTenantUser();

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_shows_current_active_lease_info(): void
    {
        $tenantUser = $this->createTenantUser([
            'name' => 'Marisol Cruz',
        ]);
        $property = $this->createProperty([
            'name' => 'Emerald Residences',
        ]);
        $unit = $this->createUnit($property->id, 'occupied', [
            'unit_number' => '8D',
            'unit_type' => 'Studio',
        ]);

        $this->createLease($tenantUser->tenant->id, $unit->id, 'active', [
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'monthly_rent' => 21250.00,
        ]);

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('Emerald Residences');
        $response->assertSee('Unit 8D');
        $response->assertSee('₱21,250');
    }

    public function test_dashboard_shows_next_pending_payment(): void
    {
        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $this->createPayment($lease->id, 'pending', [
            'amount_paid' => 19999.25,
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('₱19,999.25');
        $response->assertSee('Pending');
    }

    public function test_dashboard_shows_no_active_lease_when_tenant_has_no_lease(): void
    {
        $tenantUser = $this->createTenantUser();

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('No Active Leases');
    }

    public function test_dashboard_shows_no_pending_payments_when_all_caught_up(): void
    {
        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $this->createPayment($lease->id, 'paid', [
            'due_date' => now()->subMonth()->toDateString(),
            'verified_at' => now()->subWeek(),
        ]);

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('All caught up!');
    }
}
