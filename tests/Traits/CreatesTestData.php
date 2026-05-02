<?php

namespace Tests\Traits;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesTestData
{
    private ?User $implicitTestOwner = null;

    /**
     * Default landlord for a test when no explicit owner is passed to factory helpers.
     */
    private function implicitOwner(): User
    {
        return $this->implicitTestOwner ??= $this->createAdmin();
    }

    protected function createAdmin(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Ricardo Villarin',
            'email' => 'admin.'.Str::lower(Str::random(12)).'@landlord.test',
            'password' => 'password',
            'role' => 'admin',
        ], $attributes));
    }

    /**
     * Creates a tenant-scoped user plus the related tenant profile.
     */
    protected function createTenantUser(array $userAttributes = [], array $tenantAttributes = [], ?User $owner = null): User
    {
        $owner ??= $this->implicitOwner();

        $user = User::create(array_merge([
            'name' => 'Ana Del Rosario',
            'email' => 'tenant.'.Str::lower(Str::random(12)).'@renter.test',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ], $userAttributes));

        Tenant::create(array_merge([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone_number' => '+639171234567',
            'emergency_contact_name' => 'Miguel Del Rosario',
            'emergency_contact_number' => '+639189998887',
            'address' => '123 Rizal Street, Angeles City, Pampanga',
        ], $tenantAttributes));

        return $user->fresh(['tenant']);
    }

    protected function createProperty(array $attributes = [], ?User $owner = null): Property
    {
        $owner ??= $this->implicitOwner();

        return Property::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'Sunrise Apartments',
            'address' => 'MacArthur Highway, Mabalacat, Pampanga',
            'description' => 'Mid-rise residential building with 24-hour security.',
        ], $attributes));
    }

    protected function createUnit(int $propertyId, string $status = 'vacant', array $attributes = []): Unit
    {
        return Unit::create(array_merge([
            'property_id' => $propertyId,
            'unit_number' => '12B',
            'unit_type' => 'Two-bedroom apartment',
            'rent_price' => 18500.50,
            'status' => $status,
        ], $attributes));
    }

    protected function createLease(int $tenantId, int $unitId, string $status = 'active', array $attributes = []): Lease
    {
        return Lease::create(array_merge([
            'tenant_id' => $tenantId,
            'unit_id' => $unitId,
            'start_date' => now()->subMonthsWithoutOverflow(2)->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'monthly_rent' => 18500.00,
            'deposit_amount' => 37000.00,
            'deposit_status' => 'held',
            'status' => $status,
        ], $attributes));
    }

    protected function createPayment(int $leaseId, string $status = 'pending', array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'lease_id' => $leaseId,
            'amount_paid' => 18500.00,
            'due_date' => now()->toDateString(),
            'payment_date' => null,
            'payment_method' => 'gcash',
            'proof_of_payment' => null,
            'verified_at' => null,
            'status' => $status,
            'remarks' => null,
        ], $attributes));
    }
}
