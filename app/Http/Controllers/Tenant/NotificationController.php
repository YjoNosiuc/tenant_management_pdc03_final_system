<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        $unreadCount = (int) DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('tenant.notifications.index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'unreadNotificationCount' => $unreadCount,
        ]);
    }

    public function markAsReadAndRedirect(Request $request, string $id): RedirectResponse
    {
        $notification = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $notification) {
            abort(404);
        }

        DB::table('notifications')
            ->where('id', $id)
            ->update(['read_at' => now(), 'updated_at' => now()]);

        $data = json_decode($notification->data, true);
        $url = $data['url'] ?? route('tenant.notifications.index');

        return redirect($url);
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Notification could not be marked as read.');
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
