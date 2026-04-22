<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Tenant payment proofs are stored on the public disk under payments/proofs.
     * Run: php artisan storage:link
     */
    public function index(): View
    {
        $tenant = auth()->user()->tenant;

        $lease = $tenant?->leases()
            ->where('status', 'active')
            ->with('unit.property')
            ->orderByDesc('start_date')
            ->first();

        $payments = $lease
            ? $lease->payments()->latest('due_date')->paginate(10)
            : Payment::query()->whereRaw('1 = 0')->paginate(10);

        return view('tenant.payments.index', [
            'title' => 'My Payments',
            'payments' => $payments,
            'lease' => $lease,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['lease.unit.property']);

        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless((int) $payment->lease->tenant_id === (int) $tenant->id, 403);

        return view('tenant.payments.show', [
            'title' => 'Payment Details',
            'payment' => $payment,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function submitProof(Request $request, Payment $payment): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless((int) $payment->lease->tenant_id === (int) $tenant->id, 403);

        if (! in_array($payment->status, ['pending', 'late'], true)) {
            return back()->with('error', 'You can only submit proof when your payment is pending or late.');
        }

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }

        $path = $request->file('proof')->store('payments/proofs', 'public');

        $payment->update([
            'proof_of_payment' => $path,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Proof submitted successfully. Awaiting admin verification.');
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
