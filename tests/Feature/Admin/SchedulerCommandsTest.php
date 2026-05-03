<?php

namespace Tests\Feature\Admin;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchedulerCommandsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Tenant, 2: Lease}
     */
    private function setupLeaseWithPayments(
        string $ownerEmail = 'owner@test.com',
        string $tenantEmail = 'tenant@test.com'
    ): array {
        $owner = User::create([
            'name' => 'Test Owner',
            'email' => $ownerEmail,
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
            'email' => $tenantEmail,
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
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addYear(),
            'monthly_rent' => 6500,
            'deposit_amount' => 13000,
            'deposit_status' => 'held',
            'status' => 'active',
        ]);

        (new LeasePaymentService)->generatePayments($lease);

        return [$owner, $tenant, $lease];
    }

    // =====================
    // LEASE EXPIRY NOTIFICATIONS
    // =====================

    public function test_notify_expiring_leases_command_sends_notification_30_days_before_end(): void
    {
        [, $tenant, $lease] = $this->setupLeaseWithPayments();

        $lease->update(['end_date' => now()->addDays(30)->toDateString()]);

        $this->artisan('leases:notify-expiring')
            ->assertSuccessful();

        $tenantUser = $tenant->user;
        $notification = DB::table('notifications')
            ->where('user_id', $tenantUser->id)
            ->where('type', 'lease_expiring')
            ->first();

        $this->assertNotNull($notification);
    }

    public function test_notify_expiring_leases_does_not_send_for_leases_not_expiring_in_30_days(): void
    {
        [, $tenant, $lease] = $this->setupLeaseWithPayments();

        $lease->update(['end_date' => now()->addDays(60)->toDateString()]);

        $this->artisan('leases:notify-expiring')
            ->assertSuccessful();

        $tenantUser = $tenant->user;
        $notification = DB::table('notifications')
            ->where('user_id', $tenantUser->id)
            ->where('type', 'lease_expiring')
            ->first();

        $this->assertNull($notification);
    }

    public function test_notify_expiring_leases_does_not_send_duplicate_notifications_same_day(): void
    {
        [, $tenant, $lease] = $this->setupLeaseWithPayments();

        $lease->update(['end_date' => now()->addDays(30)->toDateString()]);

        $this->artisan('leases:notify-expiring')->assertSuccessful();
        $this->artisan('leases:notify-expiring')->assertSuccessful();

        $tenantUser = $tenant->user;
        $count = DB::table('notifications')
            ->where('user_id', $tenantUser->id)
            ->where('type', 'lease_expiring')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_notify_expiring_leases_only_targets_active_leases(): void
    {
        [, $tenant, $lease] = $this->setupLeaseWithPayments();

        $lease->update([
            'status' => 'completed',
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->artisan('leases:notify-expiring')
            ->assertSuccessful();

        $tenantUser = $tenant->user;
        $notification = DB::table('notifications')
            ->where('user_id', $tenantUser->id)
            ->where('type', 'lease_expiring')
            ->first();

        $this->assertNull($notification);
    }

    // =====================
    // MARK LATE PAYMENTS
    // =====================

    public function test_mark_late_command_only_processes_pending_payments(): void
    {
        [, , $lease] = $this->setupLeaseWithPayments();

        $payment = $lease->payments()->first();
        $payment->update([
            'status' => 'verifying',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->artisan('payments:mark-late')->assertSuccessful();

        $payment->refresh();
        $this->assertEquals('verifying', $payment->status);
    }

    public function test_mark_late_command_does_not_affect_paid_payments(): void
    {
        [, , $lease] = $this->setupLeaseWithPayments();

        $payment = $lease->payments()->first();
        $payment->update([
            'status' => 'paid',
            'due_date' => now()->subDays(5)->toDateString(),
            'verified_at' => now()->subDays(3),
            'total_amount_due' => 6500,
        ]);

        $this->artisan('payments:mark-late')->assertSuccessful();

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
    }
}
