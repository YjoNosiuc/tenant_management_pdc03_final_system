<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

    public function show(Property $property): View
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->firstOrFail();

        $property->load([
            'images',
            'units' => function ($q) {
                $q->with([
                    'images',
                    'leases' => function ($q) {
                        $q->where('status', 'active')->with('tenant.user');
                    },
                ])->orderBy('unit_number');
            },
        ]);

        $unitIds = $property->units->pluck('id');
        $totalUnitsCount = $property->units->count();
        $occupiedUnitsCount = $property->units->where('status', 'occupied')->count();
        $vacantUnitsCount = $property->units->where('status', 'vacant')->count();

        $activeLeasesCount = $unitIds->isEmpty()
            ? 0
            : (int) Lease::query()
                ->where('status', 'active')
                ->whereIn('unit_id', $unitIds)
                ->count();

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $revenueThisMonth = $unitIds->isEmpty()
            ? 0.0
            : (float) Payment::query()
                ->where('status', 'paid')
                ->whereHas('lease', fn ($q) => $q->whereIn('unit_id', $unitIds))
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('payment_date', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->whereNull('payment_date')->whereBetween('due_date', [$start, $end]);
                        });
                })
                ->sum('amount_paid');

        $occupancyPercent = $totalUnitsCount > 0
            ? (int) round(($occupiedUnitsCount / $totalUnitsCount) * 100)
            : 0;

        return view('admin.properties.show', [
            'title' => $property->name,
            'property' => $property,
            'activeLeasesCount' => $activeLeasesCount,
            'revenueThisMonth' => $revenueThisMonth,
            'occupancyPercent' => $occupancyPercent,
            'occupiedUnitsCount' => $occupiedUnitsCount,
            'vacantUnitsCount' => $vacantUnitsCount,
            'totalUnitsCount' => $totalUnitsCount,
            'unreadNotificationCount' => $this->unreadNotificationCount(),
        ]);
    }

    public function uploadImages(Request $request, Property $property): RedirectResponse
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->firstOrFail();

        $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $currentCount = $property->images()->count();
        $newCount = count($request->file('images', []));

        if ($currentCount + $newCount > 5) {
            return back()->with('error', "You can only upload up to 5 images. You currently have {$currentCount} image(s).");
        }

        foreach ($request->file('images') as $index => $image) {
            $path = Storage::disk('public')->putFile('properties', $image);
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
                'order' => $currentCount + $index,
            ]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function deleteImage(Property $property, PropertyImage $image): RedirectResponse
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->firstOrFail();

        if ((int) $image->property_id !== (int) $property->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted.');
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

    public function updateLateFee(Request $request, Property $property): RedirectResponse
    {
        $property = Property::query()
            ->where('owner_id', auth()->id())
            ->whereKey($property->getKey())
            ->firstOrFail();

        $request->validate([
            'late_fee_type' => ['required', 'in:fixed,percentage'],
            'late_fee_value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $property->update([
            'late_fee_type' => $request->late_fee_type,
            'late_fee_value' => $request->late_fee_value,
        ]);

        return back()->with('success', 'Late fee settings updated successfully.');
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
