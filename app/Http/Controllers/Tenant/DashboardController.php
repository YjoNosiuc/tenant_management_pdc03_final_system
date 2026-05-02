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
            ->with(['unit.images', 'unit.property.images'])
            ->orderByDesc('start_date')
            ->first();

        $unitImages = $lease?->unit?->images ?? collect();
        $propertyImages = $lease?->unit?->property?->images ?? collect();
        $allImages = $unitImages->merge($propertyImages);

        $nextPayment = $lease?->payments()
            ->whereNotIn('status', ['paid'])
            ->orderBy('due_date')
            ->first();

        $recentPayments = $lease?->payments()
            ->latest('due_date')
            ->take(5)
            ->get() ?? collect();

        $contractPath = $lease?->contract_path;
        $contractUploadedAt = $lease?->contract_uploaded_at;

        return view('tenant.dashboard', [
            'title' => 'My Dashboard',
            'tenant' => $tenant,
            'lease' => $lease,
            'contractPath' => $contractPath,
            'contractUploadedAt' => $contractUploadedAt,
            'nextPayment' => $nextPayment,
            'recentPayments' => $recentPayments,
            'unitImages' => $unitImages,
            'propertyImages' => $propertyImages,
            'allImages' => $allImages,
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
