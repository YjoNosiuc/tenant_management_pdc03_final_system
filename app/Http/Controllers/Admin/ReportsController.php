<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportsController extends Controller
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getDateRange(Request $request): array
    {
        $period = $request->get('period', '6months');

        if ($period === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date, 'Asia/Manila')->startOfDay();
            $endDate = Carbon::parse($request->end_date, 'Asia/Manila')->endOfDay();
        } else {
            $endDate = now('Asia/Manila')->endOfDay();
            $startDate = match ($period) {
                '30days' => now('Asia/Manila')->subDays(30)->startOfDay(),
                '3months' => now('Asia/Manila')->subMonths(3)->startOfDay(),
                '12months' => now('Asia/Manila')->subMonths(12)->startOfDay(),
                default => now('Asia/Manila')->subMonths(6)->startOfDay(),
            };
        }

        return [$startDate, $endDate];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $ownerId = (int) auth()->id();
        [$startDate, $endDate] = $this->getDateRange($request);

        $monthlyIncome = [];
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $basePaid = Payment::whereHas('lease.unit.property',
                fn ($q) => $q->where('owner_id', $ownerId)
            )
                ->where('status', 'paid')
                ->whereYear('payment_date', $current->year)
                ->whereMonth('payment_date', $current->month);

            $monthlyIncome[] = [
                'month' => $current->format('M Y'),
                'amount' => (float) (clone $basePaid)->sum('total_amount_due'),
                'payments_count' => (clone $basePaid)->count(),
            ];
            $current->addMonth();
        }

        $totalCollected = array_sum(array_column($monthlyIncome, 'amount'));

        $propertyBreakdown = Property::where('owner_id', $ownerId)
            ->with(['units.leases.payments' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'paid')
                    ->whereBetween('payment_date', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($property) {
                $totalCollected = 0;
                $totalUnits = $property->units->count();
                $occupiedUnits = $property->units->where('status', 'occupied')->count();

                foreach ($property->units as $unit) {
                    foreach ($unit->leases as $lease) {
                        foreach ($lease->payments as $payment) {
                            $totalCollected += (float) $payment->total_amount_due;
                        }
                    }
                }

                return [
                    'name' => $property->name,
                    'city' => $property->city,
                    'total_units' => $totalUnits,
                    'occupied_units' => $occupiedUnits,
                    'vacant_units' => $totalUnits - $occupiedUnits,
                    'occupancy_rate' => $totalUnits > 0
                        ? (int) round(($occupiedUnits / $totalUnits) * 100)
                        : 0,
                    'total_collected' => $totalCollected,
                ];
            })
            ->sortByDesc('total_collected')
            ->values();

        $totalUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', $ownerId))->count();
        $occupiedUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', $ownerId))
            ->where('status', 'occupied')
            ->count();
        $vacantUnits = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0 ? (int) round(($occupiedUnits / $totalUnits) * 100) : 0;

        $occupancyTrend = [];
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $activeLeases = Lease::whereHas('unit.property',
                fn ($q) => $q->where('owner_id', $ownerId)
            )
                ->where('status', 'active')
                ->where('start_date', '<=', $current->copy()->endOfMonth())
                ->where('end_date', '>=', $current->copy()->startOfMonth())
                ->count();

            $occupancyTrend[] = [
                'month' => $current->format('M Y'),
                'active_leases' => $activeLeases,
                'occupancy_rate' => $totalUnits > 0
                    ? (int) round(($activeLeases / $totalUnits) * 100)
                    : 0,
            ];
            $current->addMonth();
        }

        $latePayments = Payment::whereHas('lease.unit.property',
            fn ($q) => $q->where('owner_id', $ownerId)
        )
            ->where('status', 'late')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with(['lease.tenant.user', 'lease.unit.property'])
            ->orderBy('due_date', 'desc')
            ->get();

        $totalLateAmount = (float) $latePayments->sum('total_amount_due');
        $totalLateFees = (float) $latePayments->sum('late_fee_amount');

        $lateByProperty = $latePayments->groupBy(fn ($p) => $p->lease?->unit?->property?->name ?? 'Unknown')
            ->map(fn ($payments, $name) => [
                'name' => $name,
                'count' => $payments->count(),
                'total' => (float) $payments->sum('total_amount_due'),
            ])
            ->sortByDesc('count')
            ->values();

        $paymentStatusSummary = [];
        foreach (['pending', 'verifying', 'paid', 'late', 'rejected'] as $status) {
            $paymentStatusSummary[$status] = Payment::whereHas('lease.unit.property',
                fn ($q) => $q->where('owner_id', $ownerId)
            )
                ->where('status', $status)
                ->whereBetween('due_date', [$startDate, $endDate])
                ->count();
        }

        return [
            'monthlyIncome' => $monthlyIncome,
            'totalCollected' => $totalCollected,
            'propertyBreakdown' => $propertyBreakdown,
            'totalUnits' => $totalUnits,
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'occupancyRate' => $occupancyRate,
            'occupancyTrend' => $occupancyTrend,
            'latePayments' => $latePayments,
            'totalLateAmount' => $totalLateAmount,
            'totalLateFees' => $totalLateFees,
            'lateByProperty' => $lateByProperty,
            'paymentStatusSummary' => $paymentStatusSummary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function index(Request $request): View
    {
        $data = $this->buildReportData($request);

        return view('admin.reports.index', array_merge($data, [
            'title' => 'Reports',
        ]));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $owner = auth()->user();

        $pdf = Pdf::loadView('admin.reports.pdf', array_merge($data, compact('owner')))
            ->setPaper('a4', 'portrait');

        return $pdf->download('renttrack-report-'.now('Asia/Manila')->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $filename = 'renttrack-report-'.now('Asia/Manila')->format('Y-m-d').'.xlsx';

        return Excel::download(new ReportsExport((int) auth()->id(), $startDate, $endDate), $filename);
    }
}
