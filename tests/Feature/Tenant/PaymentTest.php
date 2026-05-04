<?php

namespace Tests\Feature\Tenant;

use App\Models\TenantTermAgreement;
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
        $response->assertSessionHas('success', 'Proof submitted successfully. Awaiting landlord verification.');

        $payment->refresh();
        $this->assertNotNull($payment->proof_of_payment);
        $this->assertSame('verifying', $payment->status);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
    }

    public function test_submitting_proof_for_late_payment_sets_status_to_verifying_late(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'late', [
            'payment_date' => null,
            'late_fee_amount' => 500,
            'total_amount_due' => 19000,
        ]);

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('receipt.png'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying_late', $payment->status);
        $this->assertSame(now()->toDateString(), $payment->payment_date?->toDateString());
    }

    public function test_submitting_proof_for_pending_payment_sets_status_to_verifying(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'pending');

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('receipt.png'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying', $payment->status);
    }

    public function test_tenant_cannot_submit_proof_for_non_earliest_unpaid_payment(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $this->createPayment($lease->id, 'pending', [
            'due_date' => now()->subMonth()->toDateString(),
        ]);
        $laterPayment = $this->createPayment($lease->id, 'pending', [
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $laterPayment))
            ->post(route('tenant.payments.submitProof', $laterPayment), [
                'proof' => UploadedFile::fake()->image('proof.jpg'),
            ]);

        $response->assertRedirect(route('tenant.payments.show', $laterPayment));
        $response->assertSessionHas('error', 'You must pay in order. Please submit proof for the earliest pending payment first.');

        $laterPayment->refresh();
        $this->assertSame('pending', $laterPayment->status);
        $this->assertNull($laterPayment->proof_of_payment);
    }

    public function test_tenant_can_resubmit_proof_when_status_is_verifying(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying', [
            'proof_of_payment' => 'payments/proofs/old.jpg',
            'payment_date' => now()->toDateString(),
        ]);
        Storage::disk('public')->put($payment->proof_of_payment, 'fake');

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('new-proof.jpg'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying', $payment->status);
        $this->assertStringContainsString('payments/proofs/', $payment->proof_of_payment);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
        Storage::disk('public')->assertMissing('payments/proofs/old.jpg');
    }

    public function test_tenant_can_resubmit_proof_when_status_is_verifying_late(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'verifying_late', [
            'proof_of_payment' => 'payments/proofs/old-late.jpg',
            'payment_date' => now()->toDateString(),
            'late_fee_amount' => 300,
            'total_amount_due' => 18800,
        ]);
        Storage::disk('public')->put($payment->proof_of_payment, 'fake');

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('new-late-proof.jpg'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying_late', $payment->status);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
    }

    public function test_tenant_can_resubmit_proof_when_status_is_rejected(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'rejected', [
            'proof_of_payment' => 'payments/proofs/rejected.jpg',
            'payment_date' => now()->subDay()->toDateString(),
            'remarks' => 'Unclear image',
        ]);
        Storage::disk('public')->put($payment->proof_of_payment, 'fake');

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('retry.jpg'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying', $payment->status);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
    }

    public function test_resubmitting_proof_after_rejected_with_late_fee_sets_verifying_late(): void
    {
        Storage::fake('public');

        $tenantUser = $this->createTenantUser();
        $property = $this->createProperty();
        $unit = $this->createUnit($property->id, 'occupied');
        $lease = $this->createLease($tenantUser->tenant->id, $unit->id, 'active');
        $payment = $this->createPayment($lease->id, 'rejected', [
            'proof_of_payment' => 'payments/proofs/rejected.jpg',
            'payment_date' => now()->subDay()->toDateString(),
            'remarks' => 'Unclear image',
            'late_fee_amount' => 250,
            'total_amount_due' => 18750,
        ]);
        Storage::disk('public')->put($payment->proof_of_payment, 'fake');

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $payment))
            ->post(route('tenant.payments.submitProof', $payment), [
                'proof' => UploadedFile::fake()->image('retry-late.jpg'),
            ]);

        $payment->refresh();
        $this->assertSame('verifying_late', $payment->status);
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

    public function test_tenant_without_active_lease_is_not_redirected_to_terms_page(): void
    {
        $owner = $this->createAdmin();
        $this->createOwnerTerms($owner);
        $tenantUser = $this->createTenantUser([], [], $owner);

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
    }

    public function test_tenant_with_active_lease_sees_terms_page_if_not_agreed(): void
    {
        $owner = $this->createAdmin();
        $this->createOwnerTerms($owner);
        $tenantUser = $this->createTenantUser([], [], $owner);
        $property = $this->createProperty([], $owner);
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $response = $this->actingAs($tenantUser)->get(route('terms.show'));

        $response->assertOk();
        $response->assertSeeText('Terms & Conditions');
    }

    public function test_tenant_cannot_access_dashboard_without_agreeing_to_terms(): void
    {
        $owner = $this->createAdmin();
        $this->createOwnerTerms($owner);
        $tenantUser = $this->createTenantUser([], [], $owner);
        $property = $this->createProperty([], $owner);
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertRedirect(route('terms.show'));
    }

    public function test_tenant_can_agree_to_terms_and_access_dashboard(): void
    {
        $owner = $this->createAdmin();
        $terms = $this->createOwnerTerms($owner);
        $tenantUser = $this->createTenantUser([], [], $owner);
        $property = $this->createProperty([], $owner);
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $this->actingAs($tenantUser)
            ->post(route('terms.agree'), ['agreed' => '1'])
            ->assertRedirect(route('tenant.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_term_agreements', [
            'tenant_id' => $tenantUser->tenant->id,
            'owner_id' => $owner->id,
            'version' => $terms->version,
        ]);

        $this->actingAs($tenantUser)->get(route('tenant.dashboard'))->assertOk();
    }

    public function test_when_owner_updates_terms_version_tenant_must_re_agree(): void
    {
        $owner = $this->createAdmin();
        $terms = $this->createOwnerTerms($owner);
        $tenantUser = $this->createTenantUser([], [], $owner);
        $property = $this->createProperty([], $owner);
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        TenantTermAgreement::create([
            'tenant_id' => $tenantUser->tenant->id,
            'owner_id' => $owner->id,
            'version' => $terms->version,
            'agreed_at' => now()->subDay(),
        ]);

        $terms->update([
            'content' => $terms->content.' Additional clause for version bump.',
            'version' => $terms->version + 1,
        ]);

        $this->actingAs($tenantUser)->get(route('tenant.dashboard'))->assertRedirect(route('terms.show'));
    }

    public function test_tenant_with_multiple_active_leases_sees_all_units_on_dashboard(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit1 = $this->createUnit($property->id, 'occupied', ['unit_number' => '101']);
        $unit2 = $this->createUnit($property->id, 'occupied', ['unit_number' => '202']);
        $this->createLease($tenantUser->tenant->id, $unit1->id, 'active');
        $this->createLease($tenantUser->tenant->id, $unit2->id, 'active');

        $response = $this->actingAs($tenantUser)->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('Unit 101', false);
        $response->assertSee('Unit 202', false);
    }

    public function test_tenant_payments_index_groups_headers_per_active_lease(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit1 = $this->createUnit($property->id, 'occupied', ['unit_number' => 'A-1']);
        $unit2 = $this->createUnit($property->id, 'occupied', ['unit_number' => 'B-2']);
        $lease1 = $this->createLease($tenantUser->tenant->id, $unit1->id, 'active');
        $lease2 = $this->createLease($tenantUser->tenant->id, $unit2->id, 'active');
        $this->createPayment($lease1->id, 'paid', ['due_date' => now()->subMonth()->toDateString()]);
        $this->createPayment($lease2->id, 'pending', ['due_date' => now()->toDateString()]);

        $response = $this->actingAs($tenantUser)->get(route('tenant.payments.index'));

        $response->assertOk();
        $response->assertSee('Unit A-1', false);
        $response->assertSee('Unit B-2', false);
    }

    public function test_payment_submission_order_is_enforced_per_lease_independently(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit1 = $this->createUnit($property->id, 'occupied', ['unit_number' => 'X1']);
        $unit2 = $this->createUnit($property->id, 'occupied', ['unit_number' => 'X2']);
        $lease1 = $this->createLease($tenantUser->tenant->id, $unit1->id, 'active');
        $lease2 = $this->createLease($tenantUser->tenant->id, $unit2->id, 'active');

        $this->createPayment($lease1->id, 'pending', [
            'due_date' => now()->subMonths(2)->toDateString(),
        ]);
        $paymentLease2Earliest = $this->createPayment($lease2->id, 'pending', [
            'due_date' => now()->subMonth()->toDateString(),
        ]);

        $this->actingAs($tenantUser)
            ->from(route('tenant.payments.show', $paymentLease2Earliest))
            ->post(route('tenant.payments.submitProof', $paymentLease2Earliest), [
                'proof' => UploadedFile::fake()->image('lease2-proof.jpg'),
            ]);

        $paymentLease2Earliest->refresh();
        $this->assertSame('verifying', $paymentLease2Earliest->status);
        $this->assertNotNull($paymentLease2Earliest->proof_of_payment);
    }
}
