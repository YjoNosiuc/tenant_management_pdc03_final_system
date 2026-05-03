<?php

namespace App\Exports\Sheets;

use App\Models\Property;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PropertyBreakdownSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $ownerId,
        private Carbon $startDate,
        private Carbon $endDate
    ) {}

    public function title(): string
    {
        return 'Property Breakdown';
    }

    public function headings(): array
    {
        return [
            'Property Name',
            'City',
            'Total Units',
            'Occupied',
            'Vacant',
            'Occupancy Rate (%)',
            'Total Collected (₱)',
        ];
    }

    public function array(): array
    {
        return Property::where('owner_id', $this->ownerId)
            ->with(['units.leases.payments' => function ($q) {
                $q->where('status', 'paid')
                    ->whereBetween('payment_date', [$this->startDate, $this->endDate]);
            }])
            ->get()
            ->map(function ($property) {
                $totalCollected = 0.0;
                foreach ($property->units as $unit) {
                    foreach ($unit->leases as $lease) {
                        foreach ($lease->payments as $payment) {
                            $totalCollected += (float) $payment->total_amount_due;
                        }
                    }
                }
                $total = $property->units->count();
                $occupied = $property->units->where('status', 'occupied')->count();

                return [
                    $property->name,
                    $property->city,
                    $total,
                    $occupied,
                    $total - $occupied,
                    $total > 0 ? round(($occupied / $total) * 100).'%' : '0%',
                    number_format($totalCollected, 2),
                ];
            })
            ->toArray();
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
