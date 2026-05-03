<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Payment;
use Carbon\Carbon;

class LeasePaymentService
{
    public function generatePayments(Lease $lease): void
    {
        Payment::where('lease_id', $lease->id)
            ->whereIn('status', ['pending', 'late', 'rejected'])
            ->delete();

        $lease->refresh();

        $startDate = Carbon::parse($lease->start_date);
        $endDate = Carbon::parse($lease->end_date);

        $dueDate = $startDate->copy()->addMonth();

        $payments = [];

        while ($dueDate->lte($endDate)) {
            $dueStr = $dueDate->format('Y-m-d');

            $hasKeptPayment = Payment::query()
                ->where('lease_id', $lease->id)
                ->whereDate('due_date', $dueStr)
                ->exists();

            if (! $hasKeptPayment) {
                $amount = number_format((float) $lease->monthly_rent, 2, '.', '');
                $payments[] = [
                    'lease_id' => $lease->id,
                    'amount_paid' => $amount,
                    'late_fee_amount' => 0,
                    'total_amount_due' => $amount,
                    'due_date' => $dueStr,
                    'payment_date' => null,
                    'payment_method' => null,
                    'proof_of_payment' => null,
                    'verified_at' => null,
                    'status' => 'pending',
                    'remarks' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $dueDate = $dueDate->copy()->addMonth();
        }

        if ($payments !== []) {
            Payment::insert($payments);
        }
    }
}
