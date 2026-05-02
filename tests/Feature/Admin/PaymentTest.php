<?php

namespace Tests\Feature\Admin;

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
        $response->assertSessionHas('error', 'Only payments awaiting verification can be verified.');

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

        $response->assertSessionHas('error', 'Only payments awaiting verification can be rejected.');
        $payment->refresh();
        $this->assertSame('pending', $payment->status);
    }
}
