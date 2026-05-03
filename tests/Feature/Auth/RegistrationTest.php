<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_new_owner_registration_creates_admin_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New Owner',
            'email' => 'newowner@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'newowner@test.com',
            'role' => 'admin',
        ]);
    }

    public function test_new_owner_must_change_password_is_false_after_registration(): void
    {
        $this->post(route('register'), [
            'name' => 'New Owner',
            'email' => 'newowner@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'newowner@test.com')->first();
        $this->assertFalse((bool) $user->must_change_password);
    }
}
