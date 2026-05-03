<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcedPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function createTenantWithTempPassword(): array
    {
        $owner = User::create([
            'name' => 'Landlord Rico Mendoza',
            'email' => 'rico.mendoza@landlord.test',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $user = User::create([
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => true,
        ]);

        $tenant = Tenant::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone_number' => '09171234567',
        ]);

        return [$user, $tenant];
    }

    public function test_new_tenant_is_redirected_to_change_password_on_login(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->get(route('tenant.dashboard'));

        $response->assertRedirect(route('password.change'));
    }

    public function test_tenant_cannot_access_payments_before_changing_password(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->get(route('tenant.payments.index'));

        $response->assertRedirect(route('password.change'));
    }

    public function test_tenant_cannot_access_notifications_before_changing_password(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->get(route('tenant.notifications.index'));

        $response->assertRedirect(route('password.change'));
    }

    public function test_change_password_page_is_accessible_with_temp_password(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->get(route('password.change'));

        $response->assertStatus(200);
    }

    public function test_tenant_can_change_password_successfully(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->post(route('password.change.update'), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('tenant.dashboard'));

        $user->refresh();
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_tenant_cannot_use_temporary_password_as_new_password(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->post(route('password.change.update'), [
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('password');

        $user->refresh();
        $this->assertTrue((bool) $user->must_change_password);
    }

    public function test_tenant_cannot_change_password_with_too_short_password(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->post(route('password.change.update'), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_tenant_cannot_change_password_with_mismatched_confirmation(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $response = $this->actingAs($user)
            ->post(route('password.change.update'), [
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_after_changing_password_tenant_can_access_dashboard(): void
    {
        [$user] = $this->createTenantWithTempPassword();

        $this->actingAs($user)
            ->post(route('password.change.update'), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $user->refresh();

        $response = $this->actingAs($user)
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_tenant_with_changed_password_is_not_redirected_to_change_password(): void
    {
        $owner = User::create([
            'name' => 'Landlord Bea Aquino',
            'email' => 'bea.aquino@landlord.test',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $user = User::create([
            'name' => 'Normal Tenant',
            'email' => 'normal@test.com',
            'password' => 'mypassword',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);

        Tenant::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone_number' => '09171234567',
        ]);

        $response = $this->actingAs($user)
            ->get(route('password.change'));

        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_admin_is_never_redirected_to_change_password_page(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }
}
