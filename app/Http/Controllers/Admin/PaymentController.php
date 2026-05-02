<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = $this->scopedPaymentsQuery()
            ->with(['lease.tenant.user', 'lease.unit.property'])
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->paginate(10);

        $pendingCount = $this->scopedPaymentsQuery()->where('status', 'pending')->count();
        $verifyingCount = $this->scopedPaymentsQuery()->where('status', 'verifying')->count();
        $paidCount = $this->scopedPaymentsQuery()->where('status', 'paid')->count();
        $lateCount = $this->scopedPaymentsQuery()->where('status', 'late')->count();
        $rejectedCount = $this->scopedPaymentsQuery()->where('status', 'rejected')->count();

        return view('admin.payments.index', compact(
            'payments',
            'pendingCount',
            'verifyingCount',
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

        if ($payment->status !== 'verifying') {
            return back()->with('error', 'Only payments awaiting verification can be verified.');
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

        if ($payment->status !== 'verifying') {
            return back()->with('error', 'Only payments awaiting verification can be rejected.');
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

    private function scopedPaymentsQuery(): Builder
    {
        return Payment::query()
            ->whereHas('lease.unit.property', fn ($q) => $q->where('owner_id', auth()->id()));
    }
}
