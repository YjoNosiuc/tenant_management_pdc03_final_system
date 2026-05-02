<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\LeasePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaseController extends Controller
{
    public function index(): View
    {
        $leases = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->with(['tenant.user', 'unit.property'])
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        $tenants = $this->tenantsForSelect();
        $units = $this->vacantUnitsForSelect();
        $unitsForEdit = $this->unitsForEditSelect();

        $activeLeases = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'active')
            ->count();
        $completedLeases = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'completed')
            ->count();
        $terminatedLeases = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'terminated')
            ->count();

        return view('admin.leases.index', [
            'title' => 'Leases',
            'leases' => $leases,
            'tenants' => $tenants,
            'units' => $units,
            'unitsForEdit' => $unitsForEdit,
            'activeLeases' => $activeLeases,
            'completedLeases' => $completedLeases,
            'terminatedLeases' => $terminatedLeases,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Lease $lease): View
    {
        $lease = $this->ownedLeaseOrAbort($lease);

        $lease->load([
            'tenant.user',
            'unit.property',
            'payments' => fn ($q) => $q->orderByDesc('due_date'),
        ]);

        return view('admin.leases.show', [
            'title' => 'Lease Details',
            'lease' => $lease,
            'tenants' => $this->tenantsForSelect(),
            'unitsForEdit' => $this->unitsForEditSelect(),
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedLeasePayload($request);

        Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($validated['tenant_id'])
            ->firstOrFail();

        Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($validated['unit_id'])
            ->firstOrFail();

        $this->assertNoConflictingActiveLease((int) $validated['unit_id'], null, (string) $validated['status']);

        DB::transaction(function () use ($validated) {
            $lease = Lease::create($validated);
            (new LeasePaymentService)->generatePayments($lease);
            $this->syncUnitForLease($lease);
        });

        return back()->with('success', 'Lease created successfully.');
    }

    public function update(Request $request, Lease $lease): RedirectResponse
    {
        $lease = $this->ownedLeaseOrAbort($lease);

        $validated = $this->validatedLeasePayload($request);

        Tenant::query()
            ->where('owner_id', auth()->id())
            ->whereKey($validated['tenant_id'])
            ->firstOrFail();

        Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($validated['unit_id'])
            ->firstOrFail();

        $this->assertNoConflictingActiveLease((int) $validated['unit_id'], $lease->id, (string) $validated['status']);

        $previousUnitId = (int) $lease->unit_id;
        $previousStatus = (string) $lease->status;

        DB::transaction(function () use ($lease, $validated, $previousUnitId, $previousStatus) {
            $lease->update($validated);
            $lease->refresh();
            (new LeasePaymentService)->generatePayments($lease);
            $this->syncUnitAfterLeaseUpdate($lease, $previousUnitId, $previousStatus);
        });

        return back()->with('success', 'Lease updated successfully.');
    }

    public function destroy(Lease $lease): RedirectResponse
    {
        $lease = $this->ownedLeaseOrAbort($lease);

        $unitId = (int) $lease->unit_id;

        $lease->delete();

        $this->vacateUnitIfNoActiveLease($unitId);

        return back()->with('success', 'Lease deleted successfully.');
    }

    public function uploadContract(Request $request, Lease $lease): RedirectResponse
    {
        $lease = $this->ownedLeaseOrAbort($lease);

        $request->validate([
            'contract' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx',
                'max:10240',
            ],
        ]);

        if ($lease->contract_path) {
            Storage::disk('public')->delete($lease->contract_path);
        }

        $path = Storage::disk('public')->putFile(
            'contracts',
            $request->file('contract')
        );

        $lease->update([
            'contract_path' => $path,
            'contract_uploaded_at' => now(),
        ]);

        return back()->with('success', 'Contract uploaded successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedLeasePayload(Request $request): array
    {
        if ($request->input('deposit_amount') === '') {
            $request->merge(['deposit_amount' => null]);
        }

        if ($request->routeIs('admin.leases.store')) {
            $request->mergeIfMissing(['inclusions' => []]);
        }

        if ($request->routeIs('admin.leases.update') && $request->input('_form') === 'edit') {
            $request->mergeIfMissing(['inclusions' => []]);
        }

        $inclusionChoices = ['Water', 'Electricity', 'Internet/WiFi', 'Parking', 'Cable TV', 'Trash Collection'];

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_status' => ['required', 'in:held,returned,forfeited'],
            'status' => ['required', 'in:active,completed,terminated'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'inclusions' => ['sometimes', 'nullable', 'array'],
            'inclusions.*' => ['string', Rule::in($inclusionChoices)],
        ]);

        if ($request->routeIs('admin.leases.store')) {
            $validated['inclusions'] = array_values($validated['inclusions'] ?? []);
        }

        if ($request->routeIs('admin.leases.update')) {
            if ($request->input('_form') === 'edit') {
                $validated['inclusions'] = array_values($validated['inclusions'] ?? []);
            } elseif (array_key_exists('inclusions', $validated)) {
                $validated['inclusions'] = array_values($validated['inclusions'] ?? []);
            }
        }

        return $validated;
    }

    private function assertNoConflictingActiveLease(int $unitId, ?int $ignoreLeaseId, string $status): void
    {
        if ($status !== 'active') {
            return;
        }

        $query = Lease::query()->where('unit_id', $unitId)->where('status', 'active');
        if ($ignoreLeaseId) {
            $query->where('id', '!=', $ignoreLeaseId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'unit_id' => 'This unit already has an active lease.',
            ]);
        }
    }

    private function syncUnitForLease(Lease $lease): void
    {
        if ($lease->status === 'active') {
            Unit::whereKey($lease->unit_id)->update(['status' => 'occupied']);

            return;
        }

        if (in_array($lease->status, ['completed', 'terminated'], true)) {
            Unit::whereKey($lease->unit_id)->update(['status' => 'vacant']);
        }
    }

    private function syncUnitAfterLeaseUpdate(Lease $lease, int $previousUnitId, string $previousStatus): void
    {
        if ($lease->status === 'active') {
            Unit::whereKey($lease->unit_id)->update(['status' => 'occupied']);

            if ($previousUnitId !== (int) $lease->unit_id && $previousStatus === 'active') {
                $this->vacateUnitIfNoActiveLease($previousUnitId);
            }

            return;
        }

        if (in_array($lease->status, ['completed', 'terminated'], true)) {
            Unit::whereKey($lease->unit_id)->update(['status' => 'vacant']);

            if ($previousUnitId !== (int) $lease->unit_id && $previousStatus === 'active') {
                $this->vacateUnitIfNoActiveLease($previousUnitId);
            }
        }
    }

    private function vacateUnitIfNoActiveLease(int $unitId): void
    {
        $hasActive = Lease::query()
            ->where('unit_id', $unitId)
            ->where('status', 'active')
            ->exists();

        if (! $hasActive) {
            Unit::whereKey($unitId)->update(['status' => 'vacant']);
        }
    }

    private function tenantsForSelect(): Collection
    {
        return Tenant::query()
            ->where('owner_id', auth()->id())
            ->with('user')
            ->get()
            ->sortBy(fn (Tenant $tenant) => strtolower($tenant->user?->name ?? ''))
            ->values();
    }

    private function vacantUnitsForSelect(): Collection
    {
        return Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->with('property')
            ->where('status', 'vacant')
            ->orderBy('property_id')
            ->orderBy('unit_number')
            ->get();
    }

    private function unitsForEditSelect(): Collection
    {
        $vacant = $this->vacantUnitsForSelect();
        $leasedUnitIds = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->pluck('unit_id')
            ->unique()
            ->filter()
            ->values();

        return $vacant->merge(
            Unit::query()
                ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
                ->with('property')
                ->whereIn('id', $leasedUnitIds)
                ->orderBy('property_id')
                ->orderBy('unit_number')
                ->get()
        )->unique('id')->values();
    }

    private function ownedLeaseOrAbort(Lease $lease): Lease
    {
        $owned = Lease::query()
            ->whereHas('unit.property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($lease->getKey())
            ->first();

        if (! $owned) {
            abort(403);
        }

        return $owned;
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
