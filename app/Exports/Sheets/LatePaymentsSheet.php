<?php

namespace App\Exports\Sheets;

use App\Models\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LatePaymentsSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $ownerId,
        private Carbon $startDate,
        private Carbon $endDate
    ) {}

    public function title(): string
    {
        return 'Late Payments';
    }

    public function headings(): array
    {
        return [
            'Tenant Name',
            'Property',
            'Unit',
            'Due Date',
            'Original Rent (₱)',
            'Late Fee (₱)',
            'Total Due (₱)',
            'Status',
        ];
    }

    public function array(): array
    {
        return Payment::whereHas('lease.unit.property',
            fn ($q) => $q->where('owner_id', $this->ownerId)
        )
            ->where('status', 'late')
            ->whereBetween('due_date', [$this->startDate, $this->endDate])
            ->with(['lease.tenant.user', 'lease.unit.property'])
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(fn ($payment) => [
                $payment->lease?->tenant?->user?->name ?? '—',
                $payment->lease?->unit?->property?->name ?? '—',
                'Unit '.($payment->lease?->unit?->unit_number ?? '—'),
                $payment->due_date?->format('M d, Y'),
                number_format((float) $payment->amount_paid, 2),
                number_format((float) $payment->late_fee_amount, 2),
                number_format((float) $payment->total_amount_due, 2),
                ucfirst((string) $payment->status),
            ])
            ->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
