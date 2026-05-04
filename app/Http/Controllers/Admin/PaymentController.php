<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $ownerId = (int) auth()->id();

        $properties = Property::query()
            ->where('owner_id', $ownerId)
            ->orderBy('name')
            ->get();

        $propertyId = $request->filled('property_id') ? (int) $request->property_id : null;
        if ($propertyId && ! $properties->contains('id', $propertyId)) {
            return redirect()->route('admin.payments.index', $request->except(['property_id', 'unit_id']));
        }

        $units = collect();
        if ($propertyId) {
            $units = Unit::query()
                ->where('property_id', $propertyId)
                ->whereHas('property', fn ($q) => $q->where('owner_id', $ownerId))
                ->orderBy('unit_number')
                ->get();
        }

        $unitId = $request->filled('unit_id') ? (int) $request->unit_id : null;
        if ($unitId) {
            $unitQuery = Unit::query()
                ->whereKey($unitId)
                ->whereHas('property', fn ($q) => $q->where('owner_id', $ownerId));

            if ($propertyId) {
                $unitQuery->where('property_id', $propertyId);
            }

            if (! $unitQuery->exists()) {
                return redirect()->route('admin.payments.index', $request->except('unit_id'));
            }
        }

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;
        if ($status !== null && ! in_array($status, ['pending', 'verifying', 'verifying_late', 'paid', 'late', 'rejected'], true)) {
            return redirect()->route('admin.payments.index', $request->except('status'));
        }

        $searchRaw = $request->query('search');
        $search = is_string($searchRaw) ? mb_substr(trim($searchRaw), 0, 255) : '';

        $query = Payment::query()
            ->whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', $ownerId))
            ->with(['lease.tenant.user', 'lease.unit.property']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($propertyId) {
            $query->whereHas('lease.unit', fn ($q) => $q->where('property_id', $propertyId));
        }

        if ($unitId) {
            $query->whereHas('lease', fn ($q) => $q->where('unit_id', $unitId));
        }

        if ($search !== '') {
            $like = '%'.addcslashes($search, '%\\_').'%';
            $query->whereHas('lease.tenant.user', fn ($q) => $q->where('name', 'like', $like));
        }

        $query->orderByDesc('due_date')->orderByDesc('id');

        $payments = $query->paginate(10)->withQueryString();

        $baseQuery = Payment::query()->whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', $ownerId));

        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $verifyingCount = (clone $baseQuery)->whereIn('status', ['verifying', 'verifying_late'])->count();
        $verifyingLateCount = (clone $baseQuery)->where('status', 'verifying_late')->count();
        $paidCount = (clone $baseQuery)->where('status', 'paid')->count();
        $lateCount = (clone $baseQuery)->where('status', 'late')->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();

        return view('admin.payments.index', compact(
            'payments',
            'properties',
            'units',
            'pendingCount',
            'verifyingCount',
            'verifyingLateCount',
            'paidCount',
            'lateCount',
            'rejectedCount'
        ));
    }

    public function show(Payment $payment): View
    {
        $this->assertPaymentOwnedByAuth($payment);

        $payment->load(['lease.tenant.user', 'lease.unit.property']);

        $proofUrl = $payment->proof_of_payment
            ? Storage::url($payment->proof_of_payment)
            : null;

        return view('admin.payments.show', compact('payment', 'proofUrl'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        $this->assertPaymentOwnedByAuth($payment);

        if (! in_array($payment->status, ['verifying', 'verifying_late'], true)) {
            return back()->with('error', 'Only payments with submitted proof can be verified.');
        }

        $payment->update([
            'status' => 'paid',
            'verified_at' => now(),
        ]);

        $payment->load('lease.tenant.user', 'lease.unit.property');

        $lease = $payment->lease;
        $tenantUser = $lease->tenant->user;

        (new NotificationService)->send(
            $tenantUser,
            'payment_verified',
            'Your payment of ₱'.number_format((float) $payment->amount_paid, 2).' for '.$payment->due_date->format('M d, Y').' has been verified. ✓',
            route('tenant.payments.show', $payment->id)
        );

        return back()->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->assertPaymentOwnedByAuth($payment);

        if (! in_array($payment->status, ['verifying', 'verifying_late'], true)) {
            return back()->with('error', 'Only payments with submitted proof can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
            'verified_at' => null,
        ]);

        $payment->load('lease.tenant.user', 'lease.unit.property');

        $lease = $payment->lease;
        $tenantUser = $lease->tenant->user;

        (new NotificationService)->send(
            $tenantUser,
            'payment_rejected',
            "Your payment proof was rejected. Reason: {$payment->remarks}",
            route('tenant.payments.show', $payment->id)
        );

        return back()->with('success', 'Payment rejected. Tenant can resubmit.');
    }

    private function assertPaymentOwnedByAuth(Payment $payment): void
    {
        $payment->loadMissing('lease.unit.property');

        $property = $payment->lease?->unit?->property;

        if (! $property || (int) $property->owner_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
