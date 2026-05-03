<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;

class LatePaymentService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function markOverduePayments(): int
    {
        $overduePayments = Payment::query()
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->with([
                'lease.unit.property',
                'lease.tenant.user',
            ])
            ->get();

        $count = 0;

        foreach ($overduePayments as $payment) {
            $lease = $payment->lease;
            $property = $lease?->unit?->property;
            $tenant = $lease?->tenant;

            if (! $property || ! $tenant) {
                continue;
            }

            $lateFee = $property->calculateLateFee((float) $payment->amount_paid);
            $totalDue = (float) $payment->amount_paid + $lateFee;

            $payment->update([
                'status' => 'late',
                'late_fee_amount' => $lateFee,
                'total_amount_due' => $totalDue,
            ]);

            $tenantUser = $tenant->user;
            if ($tenantUser) {
                $feeText = $lateFee > 0
                    ? ' A late fee of ₱'.number_format($lateFee, 2).' has been applied. Total due: ₱'.number_format($totalDue, 2).'.'
                    : '';

                $this->notificationService->send(
                    $tenantUser,
                    'payment_late',
                    'Your payment of ₱'.number_format((float) $payment->amount_paid, 2).' due on '.$payment->due_date->format('M d, Y')." is now late.{$feeText}",
                    route('tenant.payments.show', $payment->id)
                );
            }

            $owner = User::find($property->owner_id);
            if ($owner) {
                $tenantName = $tenantUser?->name ?? 'Tenant';
                $this->notificationService->send(
                    $owner,
                    'payment_late_owner',
                    "{$tenantName} has a late payment for Unit {$lease->unit->unit_number} — ₱".number_format($totalDue, 2).' (includes late fee).',
                    route('admin.payments.show', $payment->id)
                );
            }

            $count++;
        }

        return $count;
    }
}
