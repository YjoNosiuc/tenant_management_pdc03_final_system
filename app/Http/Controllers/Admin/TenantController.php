<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $activeLeaseExists = function ($query): void {
            $query->where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now());
        };

        $tenants = Tenant::query()
            ->where('owner_id', auth()->id())
            ->with(['user', 'leases' => function ($query) {
                $query->where('status', 'active')->with('unit.property');
            }])
            ->join('users', 'users.id', '=', 'tenants.user_id')
            ->select('tenants.*')
            ->orderBy('users.name')
            ->paginate(10)
            ->withQueryString();

        $totalTenants = Tenant::where('owner_id', auth()->id())->count();
        $tenantsWithActiveLease = Tenant::where('owner_id', auth()->id())->whereHas('leases', $activeLeaseExists)->count();
        $tenantsWithoutActiveLease = Tenant::where('owner_id', auth()->id())->whereDoesntHave('leases', $activeLeaseExists)->count();

        return view('admin.tenants.index', [
            'title' => 'Tenants',
            'tenants' => $tenants,
            'totalTenants' => $totalTenants,
            'tenantsWithActiveLease' => $tenantsWithActiveLease,
            'tenantsWithoutActiveLease' => $tenantsWithoutActiveLease,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Tenant $tenant): View
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->firstOrFail();

        $tenant->load([
            'user',
            'leases' => fn ($query) => $query
                ->with(['unit.property'])
                ->orderByDesc('start_date'),
            'payments' => fn ($query) => $query->orderByDesc('due_date'),
        ]);

        return view('admin.tenants.show', [
            'title' => $tenant->user?->name ?? 'Tenant',
            'tenant' => $tenant,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['required', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'tenant',
            ]);

            Tenant::create([
                'user_id' => $user->id,
                'owner_id' => auth()->id(),
                'phone_number' => $validated['phone_number'],
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return back()->with('success', 'Tenant added successfully.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($tenant->user_id)],
            'phone_number' => ['required', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($tenant, $validated) {
            $tenant->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $tenant->update([
                'phone_number' => $validated['phone_number'],
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return back()->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->firstOrFail();

        $user = $tenant->user;

        DB::transaction(function () use ($tenant, $user) {
            $tenant->delete();

            $user->forceFill([
                'name' => 'Deleted Tenant',
                'email' => 'deleted-tenant-'.$user->id.'.'.Str::lower(Str::random(16)).'@invalid.local',
                'password' => Hash::make(Str::random(40)),
            ])->save();
        });

        return back()->with('success', 'Tenant deleted successfully.');
    }

    private function unreadNotificationCount(): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        $columns = Schema::getColumnListing('notifications');

        if (! in_array('user_id', $columns, true) || ! in_array('read_at', $columns, true)) {
            return 0;
        }

        return (int) DB::table('notifications')
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }
}
