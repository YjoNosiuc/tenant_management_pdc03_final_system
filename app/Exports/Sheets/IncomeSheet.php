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

class IncomeSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $ownerId,
        private Carbon $startDate,
        private Carbon $endDate
    ) {}

    public function title(): string
    {
        return 'Monthly Income';
    }

    public function headings(): array
    {
        return ['Month', 'Amount Collected (₱)', 'Number of Payments'];
    }

    public function array(): array
    {
        $rows = [];
        $current = $this->startDate->copy()->startOfMonth();

        while ($current->lte($this->endDate)) {
            $payments = Payment::whereHas('lease.unit.property',
                fn ($q) => $q->where('owner_id', $this->ownerId)
            )
                ->where('status', 'paid')
                ->whereYear('payment_date', $current->year)
                ->whereMonth('payment_date', $current->month)
                ->get();

            $rows[] = [
                $current->format('F Y'),
                number_format((float) $payments->sum('total_amount_due'), 2),
                $payments->count(),
            ];

            $current->addMonth();
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
