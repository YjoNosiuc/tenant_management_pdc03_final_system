<?php

namespace Tests\Feature\Admin;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class PaymentTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_payments_index_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.payments.index'));

        $response->assertOk();
    }

    public function test_admin_can_view_payment_detail_page(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $response = $this->actingAs($admin)->get(route('admin.payments.show', $payment));

        $response->assertOk();
    }

    public function test_admin_can_verify_a_verifying_payment(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying', [
            'proof_of_payment' => 'payments/proofs/x.jpg',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.verify', $payment));

        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('success', 'Payment verified successfully.');

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->verified_at);
    }

    public function test_admin_can_verify_a_verifying_late_payment(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying_late', [
            'proof_of_payment' => 'payments/proofs/late.jpg',
            'payment_date' => now()->toDateString(),
            'late_fee_amount' => 400,
            'total_amount_due' => 18900,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.verify', $payment))
            ->assertSessionHas('success', 'Payment verified successfully.');

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->verified_at);
    }

    public function test_admin_cannot_verify_a_pending_payment(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $response = $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.verify', $payment));

        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('error', 'Only payments with submitted proof can be verified.');

        $payment->refresh();
        $this->assertSame('pending', $payment->status);
        $this->assertNull($payment->verified_at);
    }

    public function test_admin_can_reject_a_verifying_payment_with_remarks(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying', [
            'proof_of_payment' => 'payments/proofs/x.jpg',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.reject', $payment), [
                'remarks' => 'Proof image was unreadable. Please resubmit a clearer receipt.',
            ]);

        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('success', 'Payment rejected. Tenant can resubmit.');

        $payment->refresh();
        $this->assertSame('rejected', $payment->status);
        $this->assertSame('Proof image was unreadable. Please resubmit a clearer receipt.', $payment->remarks);
        $this->assertNull($payment->verified_at);
    }

    public function test_admin_can_reject_a_verifying_late_payment_with_remarks(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying_late', [
            'proof_of_payment' => 'payments/proofs/late-proof.jpg',
            'payment_date' => now()->toDateString(),
            'late_fee_amount' => 200,
            'total_amount_due' => 18700,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.reject', $payment), [
                'remarks' => 'Amount on receipt does not match total due including late fee.',
            ])
            ->assertSessionHas('success', 'Payment rejected. Tenant can resubmit.');

        $payment->refresh();
        $this->assertSame('rejected', $payment->status);
        $this->assertSame('Amount on receipt does not match total due including late fee.', $payment->remarks);
    }

    public function test_admin_cannot_verify_a_late_payment_without_proof(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'late', [
            'due_date' => now()->subDay()->toDateString(),
            'late_fee_amount' => 500,
            'total_amount_due' => 19000,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.verify', $payment))
            ->assertSessionHas('error', 'Only payments with submitted proof can be verified.');

        $payment->refresh();
        $this->assertSame('late', $payment->status);
    }

    public function test_admin_payments_index_includes_verifying_late_count(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $this->createPayment($lease->id, 'verifying_late', [
            'proof_of_payment' => 'payments/proofs/a.jpg',
            'payment_date' => now()->toDateString(),
        ]);
        $this->createPayment($lease->id, 'verifying_late', [
            'proof_of_payment' => 'payments/proofs/b.jpg',
            'payment_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payments.index'));

        $response->assertOk();
        $response->assertViewHas('verifyingLateCount', 2);
    }

    public function test_admin_cannot_reject_a_payment_without_providing_remarks(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying', [
            'proof_of_payment' => 'payments/proofs/x.jpg',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.reject', $payment), []);

        $response->assertSessionHasErrors('remarks');
    }

    public function test_admin_cannot_reject_a_pending_payment(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $response = $this->actingAs($admin)
            ->from(route('admin.payments.show', $payment))
            ->patch(route('admin.payments.reject', $payment), [
                'remarks' => 'Some reason',
            ]);

        $response->assertSessionHas('error', 'Only payments with submitted proof can be rejected.');
        $payment->refresh();
        $this->assertSame('pending', $payment->status);
    }

    public function test_pending_payment_past_due_date_is_marked_late_via_command(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 10,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending', [
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('payments:mark-late')->assertSuccessful();

        $payment->refresh();
        $this->assertSame('late', $payment->status);
    }

    public function test_mark_late_calculates_percentage_late_fee_correctly(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 10,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active', [
            'monthly_rent' => 10000,
        ]);
        $payment = $this->createPayment($lease->id, 'pending', [
            'amount_paid' => 10000,
            'due_date' => now()->subDays(2)->toDateString(),
            'total_amount_due' => 10000,
        ]);

        $this->artisan('payments:mark-late');

        $payment->refresh();
        $this->assertSame(1000.0, (float) $payment->late_fee_amount);
        $this->assertSame(11000.0, (float) $payment->total_amount_due);
    }

    public function test_mark_late_calculates_fixed_late_fee_correctly(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'fixed',
            'late_fee_value' => 500,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending', [
            'amount_paid' => 8000,
            'due_date' => now()->subDay()->toDateString(),
            'total_amount_due' => 8000,
        ]);

        $this->artisan('payments:mark-late');

        $payment->refresh();
        $this->assertSame(500.0, (float) $payment->late_fee_amount);
        $this->assertSame(8500.0, (float) $payment->total_amount_due);
    }

    public function test_total_amount_due_equals_amount_paid_plus_late_fee_amount(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 5,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending', [
            'amount_paid' => 20000,
            'due_date' => now()->subDay()->toDateString(),
            'total_amount_due' => 20000,
        ]);

        $this->artisan('payments:mark-late');

        $payment->refresh();
        $this->assertEquals(
            (float) $payment->amount_paid + (float) $payment->late_fee_amount,
            (float) $payment->total_amount_due
        );
    }

    public function test_owner_receives_notification_when_payment_is_marked_late(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 10,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $this->createPayment($lease->id, 'pending', [
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('payments:mark-late');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'payment_late_owner',
        ]);
    }

    public function test_tenant_receives_notification_when_payment_is_marked_late(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 10,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $this->createPayment($lease->id, 'pending', [
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('payments:mark-late');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenantUser->id,
            'type' => 'payment_late',
        ]);
    }

    public function test_property_with_zero_late_fee_marks_payment_late_with_no_fee(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $this->createProperty([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 0,
        ], $admin);
        $property = Property::where('owner_id', $admin->id)->firstOrFail();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending', [
            'amount_paid' => 12000,
            'due_date' => now()->subDay()->toDateString(),
            'total_amount_due' => 12000,
        ]);

        $this->artisan('payments:mark-late');

        $payment->refresh();
        $this->assertSame('late', $payment->status);
        $this->assertSame(0.0, (float) $payment->late_fee_amount);
        $this->assertSame(12000.0, (float) $payment->total_amount_due);
    }
}
