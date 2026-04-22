<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class NotificationTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_notifications_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));

        $response->assertOk();
    }

    public function test_admin_can_mark_a_single_notification_as_read(): void
    {
        $admin = $this->createAdmin();

        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $admin->id,
            'type' => 'App\\Notifications\\LeaseReminder',
            'data' => json_encode(['title' => 'Rent due soon', 'body' => 'Please settle before Friday.']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->patch(route('admin.notifications.markAsRead', ['id' => $notificationId]));

        $response->assertRedirect(route('admin.notifications.index'));

        $this->assertNotNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at')
        );
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = $this->createAdmin();

        DB::table('notifications')->insert([
            [
                'user_id' => $admin->id,
                'type' => 'App\\Notifications\\PaymentPosted',
                'data' => json_encode(['amount' => 15000]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $admin->id,
                'type' => 'App\\Notifications\\MaintenanceTicket',
                'data' => json_encode(['unit' => '12B']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->patch(route('admin.notifications.markAllAsRead'));

        $response->assertRedirect(route('admin.notifications.index'));

        $this->assertSame(
            0,
            (int) DB::table('notifications')
                ->where('user_id', $admin->id)
                ->whereNull('read_at')
                ->count()
        );
    }

    public function test_unread_notifications_show_read_at_as_null(): void
    {
        $admin = $this->createAdmin();

        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $admin->id,
            'type' => 'App\\Notifications\\SystemAlert',
            'data' => json_encode(['message' => 'Backup completed.']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at')
        );
    }

    public function test_after_marking_as_read_read_at_is_not_null(): void
    {
        $admin = $this->createAdmin();

        $notificationId = DB::table('notifications')->insertGetId([
            'user_id' => $admin->id,
            'type' => 'App\\Notifications\\WelcomeNote',
            'data' => json_encode(['message' => 'Welcome aboard.']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->patch(route('admin.notifications.markAsRead', ['id' => $notificationId]));

        $this->assertNotNull(
            DB::table('notifications')->where('id', $notificationId)->value('read_at')
        );
    }
}
