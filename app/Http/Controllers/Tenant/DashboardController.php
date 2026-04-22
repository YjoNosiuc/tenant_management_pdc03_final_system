<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenant;

        $lease = $tenant?->leases()
            ->where('status', 'active')
            ->with('unit.property')
            ->orderByDesc('start_date')
            ->first();

        $nextPayment = $lease?->payments()
            ->whereIn('status', ['pending', 'late'])
            ->orderBy('due_date')
            ->first();

        $recentPayments = $lease?->payments()
            ->latest('due_date')
            ->take(5)
            ->get() ?? collect();

        return view('tenant.dashboard', [
            'title' => 'My Dashboard',
            'tenant' => $tenant,
            'lease' => $lease,
            'nextPayment' => $nextPayment,
            'recentPayments' => $recentPayments,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    private function unreadNotificationCount(): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        $columns = Schema::getColumnListing('notifications');

        if (! in_array('user_id', $columns, true) || ! in_array('read_at', $columns, true)) {
            return 0;
        }

        return (int) DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }
}
