<?php

namespace Tests\Feature\Tenant;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class PaymentTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_tenant_can_view_their_payments_index(): void
    {
        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $response = $this->actingAs($tenantUser)->get(route('tenant.payments.index'));

        $response->assertOk();
    }

    public function test_tenant_can_view_their_own_payment_detail(): void
    {
        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $response = $this->actingAs($tenantUser)->get(route('tenant.payments.show', $payment));

        $response->assertOk();
    }

    public function test_tenant_cannot_view_another_tenants_payment(): void
    {
        $tenantUserA = $this->createTenantUser();
        $tenantUserB = $this->createTenantUser();

        $property = $this->createProperty();
        $unitB = $this->createUnit($property->id, 'occupied');
        $leaseB = $this->createLease($tenantUserB->tenant->id, $unitB->id, 'active');
        $paymentB = $this->createPayment($leaseB->id, 'pending');

        $response = $this->actingAs($tenantUserA)->get(route('tenant.payments.show', $paymentB));

        $response->assertForbidden();
    }

    public function test_tenant_can_submit_proof_of_payment_with_valid_image_file(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => $file,
            ]);

        $response->assertRedirect(route('tenant.payments.show', $payment));

        $payment->refresh();
        $this->assertNotNull($payment->proof_of_payment);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
    }

    public function test_submitting_proof_sets_status_to_pending_and_payment_date_to_today(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'late', [
            'payment_date' => null,
        ]);

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('receipt.png'),
            ]);

        $payment->refresh();
        $this->assertSame('pending', $payment->status);
        $this->assertSame(now()->toDateString(), $payment->payment_date?->toDateString());
    }

    public function test_tenant_cannot_submit_proof_without_a_file(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $response = $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), []);

        $response->assertSessionHasErrors('proof');
    }

    public function test_tenant_cannot_submit_proof_with_invalid_file_type(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $malicious = UploadedFile::fake()->create('installer.exe', 12, 'application/x-msdownload');

        $response = $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => $malicious,
            ]);

        $response->assertSessionHasErrors('proof');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }
}
