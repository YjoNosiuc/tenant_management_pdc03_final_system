<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $ownerId = (int) auth()->id();

        $properties = Property::query()
            ->where('owner_id', $ownerId)
            ->orderBy('name')
            ->get();

        $propertyId = $request->filled('property_id') ? (int) $request->property_id : null;
        if ($propertyId && ! $properties->contains('id', $propertyId)) {
            return redirect()->route('admin.units.index', $request->except(['property_id']));
        }

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;
        if ($status !== null && ! in_array($status, ['vacant', 'occupied'], true)) {
            return redirect()->route('admin.units.index', $request->except('status'));
        }

        $query = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', $ownerId))
            ->with(['property', 'images']);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhereHas('property',
                        fn ($q) => $q->where('name', 'like', "%{$search}%")
                    );
            });
        }

        match ($request->get('sort', 'unit_asc')) {
            'rent_low' => $query->orderBy('rent_price', 'asc'),
            'rent_high' => $query->orderBy('rent_price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('unit_number', 'asc'),
        };

        $units = $query->paginate(12)->withQueryString();

        $baseQuery = Unit::query()->whereHas('property', fn ($q) => $q->where('owner_id', $ownerId));

        $totalUnits = (clone $baseQuery)->count();
        $occupiedUnits = (clone $baseQuery)->where('status', 'occupied')->count();
        $vacantUnits = (clone $baseQuery)->where('status', 'vacant')->count();
        $averageRent = (float) ((clone $baseQuery)->avg('rent_price') ?? 0);

        $allProperties = $properties;

        return view('admin.units.index', [
            'title' => 'Units',
            'units' => $units,
            'properties' => $properties,
            'allProperties' => $allProperties,
            'totalUnits' => $totalUnits,
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'averageRent' => $averageRent,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function show(Unit $unit): View
    {
        $unit = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($unit->getKey())
            ->with([
                'images',
                'property',
                'leases' => fn ($q) => $q->with('tenant.user')->orderByDesc('start_date'),
            ])
            ->first();

        if (! $unit) {
            abort(403);
        }

        $recentPayments = Payment::query()
            ->whereHas('lease', fn ($q) => $q->where('unit_id', $unit->id))
            ->with(['lease.tenant.user'])
            ->orderByDesc('due_date')
            ->limit(5)
            ->get();

        $activeLease = $unit->leases->firstWhere('status', 'active');

        return view('admin.units.show', [
            'title' => 'Unit '.$unit->unit_number,
            'unit' => $unit,
            'recentPayments' => $recentPayments,
            'activeLease' => $activeLease,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function uploadImages(Request $request, Unit $unit): RedirectResponse
    {
        $unit = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($unit->getKey())
            ->first();

        if (! $unit) {
            abort(403);
        }

        $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $currentCount = $unit->images()->count();
        $newCount = count($request->file('images', []));

        if ($currentCount + $newCount > 5) {
            return back()->with('error', "You can only upload up to 5 images. You currently have {$currentCount} image(s).");
        }

        foreach ($request->file('images') as $index => $image) {
            $path = Storage::disk('public')->putFile('units', $image);
            UnitImage::create([
                'unit_id' => $unit->id,
                'image_path' => $path,
                'order' => $currentCount + $index,
            ]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function deleteImage(Unit $unit, UnitImage $image): RedirectResponse
    {
        $unit = Unit::query()
            ->whereHas('property', fn ($q) => $q->where('owner_id', auth()->id()))
            ->whereKey($unit->getKey())
            ->first();

        if (! $unit) {
            abort(403);
        }

        if ((int) $image->unit_id !== (int) $unit->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_number' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', 'in:Studio,1BR,2BR,3BR'],
            'rent_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:vacant,occupied'],
        ]);

        Property::query()
            ->whereKey($validated['property_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        Unit::create($validated);

        if ($request->input('_form') === 'create_unit_property') {
            $property = Property::query()
                ->where('owner_id', auth()->id())
                ->findOrFail($validated['property_id']);

            return redirect()
                ->route('admin.properties.show', $property)
                ->with('success', 'Unit added successfully.');
        }

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
            'unit_type' => ['required', 'in:Studio,1BR,2BR,3BR'],
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
