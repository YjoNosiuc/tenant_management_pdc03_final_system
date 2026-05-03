<?php

namespace App\Exports;

use App\Exports\Sheets\IncomeSheet;
use App\Exports\Sheets\LatePaymentsSheet;
use App\Exports\Sheets\OccupancySheet;
use App\Exports\Sheets\PropertyBreakdownSheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private int $ownerId,
        private Carbon $startDate,
        private Carbon $endDate
    ) {}

    public function sheets(): array
    {
        return [
            new IncomeSheet($this->ownerId, $this->startDate, $this->endDate),
            new PropertyBreakdownSheet($this->ownerId, $this->startDate, $this->endDate),
            new OccupancySheet($this->ownerId, $this->startDate, $this->endDate),
            new LatePaymentsSheet($this->ownerId, $this->startDate, $this->endDate),
        ];
    }
}
