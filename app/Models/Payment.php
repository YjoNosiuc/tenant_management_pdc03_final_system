<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'lease_id',
        'amount_paid',
        'late_fee_amount',
        'total_amount_due',
        'due_date',
        'payment_date',
        'payment_method',
        'proof_of_payment',
        'verified_at',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function hasLateFee(): bool
    {
        return (float) $this->late_fee_amount > 0;
    }

    /**
     * @return array{original_rent: float, late_fee_amount: float, total_amount_due: float, is_late: bool}
     */
    public function getBreakdown(): array
    {
        $original = (float) $this->amount_paid;
        $late = (float) $this->late_fee_amount;
        $total = $this->total_amount_due !== null
            ? (float) $this->total_amount_due
            : $original + $late;

        return [
            'original_rent' => $original,
            'late_fee_amount' => $late,
            'total_amount_due' => $total,
            'is_late' => $this->status === 'late',
        ];
    }
}
