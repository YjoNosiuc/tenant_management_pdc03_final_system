<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class TenantTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_tenants_index_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.tenants.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_tenant_creating_both_user_and_tenant_profile(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Josefa Mercado',
            'email' => 'josefa.mercado@renter.test',
            'password' => 'SecurePass1',
            'phone_number' => '+639998887766',
            'emergency_contact_name' => 'Ramon Mercado',
            'emergency_contact_number' => '+639887766554',
            'address' => '45 Sto. Rosario Street, Angeles City, Pampanga',
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.tenants.index'))
            ->post(route('admin.tenants.store'), $payload);

        $response->assertRedirect(route('admin.tenants.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'josefa.mercado@renter.test',
            'name' => 'Josefa Mercado',
            'role' => 'tenant',
        ]);

        $user = User::query()->where('email', 'josefa.mercado@renter.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecurePass1', $user->password));

        $this->assertDatabaseHas('tenants', [
            'user_id' => $user->id,
            'owner_id' => $admin->id,
            'phone_number' => '+639998887766',
        ]);
    }

    public function test_new_tenant_user_has_role_tenant(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->from(route('admin.tenants.index'))
            ->post(route('admin.tenants.store'), [
                'name' => 'Lourdes Navarro',
                'email' => 'lourdes.navarro@renter.test',
                'password' => 'AnotherPass2',
                'phone_number' => '+639112223344',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'lourdes.navarro@renter.test',
            'role' => 'tenant',
        ]);
    }

    public function test_admin_cannot_create_tenant_with_duplicate_email(): void
    {
        $admin = $this->createAdmin();
        $this->createTenantUser([
            'email' => 'duplicate.email@renter.test',
        ], [], $admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.tenants.index'))
            ->post(route('admin.tenants.store'), [
                'name' => 'Different Person',
                'email' => 'duplicate.email@renter.test',
                'password' => 'Password11',
                'phone_number' => '+639554433221',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_tenant_info(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([
            'name' => 'Old Display Name',
            'email' => 'old.email@renter.test',
        ], [], $admin);
        $tenant = $tenantUser->tenant;

        $response = $this->actingAs($admin)
            ->from(route('admin.tenants.show', $tenant))
            ->put(route('admin.tenants.update', $tenant), [
                'name' => 'Updated Display Name',
                'email' => 'new.email@renter.test',
                'phone_number' => '+639000001111',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_number' => '+639000002222',
                'address' => '88 Friendship Highway, Dau, Mabalacat, Pampanga',
            ]);

        $response->assertRedirect(route('admin.tenants.show', $tenant));

        $this->assertDatabaseHas('users', [
            'id' => $tenantUser->id,
            'name' => 'Updated Display Name',
            'email' => 'new.email@renter.test',
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'phone_number' => '+639000001111',
            'address' => '88 Friendship Highway, Dau, Mabalacat, Pampanga',
        ]);
    }

    public function test_admin_can_soft_delete_a_tenant(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $tenant = $tenantUser->tenant;

        $response = $this->actingAs($admin)
            ->from(route('admin.tenants.index'))
            ->delete(route('admin.tenants.destroy', $tenant));

        $response->assertRedirect(route('admin.tenants.index'));
        $this->assertSoftDeleted('tenants', [
            'id' => $tenant->id,
        ]);
    }

    public function test_admin_can_view_tenant_profile_page(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([
            'name' => 'Profile View Tenant',
        ], [], $admin);
        $tenant = $tenantUser->tenant;

        $response = $this->actingAs($admin)->get(route('admin.tenants.show', $tenant));

        $response->assertOk();
        $response->assertSee('Profile View Tenant');
    }
}
