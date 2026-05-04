<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenant;

        $activeLeases = $tenant
            ? $tenant->leases()
                ->where('status', 'active')
                ->with(['unit.property', 'unit.images', 'unit.property.images', 'payments'])
                ->orderByDesc('start_date')
                ->get()
            : collect();

        $leaseCards = $activeLeases->map(function ($lease) {
            $nextPayment = $lease->payments()
                ->whereNotIn('status', ['paid'])
                ->orderBy('due_date', 'asc')
                ->first();

            return [
                'lease' => $lease,
                'nextPayment' => $nextPayment,
            ];
        });

        $allImages = $activeLeases->flatMap(function ($lease) {
            $unitImages = $lease->unit?->images ?? collect();
            $propertyImages = $lease->unit?->property?->images ?? collect();

            return $unitImages->merge($propertyImages);
        });

        $allLeaseIds = $activeLeases->pluck('id');
        $recentPayments = Payment::query()
            ->whereIn('lease_id', $allLeaseIds)
            ->with(['lease.unit.property'])
            ->orderBy('due_date', 'desc')
            ->take(5)
            ->get();

        $lease = $activeLeases->first();
        $nextPayment = $leaseCards->first()['nextPayment'] ?? null;

        $contractPath = $lease?->contract_path;
        $contractUploadedAt = $lease?->contract_uploaded_at;

        $unitImages = $lease?->unit?->images ?? collect();
        $propertyImages = $lease?->unit?->property?->images ?? collect();

        return view('tenant.dashboard', [
            'title' => 'My Dashboard',
            'tenant' => $tenant,
            'activeLeases' => $activeLeases,
            'leaseCards' => $leaseCards,
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
