<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class LeaseTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_leases_index_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.leases.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_lease_with_valid_data(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant');

        $payload = [
            'tenant_id' => $tenantUser->tenant->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(11)->toDateString(),
            'monthly_rent' => 19500.00,
            'deposit_amount' => 39000.00,
            'deposit_status' => 'held',
            'status' => 'active',
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), $payload);

        $response->assertRedirect(route('admin.leases.index'));
        $this->assertDatabaseHas('leases', [
            'tenant_id' => $tenantUser->tenant->id,
            'unit_id' => $unit->id,
            'status' => 'active',
        ]);
    }

    public function test_creating_an_active_lease_sets_unit_status_to_occupied(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant', [
            'unit_number' => '7C',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->subWeek()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 17000.00,
                'deposit_amount' => 17000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'status' => 'occupied',
        ]);
    }

    public function test_admin_cannot_create_lease_with_end_date_before_start_date(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant');

        $response = $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->addMonths(6)->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'monthly_rent' => 16000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_admin_can_update_a_lease(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant');

        $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 16000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();

        $response = $this->actingAs($admin)
            ->from(route('admin.leases.show', $lease))
            ->patch(route('admin.leases.update', $lease), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(13)->toDateString(),
                'monthly_rent' => 16800.00,
                'deposit_amount' => 33600.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.leases.show', $lease));
        $this->assertDatabaseHas('leases', [
            'id' => $lease->id,
            'monthly_rent' => 16800.00,
        ]);
    }

    public function test_updating_lease_to_terminated_sets_unit_status_to_vacant(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant');

        $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 17500.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();
        $this->assertSame('occupied', $unit->fresh()->status);

        $this->actingAs($admin)
            ->from(route('admin.leases.show', $lease))
            ->patch(route('admin.leases.update', $lease), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => $lease->start_date->toDateString(),
                'end_date' => $lease->end_date->toDateString(),
                'monthly_rent' => 17500.00,
                'deposit_amount' => 35000.00,
                'deposit_status' => 'returned',
                'status' => 'terminated',
            ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'status' => 'vacant',
        ]);
    }

    public function test_admin_can_soft_delete_a_lease(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant');

        $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->post(route('admin.leases.store'), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 15000.00,
                'deposit_status' => 'held',
                'status' => 'completed',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();

        $response = $this->actingAs($admin)
            ->from(route('admin.leases.index'))
            ->delete(route('admin.leases.destroy', $lease));

        $response->assertRedirect(route('admin.leases.index'));
        $this->assertSoftDeleted('leases', [
            'id' => $lease->id,
        ]);
    }

    public function test_admin_can_view_lease_detail_page(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');

        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active', [
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'monthly_rent' => 18250.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.leases.show', $lease));

        $response->assertOk();
        $response->assertSee('Lease Details');
    }
}
