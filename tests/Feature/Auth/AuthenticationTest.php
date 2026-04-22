<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AuthenticationTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_login_with_correct_credentials_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = $this->createAdmin([
            'email' => 'ricardo.villarin@landlord.test',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_tenant_can_login_with_correct_credentials_and_is_redirected_to_tenant_dashboard(): void
    {
        $tenantUser = $this->createTenantUser([
            'email' => 'ana.delrosario@renter.test',
        ]);

        $response = $this->post('/login', [
            'email' => $tenantUser->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($tenantUser);
        $response->assertRedirect(route('tenant.dashboard', absolute: false));
    }

    public function test_user_with_wrong_password_cannot_login(): void
    {
        $user = $this->createTenantUser([
            'email' => 'wrong.password.case@renter.test',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'not-the-right-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_unauthenticated_user_is_redirected_to_login_when_accessing_admin_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_to_login_when_accessing_tenant_routes(): void
    {
        $response = $this->get(route('tenant.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_tenant_cannot_access_admin_routes_and_gets_403(): void
    {
        $tenantUser = $this->createTenantUser();

        $response = $this->actingAs($tenantUser)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_tenant_routes_and_gets_403(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('tenant.dashboard'));

        $response->assertForbidden();
    }
}
