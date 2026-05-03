<?php

namespace App\Http\Middleware;

use App\Models\OwnerTerms;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTermsAgreement
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        if (auth()->user()->role !== 'tenant') {
            return $next($request);
        }

        if ($request->routeIs(
            'terms.show',
            'terms.agree',
            'password.change',
            'password.change.update',
            'logout'
        )) {
            return $next($request);
        }

        $user = auth()->user();
        $tenant = $user->tenant;

        if (! $tenant) {
            return $next($request);
        }

        $activeLease = $tenant->leases()
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeLease) {
            return $next($request);
        }

        $ownerId = $activeLease->unit->property->owner_id;

        $terms = OwnerTerms::where('owner_id', $ownerId)->first();

        if (! $terms) {
            return $next($request);
        }

        $hasAgreed = $tenant->hasAgreedToTerms($ownerId, $terms->version);

        if (! $hasAgreed) {
            return redirect()->route('terms.show');
        }

        return $next($request);
    }
}
