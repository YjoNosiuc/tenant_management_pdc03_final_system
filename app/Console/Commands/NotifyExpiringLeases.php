<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyExpiringLeases extends Command
{
    protected $signature = 'leases:notify-expiring';

    protected $description = 'Notify tenants whose lease expires in 30 days';

    public function handle(): void
    {
        $expiringLeases = Lease::where('status', 'active')
            ->whereDate('end_date', now()->addDays(30)->toDateString())
            ->with(['tenant.user', 'unit.property'])
            ->get();

        foreach ($expiringLeases as $lease) {
            $tenantUser = $lease->tenant?->user;
            if (! $tenantUser) {
                continue;
            }

            $alreadySent = DB::table('notifications')
                ->where('user_id', $tenantUser->id)
                ->where('type', 'lease_expiring')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            (new NotificationService)->send(
                $tenantUser,
                'lease_expiring',
                "Your lease for Unit {$lease->unit->unit_number} at {$lease->unit->property->name} expires on {$lease->end_date->format('M d, Y')}. Please contact your landlord.",
                route('tenant.dashboard')
            );
        }

        $this->info("Notified {$expiringLeases->count()} expiring leases.");
    }
}
