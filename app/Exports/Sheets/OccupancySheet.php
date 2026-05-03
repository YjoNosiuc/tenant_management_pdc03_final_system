<?php

namespace App\Exports\Sheets;

use App\Models\Lease;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OccupancySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $ownerId,
        private Carbon $startDate,
        private Carbon $endDate
    ) {}

    public function title(): string
    {
        return 'Occupancy Trend';
    }

    public function headings(): array
    {
        return ['Month', 'Active Leases', 'Total Units', 'Occupancy Rate (%)'];
    }

    public function array(): array
    {
        $totalUnits = Unit::whereHas('property',
            fn ($q) => $q->where('owner_id', $this->ownerId)
        )->count();

        $rows = [];
        $current = $this->startDate->copy()->startOfMonth();

        while ($current->lte($this->endDate)) {
            $activeLeases = Lease::whereHas('unit.property',
                fn ($q) => $q->where('owner_id', $this->ownerId)
            )
                ->where('status', 'active')
                ->where('start_date', '<=', $current->copy()->endOfMonth())
                ->where('end_date', '>=', $current->copy()->startOfMonth())
                ->count();

            $rows[] = [
                $current->format('F Y'),
                $activeLeases,
                $totalUnits,
                $totalUnits > 0
                    ? round(($activeLeases / $totalUnits) * 100).'%'
                    : '0%',
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
