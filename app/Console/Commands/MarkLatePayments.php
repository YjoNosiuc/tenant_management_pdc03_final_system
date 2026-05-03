<?php

namespace App\Console\Commands;

use App\Services\LatePaymentService;
use Illuminate\Console\Command;

class MarkLatePayments extends Command
{
    protected $signature = 'payments:mark-late';

    protected $description = 'Mark overdue payments as late and apply late fees';

    public function handle(LatePaymentService $latePaymentService): int
    {
        $count = $latePaymentService->markOverduePayments();
        $this->info("Marked {$count} payment(s) as late.");

        return self::SUCCESS;
    }
}
