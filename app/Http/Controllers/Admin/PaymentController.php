<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
        $paidCount = $this->scopedPaymentsQuery()->where('status', 'paid')->count();
        $lateCount = $this->scopedPaymentsQuery()->where('status', 'late')->count();
        $rejectedCount = $this->scopedPaymentsQuery()->where('status', 'rejected')->count();

        return view('admin.payments.index', compact(
            'payments',
            'pendingCount',
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

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be verified.');
        }

        $payment->update([
            'status' => 'paid',
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->assertPaymentOwnedByAuth($payment);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
            'verified_at' => null,
        ]);

        return back()->with('success', 'Payment rejected.');
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
