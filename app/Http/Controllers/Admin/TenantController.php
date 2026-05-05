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
    public function index(Request $request): View
    {
        $ownerId = auth()->id();

        $query = Tenant::where('owner_id', $ownerId)
            ->with(['user', 'leases' => function ($q) {
                $q->where('status', 'active')
                    ->with('unit.property');
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('leases', fn ($q) => $q->where('status', 'active'));
            } elseif ($request->status === 'none') {
                $query->whereDoesntHave('leases', fn ($q) => $q->where('status', 'active'));
            }
        }

        $sort = $request->get('sort', 'newest');

        switch ($sort) {
            case 'name_asc':
                $query->join('users', 'users.id', '=', 'tenants.user_id')
                    ->orderBy('users.name', 'asc')
                    ->select('tenants.*');
                break;
            case 'name_desc':
                $query->join('users', 'users.id', '=', 'tenants.user_id')
                    ->orderBy('users.name', 'desc')
                    ->select('tenants.*');
                break;
            case 'oldest':
                $query->orderBy('tenants.created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('tenants.created_at', 'desc');
                break;
        }

        $tenants = $query->paginate(10)->withQueryString();

        $baseQuery = Tenant::where('owner_id', $ownerId);
        $totalTenants = (clone $baseQuery)->count();
        $withLease = (clone $baseQuery)->whereHas('leases', fn ($q) => $q->where('status', 'active'))->count();
        $withoutLease = $totalTenants - $withLease;

        return view('admin.tenants.index', [
            'title' => 'Tenants',
            'tenants' => $tenants,
            'totalTenants' => $totalTenants,
            'withLease' => $withLease,
            'withoutLease' => $withoutLease,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Tenant $tenant): View
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->first();

        if (! $tenant) {
            abort(403);
        }

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
            'phone_number' => ['required', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => 'password',
                'role' => 'tenant',
                'must_change_password' => true,
            ]);

            Tenant::create([
                'user_id' => $user->id,
                'owner_id' => auth()->id(),
                'phone_number' => $validated['phone_number'],
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
                'province' => $validated['province'] ?? null,
                'city' => $validated['city'] ?? null,
                'barangay' => $validated['barangay'] ?? null,
                'address_line1' => $validated['address_line1'] ?? null,
                'address_line2' => $validated['address_line2'] ?? null,
            ]);
        });

        return back()->with('success', 'Tenant added successfully.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->first();

        if (! $tenant) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($tenant->user_id)],
            'phone_number' => ['required', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
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
                'province' => $validated['province'] ?? null,
                'city' => $validated['city'] ?? null,
                'barangay' => $validated['barangay'] ?? null,
                'address_line1' => $validated['address_line1'] ?? null,
                'address_line2' => $validated['address_line2'] ?? null,
            ]);
        });

        return back()->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($tenant->getKey())
            ->first();

        if (! $tenant) {
            abort(403);
        }

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
