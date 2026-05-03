<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $startThisMonth = now()->startOfMonth();
        $endThisMonth = now()->endOfMonth();
        $startLastMonth = now()->copy()->subMonth()->startOfMonth();
        $endLastMonth = now()->copy()->subMonth()->endOfMonth();

        $totalProperties = Property::where('owner_id', auth()->id())->count();
        $totalUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))->count();
        $activeLeases = Lease::whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'active')
            ->count();
        $pendingPayments = Payment::query()
            ->whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereIn('status', ['pending', 'late', 'verifying'])
            ->count();

        $occupiedUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'occupied')
            ->count();
        $vacantUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'vacant')
            ->count();
        $occupancyPercent = $totalUnits > 0 ? (int) round(($occupiedUnits / $totalUnits) * 100) : 0;

        $recentPayments = Payment::query()
            ->whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->with(['lease.tenant.user', 'lease.unit.property'])
            ->latest()
            ->take(8)
            ->get();

        $propertiesThisMonth = Property::where('owner_id', auth()->id())
            ->whereBetween('created_at', [$startThisMonth, $endThisMonth])
            ->count();
        $propertiesLastMonth = Property::where('owner_id', auth()->id())
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $unitsThisMonth = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startThisMonth, $endThisMonth])
            ->count();
        $unitsLastMonth = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $leasesThisMonth = Lease::whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startThisMonth, $endThisMonth])
            ->count();
        $leasesLastMonth = Lease::whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $paymentsThisMonth = Payment::whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startThisMonth, $endThisMonth])
            ->count();
        $paymentsLastMonth = Payment::whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $monthlyIncomeLabels = [];
        $monthlyIncomeData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now('Asia/Manila')->subMonths($i);
            $monthlyIncomeLabels[] = $month->format('M Y');
            $monthlyIncomeData[] = (float) Payment::whereHas('lease.unit.property',
                fn ($q) => $q->where('owner_id', auth()->id())
            )
                ->where('status', 'paid')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('total_amount_due');
        }

        $paymentStatusData = [];
        foreach (['pending', 'verifying', 'paid', 'late', 'rejected'] as $status) {
            $paymentStatusData[] = Payment::whereHas('lease.unit.property',
                fn ($q) => $q->where('owner_id', auth()->id())
            )->where('status', $status)->count();
        }

        $paymentStatusCounts = [
            'pending' => $paymentStatusData[0],
            'verifying' => $paymentStatusData[1],
            'paid' => $paymentStatusData[2],
            'late' => $paymentStatusData[3],
            'rejected' => $paymentStatusData[4],
        ];
        $totalPaymentsCount = array_sum($paymentStatusData);

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'totalProperties' => $totalProperties,
            'totalUnits' => $totalUnits,
            'activeLeases' => $activeLeases,
            'pendingPayments' => $pendingPayments,
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'occupancyPercent' => $occupancyPercent,
            'recentPayments' => $recentPayments,
            'propertiesTrend' => $this->monthTrendLabel($propertiesThisMonth, $propertiesLastMonth, 'properties'),
            'unitsTrend' => $this->monthTrendLabel($unitsThisMonth, $unitsLastMonth, 'units'),
            'leasesTrend' => $this->monthTrendLabel($leasesThisMonth, $leasesLastMonth, 'leases'),
            'paymentsTrend' => $this->monthTrendLabel($paymentsThisMonth, $paymentsLastMonth, 'payments'),
            'unreadNotificationCount' => $this->unreadNotificationCount(),
            'monthlyIncomeLabels' => $monthlyIncomeLabels,
            'monthlyIncomeData' => $monthlyIncomeData,
            'paymentStatusData' => $paymentStatusData,
            'paymentStatusCounts' => $paymentStatusCounts,
            'totalPaymentsCount' => $totalPaymentsCount,
        ]);
    }

    private function monthTrendLabel(int $current, int $previous, string $noun): string
    {
        if ($current === 0 && $previous === 0) {
            return 'No new '.$noun.' this month';
        }

        if ($previous === 0) {
            return $current === 1
                ? '1 new '.$noun.' this month'
                : $current.' new '.$noun.' this month';
        }

        $pct = (int) round((($current - $previous) / $previous) * 100);

        if ($pct > 0) {
            return '+'.$pct.'% vs last month';
        }

        if ($pct < 0) {
            return $pct.'% vs last month';
        }

        return 'Flat vs last month';
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
