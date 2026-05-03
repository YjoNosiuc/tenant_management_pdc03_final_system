<?php

namespace Tests\Feature\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        return User::create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);
    }

    private function createNotification(
        User $user,
        string $type,
        string $message,
        string $url,
        bool $read = false
    ): int {
        return (int) DB::table('notifications')->insertGetId([
            'user_id' => $user->id,
            'type' => $type,
            'data' => json_encode(['message' => $message, 'url' => $url]),
            'read_at' => $read ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_clicking_admin_notification_marks_it_as_read_and_redirects(): void
    {
        $owner = $this->createOwner();

        $url = route('admin.payments.index');
        $notificationId = $this->createNotification(
            $owner,
            'payment_submitted',
            'Test payment submitted',
            $url
        );

        $response = $this->actingAs($owner)
            ->get(route('admin.notifications.redirect', $notificationId));

        $response->assertRedirect($url);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'user_id' => $owner->id,
        ]);

        $notification = DB::table('notifications')->find($notificationId);
        $this->assertNotNull($notification->read_at);
    }

    public function test_admin_cannot_redirect_another_users_notification(): void
    {
        $owner1 = $this->createOwner();
        $owner2 = User::create([
            'name' => 'Owner 2',
            'email' => 'owner2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $notificationId = $this->createNotification(
            $owner2,
            'payment_submitted',
            'Owner2 notification',
            route('admin.payments.index')
        );

        $response = $this->actingAs($owner1)
            ->get(route('admin.notifications.redirect', $notificationId));

        $response->assertStatus(404);
    }

    public function test_clicking_notification_that_is_already_read_still_redirects(): void
    {
        $owner = $this->createOwner();

        $url = route('admin.payments.index');
        $notificationId = $this->createNotification(
            $owner,
            'payment_verified',
            'Already read notification',
            $url,
            true
        );

        $response = $this->actingAs($owner)
            ->get(route('admin.notifications.redirect', $notificationId));

        $response->assertRedirect($url);
    }

    public function test_unread_notification_count_decreases_after_marking_as_read(): void
    {
        $owner = $this->createOwner();

        $this->createNotification($owner, 'payment_submitted', 'Message 1', '/admin/payments/1');
        $this->createNotification($owner, 'payment_late_owner', 'Message 2', '/admin/payments/2');

        $unreadBefore = DB::table('notifications')
            ->where('user_id', $owner->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(2, $unreadBefore);

        $notificationId = (int) DB::table('notifications')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->actingAs($owner)
            ->get(route('admin.notifications.redirect', $notificationId));

        $unreadAfter = DB::table('notifications')
            ->where('user_id', $owner->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(1, $unreadAfter);
    }

    public function test_tenant_notification_redirect_marks_as_read_and_redirects(): void
    {
        $owner = User::create([
            'name' => 'Landlord Grace Lim',
            'email' => 'grace.lim@landlord.test',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $user = User::create([
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);

        Tenant::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'phone_number' => '09171234567',
        ]);

        $url = '/tenant/payments/1';
        $notificationId = $this->createNotification(
            $user,
            'payment_verified',
            'Your payment has been verified',
            $url
        );

        $response = $this->actingAs($user)
            ->get(route('tenant.notifications.redirect', $notificationId));

        $response->assertRedirect($url);

        $notification = DB::table('notifications')->find($notificationId);
        $this->assertNotNull($notification->read_at);
    }

    public function test_tenant_cannot_redirect_another_tenants_notification(): void
    {
        $owner = User::create([
            'name' => 'Landlord Paolo Reyes',
            'email' => 'paolo.reyes@landlord.test',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $user1 = User::create([
            'name' => 'Tenant 1',
            'email' => 'tenant1@test.com',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);
        $user2 = User::create([
            'name' => 'Tenant 2',
            'email' => 'tenant2@test.com',
            'password' => 'password',
            'role' => 'tenant',
            'must_change_password' => false,
        ]);

        Tenant::create(['user_id' => $user1->id, 'owner_id' => $owner->id, 'phone_number' => '09171234567']);
        Tenant::create(['user_id' => $user2->id, 'owner_id' => $owner->id, 'phone_number' => '09181234567']);

        $notificationId = $this->createNotification(
            $user2,
            'payment_verified',
            'Tenant2 notification',
            '/tenant/payments/1'
        );

        $response = $this->actingAs($user1)
            ->get(route('tenant.notifications.redirect', $notificationId));

        $response->assertStatus(404);
    }
}
