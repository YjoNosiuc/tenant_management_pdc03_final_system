<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->with('property')
            ->orderBy('unit_number')
            ->paginate(10)
            ->withQueryString();

        $properties = Property::query()->where('owner_id', auth()->id())->orderBy('name')->get();

        $totalUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))->count();
        $occupiedUnits = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'occupied')
            ->count();
        $vacantUnits = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'vacant')
            ->count();
        $avgRent = (float) (Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->avg('rent_price') ?? 0);

        return view('admin.units.index', [
            'title' => 'Units',
            'units' => $units,
            'properties' => $properties,
            'totalUnits' => $totalUnits,
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'avgRent' => $avgRent,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_number' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', 'string', 'max:255'],
            'rent_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied'],
        ]);

        Property::query()
            ->whereKey($validated['property_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        Unit::create($validated);

        return back()->with('success', 'Unit added successfully.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($unit->getKey())
            ->first();

        if (! $unit) {
            abort(403);
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_number' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', 'string', 'max:255'],
            'rent_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied'],
        ]);

        Property::query()
            ->whereKey($validated['property_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $unit->update($validated);

        return back()->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($unit->getKey())
            ->first();

        if (! $unit) {
            abort(403);
        }

        $unit->delete();

        return back()->with('success', 'Unit deleted successfully.');
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
