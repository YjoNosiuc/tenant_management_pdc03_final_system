<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\OwnerTerms;
use App\Models\TenantTermAgreement;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return redirect()->route('tenant.dashboard');
        }

        $activeLease = $tenant->leases()
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeLease) {
            return redirect()->route('tenant.dashboard');
        }

        $ownerId = $activeLease->unit->property->owner_id;
        $terms = OwnerTerms::where('owner_id', $ownerId)->first();

        if (! $terms) {
            return redirect()->route('tenant.dashboard');
        }

        if ($tenant->hasAgreedToTerms($ownerId, $terms->version)) {
            return redirect()->route('tenant.dashboard');
        }

        return view('tenant.terms', compact('terms', 'activeLease'));
    }

    public function agree(Request $request)
    {
        $request->validate([
            'agreed' => ['required', 'accepted'],
        ]);

        $user = auth()->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return redirect()->route('tenant.dashboard');
        }

        $activeLease = $tenant->leases()
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeLease) {
            return redirect()->route('tenant.dashboard');
        }

        $ownerId = $activeLease->unit->property->owner_id;
        $terms = OwnerTerms::where('owner_id', $ownerId)->first();

        if (! $terms) {
            return redirect()->route('tenant.dashboard');
        }

        TenantTermAgreement::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'owner_id' => $ownerId,
            ],
            [
                'version' => $terms->version,
                'agreed_at' => now(),
            ]
        );

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Thank you for agreeing to the terms and conditions!');
    }
}
