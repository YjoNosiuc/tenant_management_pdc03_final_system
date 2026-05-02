<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use App\Models\Payment;
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

    public function test_creating_a_lease_automatically_generates_payments_for_entire_duration(): void
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
                'start_date' => '2026-04-25',
                'end_date' => '2027-04-25',
                'monthly_rent' => 10000.00,
                'deposit_amount' => 20000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();
        $payments = Payment::query()->where('lease_id', $lease->id)->orderBy('due_date')->get();

        $this->assertCount(12, $payments);
        $this->assertSame('2026-05-25', $payments->first()->due_date->format('Y-m-d'));
        $this->assertSame('2027-04-25', $payments->last()->due_date->format('Y-m-d'));

        foreach ($payments as $payment) {
            $this->assertSame('pending', $payment->status);
            $this->assertEquals(10000.00, (float) $payment->amount_paid);
        }
    }

    public function test_updating_lease_end_date_regenerates_payments(): void
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
                'start_date' => '2026-04-25',
                'end_date' => '2027-04-25',
                'monthly_rent' => 5000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();
        $this->assertCount(12, Payment::query()->where('lease_id', $lease->id)->get());

        $this->actingAs($admin)
            ->from(route('admin.leases.show', $lease))
            ->patch(route('admin.leases.update', $lease), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => '2026-04-25',
                'end_date' => '2027-07-25',
                'monthly_rent' => 5000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease->refresh();
        $this->assertCount(15, Payment::query()->where('lease_id', $lease->id)->get());
    }

    public function test_updating_lease_does_not_delete_already_paid_payments(): void
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
                'start_date' => '2026-04-25',
                'end_date' => '2027-04-25',
                'monthly_rent' => 10000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $lease = Lease::query()->latest('id')->firstOrFail();
        $firstPayment = Payment::query()
            ->where('lease_id', $lease->id)
            ->orderBy('due_date')
            ->firstOrFail();

        $firstPayment->update([
            'status' => 'paid',
            'verified_at' => now(),
            'payment_date' => now()->toDateString(),
            'payment_method' => 'gcash',
        ]);

        $paidId = $firstPayment->id;

        $this->actingAs($admin)
            ->from(route('admin.leases.show', $lease))
            ->patch(route('admin.leases.update', $lease), [
                'tenant_id' => $tenantUser->tenant->id,
                'unit_id' => $unit->id,
                'start_date' => '2026-04-25',
                'end_date' => '2027-04-25',
                'monthly_rent' => 12000.00,
                'deposit_status' => 'held',
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $paidId,
            'lease_id' => $lease->id,
            'status' => 'paid',
        ]);

        $this->assertSame(1, Payment::query()->where('lease_id', $lease->id)->where('status', 'paid')->count());
        $this->assertSame(11, Payment::query()->where('lease_id', $lease->id)->where('status', 'pending')->count());

        $pendingAmounts = Payment::query()
            ->where('lease_id', $lease->id)
            ->where('status', 'pending')
            ->pluck('amount_paid')
            ->map(fn ($v) => (float) $v)
            ->unique()
            ->values();

        $this->assertCount(1, $pendingAmounts);
        $this->assertEquals(12000.00, $pendingAmounts->first());
    }
}
