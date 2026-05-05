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
    public function index(Request $request): View
    {
        $tenant = auth()->user()->tenant;

        $leases = $tenant
            ? $tenant->leases()
                ->where('status', 'active')
                ->with('unit.property')
                ->orderByDesc('start_date')
                ->get()
            : collect();

        $paymentsByLease = [];
        foreach ($leases as $lease) {
            $query = $lease->payments()->orderBy('due_date', 'asc');
            $paymentsByLease[] = [
                'lease' => $lease,
                'payments' => $query->paginate(10, ['*'], 'page_'.$lease->id),
            ];
        }

        $selectedLeaseId = $request->get('lease_id');

        return view('tenant.payments.index', [
            'title' => 'My Payments',
            'leases' => $leases,
            'paymentsByLease' => $paymentsByLease,
            'selectedLeaseId' => $selectedLeaseId,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);

        $tenantLeaseIds = $tenant->leases()->where('status', 'active')->pluck('id');
        if (! $tenantLeaseIds->contains($payment->lease_id)) {
            abort(403);
        }

        $payment->load('lease.unit.property');

        $lease = $payment->lease;
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

        if (! in_array($payment->status, ['pending', 'verifying', 'verifying_late', 'rejected', 'late'], true)) {
            return back()->with('error', 'You cannot submit proof for this payment in its current state.');
        }

        $earliestUnpaid = $payment->lease->payments()
            ->whereNotIn('status', ['paid'])
            ->orderBy('due_date')
            ->first();

        if (! $earliestUnpaid || (int) $earliestUnpaid->id !== (int) $payment->id) {
            return back()->with('error', 'You must pay in order. Please submit proof for the earliest pending payment first.');
        }

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($payment->proof_of_payment) {
            Storage::disk('public')->delete($payment->proof_of_payment);
        }

        $path = Storage::disk('public')->putFile('payments/proofs', $request->file('proof'));

        $newStatus = match ($payment->status) {
            'late' => 'verifying_late',
            'verifying_late' => 'verifying_late',
            'rejected' => ((float) $payment->late_fee_amount > 0) ? 'verifying_late' : 'verifying',
            default => 'verifying',
        };

        $payment->update([
            'proof_of_payment' => $path,
            'status' => $newStatus,
            'payment_date' => now()->toDateString(),
        ]);

        $payment->load('lease.tenant.user', 'lease.unit.property');

        $lease = $payment->lease;
        $unit = $lease->unit;
        $property = $unit->property;
        $owner = User::find($property->owner_id);
        $tenantUser = auth()->user();

        $lateNote = $newStatus === 'verifying_late' ? ' (Late Payment)' : '';

        if ($owner) {
            (new NotificationService)->send(
                $owner,
                'payment_submitted',
                "{$tenantUser->name} submitted proof of payment for Unit {$unit->unit_number}{$lateNote} — ₱"
                    .number_format((float) $payment->total_amount_due, 2),
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
