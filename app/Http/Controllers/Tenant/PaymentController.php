<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\NotificationService;
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

        $earliestUnpaidPaymentId = $lease
            ? $lease->payments()
                ->whereNotIn('status', ['paid'])
                ->orderBy('due_date')
                ->value('id')
            : null;

        $earliestUnpaidDueLabel = $lease
            ? optional(
                $lease->payments()
                    ->whereNotIn('status', ['paid'])
                    ->orderBy('due_date')
                    ->first()
            )->due_date?->format('M Y')
            : null;

        return view('tenant.payments.index', [
            'title' => 'My Payments',
            'payments' => $payments,
            'lease' => $lease,
            'earliestUnpaidPaymentId' => $earliestUnpaidPaymentId,
            'earliestUnpaidDueLabel' => $earliestUnpaidDueLabel,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['lease.unit.property']);

        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless((int) $payment->lease->tenant_id === (int) $tenant->id, 403);

        $lease = $tenant->leases()
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->first();

        if (! $lease || (int) $lease->id !== (int) $payment->lease_id) {
            $lease = $payment->lease;
        }

        $earliestUnpaid = $lease->payments()
            ->whereNotIn('status', ['paid'])
            ->orderBy('due_date', 'asc')
            ->first();

        $isEarliestUnpaid = $earliestUnpaid !== null && (int) $earliestUnpaid->id === (int) $payment->id;

        return view('tenant.payments.show', [
            'title' => 'Payment Details',
            'payment' => $payment,
            'isEarliestUnpaid' => $isEarliestUnpaid,
            'earliestUnpaid' => $earliestUnpaid,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function submitProof(Request $request, Payment $payment): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);

        $payment->loadMissing('lease');

        abort_unless((int) $payment->lease->tenant_id === (int) $tenant->id, 403);
        abort_unless($payment->lease->status === 'active', 403);

        if (! in_array($payment->status, ['pending', 'verifying', 'rejected', 'late'], true)) {
            return back()->with('error', 'You cannot submit proof for this payment in its current state.');
        }

        $earliestUnpaid = $payment->lease->payments()
            ->whereNotIn('status', ['paid'])
            ->orderBy('due_date')
            ->first();

        if (! $earliestUnpaid || (int) $earliestUnpaid->id !== (int) $payment->id) {
            return back()->with('error', 'You must pay in order. Please submit proof for the earliest pending payment first.');
        }

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }

        $path = Storage::disk('public')->putFile('payments/proofs', $request->file('proof'));

        $payment->update([
            'proof_of_payment' => $path,
            'status' => 'verifying',
            'payment_date' => now()->toDateString(),
        ]);

        $payment->load('lease.tenant.user', 'lease.unit.property');

        $lease = $payment->lease;
        $unit = $lease->unit;
        $property = $unit->property;
        $owner = User::find($property->owner_id);
        $tenant = auth()->user();

        if ($owner) {
            (new NotificationService)->send(
                $owner,
                'payment_submitted',
                "{$tenant->name} submitted proof of payment for Unit {$unit->unit_number} — ₱".number_format((float) $payment->amount_paid, 2),
                route('admin.payments.show', $payment->id)
            );
        }

        return back()->with('success', 'Proof submitted successfully. Awaiting landlord verification.');
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
