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

class PropertyController extends Controller
{
    public function index(): View
    {
        $properties = Property::query()
            ->where('owner_id', auth()->id())
            ->withCount('units')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalProperties = Property::where('owner_id', auth()->id())->count();
        $totalUnits = Unit::whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))->count();
        $vacantUnits = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->where('status', 'vacant')
            ->count();

        return view('admin.properties.index', [
            'title' => 'Properties',
            'properties' => $properties,
            'totalProperties' => $totalProperties,
            'totalUnits' => $totalUnits,
            'vacantUnits' => $vacantUnits,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        Property::create($validated + ['owner_id' => auth()->id()]);

        return back()->with('success', 'Property added successfully.');
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->first();

        if (! $property) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $property->update($validated);

        return back()->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->first();

        if (! $property) {
            abort(403);
        }

        $property->delete();

        return back()->with('success', 'Property deleted successfully.');
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
