@extends('layouts.admin', ['title' => 'Units'])

@section('title', 'Units')

@section('content')
    @php
        $unitPathPrefix = parse_url(route('admin.units.index'), PHP_URL_PATH) ?: '/admin/units';
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            createOpen: false,
            editOpen: false,
            deleteOpen: false,
            selectedUnit: { id: null, property_id: '', unit_number: '', unit_type: '', rent_price: '', status: '' },
            deleteUnit: { id: null, unit_number: '' },
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }}
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 3000);
            @endif
            @if(old('_form') === 'create' && $errors->any())
                createOpen = true;
            @endif
            @if(old('_form') === 'edit' && $errors->any())
                editOpen = true;
                selectedUnit = {
                    id: {{ (int) old('edit_unit_id', 0) }},
                    property_id: @js((string) old('property_id', '')),
                    unit_number: @js(old('unit_number', '')),
                    unit_type: @js(old('unit_type', '')),
                    rent_price: @js(old('rent_price', '')),
                    status: @js(old('status', ''))
                };
            @endif
        "
    >
        {{-- Flash alerts --}}
        <div x-show="flashVisible" x-cloak class="space-y-3">
            @if(session('success'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm ring-1 ring-emerald-100 transition-opacity duration-300" role="alert">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="rounded-lg p-1 text-emerald-600 transition hover:bg-emerald-100" @click="flashVisible = false" aria-label="Dismiss">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm ring-1 ring-rose-100 transition-opacity duration-300" role="alert">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="rounded-lg p-1 text-rose-600 transition hover:bg-rose-100" @click="flashVisible = false" aria-label="Dismiss">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        </div>

        {{-- Page header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Units</h1>
                </div>
                <p class="mt-1 pl-3 text-sm text-slate-500">Manage all rental units across your properties</p>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Units</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalUnits) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Across your portfolio</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Occupied Units</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($occupiedUnits) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Currently leased</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Vacant Units</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($vacantUnits) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Available to list</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Average Rent</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-indigo-700">₱{{ number_format($averageRent, 2) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Across all units</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.units.index') }}">
            <div class="mb-6 flex flex-col flex-wrap gap-3 lg:flex-row lg:items-center">

                {{-- Search --}}
                <div class="relative w-full max-w-xs lg:flex-1 lg:max-w-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search units..."
                        class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm placeholder:text-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                </div>

                {{-- Property Filter --}}
                <select name="property_id"
                    onchange="this.form.submit()"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 lg:w-auto lg:min-w-[10rem]">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}"
                            {{ request('property_id') == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Status Filter --}}
                <select name="status"
                    onchange="this.form.submit()"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 lg:w-auto">
                    <option value="">All Statuses</option>
                    <option value="vacant"   {{ request('status') === 'vacant'   ? 'selected' : '' }}>Vacant</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                </select>

                {{-- Sort --}}
                <select name="sort"
                    onchange="this.form.submit()"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 lg:w-auto">
                    <option value="unit_asc"    {{ request('sort', 'unit_asc') === 'unit_asc'    ? 'selected' : '' }}>Unit # A–Z</option>
                    <option value="rent_low"    {{ request('sort') === 'rent_low'    ? 'selected' : '' }}>Rent Low–High</option>
                    <option value="rent_high"   {{ request('sort') === 'rent_high'   ? 'selected' : '' }}>Rent High–Low</option>
                    <option value="newest"      {{ request('sort') === 'newest'      ? 'selected' : '' }}>Newest First</option>
                </select>

                {{-- Add Unit button --}}
                <button type="button"
                    @click="createOpen = true"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 active:scale-[0.98] transition-all duration-200 whitespace-nowrap lg:ml-auto lg:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Unit
                </button>
            </div>

            {{-- Active filters --}}
            @if(request()->hasAny(['search', 'property_id', 'status']) || (request()->filled('sort') && request('sort') !== 'unit_asc'))
                <div class="mb-4 flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Filters:</span>
                    @if(request('property_id'))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                            {{ $properties->firstWhere('id', request('property_id'))?->name }}
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                            {{ ucfirst(request('status')) }}
                        </span>
                    @endif
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                            "{{ request('search') }}"
                        </span>
                    @endif
                    <a href="{{ route('admin.units.index') }}"
                       class="inline-flex items-center gap-1 rounded-lg bg-rose-50 border border-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </a>
                </div>
            @endif
        </form>

        {{-- Results count --}}
        <div class="mb-4">
            <p class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-700">{{ $units->total() }}</span> units
                @if(request()->hasAny(['search', 'property_id', 'status']))
                    <span class="text-indigo-600 font-medium">(filtered)</span>
                @endif
            </p>
        </div>

        @if($units->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <p class="text-base font-semibold text-slate-700">No units found</p>
                <p class="text-sm text-slate-400 mt-1">Try adjusting your filters or add a new unit.</p>
                <button type="button" @click="createOpen = true"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition-all">
                    + Add Unit
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($units as $unit)
                    @php
                        $firstImage = $unit->images->first();
                        $statusConfig = [
                            'occupied' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
                            'vacant'   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
                        ];
                        $dotConfig = [
                            'occupied' => 'bg-emerald-400',
                            'vacant'   => 'bg-amber-400',
                        ];
                        $cfg = $statusConfig[$unit->status] ?? $statusConfig['vacant'];
                        $dot = $dotConfig[$unit->status] ?? $dotConfig['vacant'];
                    @endphp

                    <div class="group relative flex flex-col rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">

                        <a href="{{ route('admin.units.show', $unit) }}" class="block rounded-t-2xl overflow-hidden">
                            <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                                @if($firstImage)
                                    <img src="{{ Storage::url($firstImage->image_path) }}"
                                         class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                         alt="Unit {{ $unit->unit_number }}" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-slate-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur-sm {{ $cfg }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
                                        {{ ucfirst($unit->status) }}
                                    </span>
                                </div>

                                @if($unit->images->count() > 0)
                                    <div class="absolute bottom-2 left-2 flex items-center gap-1 rounded-lg bg-black/50 px-2 py-1 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        <span class="text-xs font-medium text-white">{{ $unit->images->count() }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="pointer-events-none absolute inset-x-0 top-0 z-10 flex h-48 justify-end p-2">
                            <div class="pointer-events-auto flex items-start gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button type="button"
                                    @click.stop="selectedUnit = { id: {{ $unit->id }}, property_id: '{{ $unit->property_id }}', unit_number: @js((string) $unit->unit_number), unit_type: @js((string) $unit->unit_type), rent_price: @js((string) $unit->rent_price), status: @js((string) $unit->status) }; editOpen = true"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-slate-600 shadow-sm hover:bg-white hover:text-indigo-600 transition-all backdrop-blur-sm"
                                    title="Edit"
                                    aria-label="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button type="button"
                                    @click.stop="deleteUnit = { id: {{ $unit->id }}, unit_number: @js((string) $unit->unit_number) }; deleteOpen = true"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-slate-600 shadow-sm hover:bg-white hover:text-red-600 transition-all backdrop-blur-sm"
                                    title="Delete"
                                    aria-label="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('admin.units.show', $unit) }}"
                           class="flex flex-1 flex-col p-5">

                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-slate-900 text-lg leading-tight group-hover:text-indigo-600 transition-colors duration-150">
                                    Unit {{ $unit->unit_number }}
                                </h3>
                                <span class="shrink-0 inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                    {{ $unit->unit_type }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-400 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                                </svg>
                                {{ $unit->property?->name }}
                            </p>

                            <div class="my-3 border-t border-slate-100"></div>

                            <div class="flex items-baseline gap-1">
                                <span class="text-xl font-extrabold text-indigo-600">
                                    ₱{{ number_format($unit->rent_price, 0) }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">/ month</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $units->links() }}
            </div>
        @endif

        {{-- Create modal --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" aria-labelledby="unit-create-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="createOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                </div>
                                <div>
                                    <h3 id="unit-create-title" class="text-base font-bold text-white">Add unit</h3>
                                    <p class="text-xs text-indigo-200">Link a unit to a property and set its listing details.</p>
                                </div>
                            </div>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white transition-all hover:bg-white/20" @click="createOpen = false" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.units.store') }}">
                        @csrf
                        <input type="hidden" name="_form" value="create">
                        <div class="space-y-4 px-6 py-5">
                            <div>
                                <label for="create-property-id" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-9H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    Property <span class="text-rose-500">*</span>
                                </label>
                                <select id="create-property-id" name="property_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('property_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="" disabled {{ old('_form') === 'create' ? '' : 'selected' }} hidden>Select property</option>
                                    @foreach($allProperties as $property)
                                        <option value="{{ $property->id }}" @selected(old('_form') === 'create' && (string) old('property_id') === (string) $property->id)>{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-unit-number" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                    Unit Number <span class="text-rose-500">*</span>
                                </label>
                                <input id="create-unit-number" name="unit_number" type="text" value="{{ old('_form') === 'create' ? old('unit_number') : '' }}" required placeholder="e.g. 101, 2A" class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_number') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('unit_number')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-unit-type" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                    Unit Type <span class="text-rose-500">*</span>
                                </label>
                                <select id="create-unit-type" name="unit_type" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_type') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="" disabled {{ old('_form') === 'create' && old('unit_type') ? '' : 'selected' }}>Select unit type</option>
                                    <option value="Studio" @selected(old('_form') === 'create' && old('unit_type') === 'Studio')>Studio</option>
                                    <option value="1BR" @selected(old('_form') === 'create' && old('unit_type') === '1BR')>1BR</option>
                                    <option value="2BR" @selected(old('_form') === 'create' && old('unit_type') === '2BR')>2BR</option>
                                    <option value="3BR" @selected(old('_form') === 'create' && old('unit_type') === '3BR')>3BR</option>
                                </select>
                                @error('unit_type')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-rent-price" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Rent Price <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="create-rent-price" name="rent_price" type="number" step="0.01" min="0" value="{{ old('_form') === 'create' ? old('rent_price') : '' }}" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('rent_price') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('rent_price')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="create-status" name="status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="vacant" @selected(old('_form') === 'create' && old('status') === 'vacant')>Vacant</option>
                                    <option value="occupied" @selected(old('_form') === 'create' && old('status') === 'occupied')>Occupied</option>
                                </select>
                                @error('status')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="createOpen = false">Cancel</button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:opacity-90 active:scale-[0.98]">Add Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" aria-labelledby="unit-edit-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                </div>
                                <div>
                                    <h3 id="unit-edit-title" class="text-base font-bold text-white">Edit unit</h3>
                                    <p class="text-xs text-indigo-200">Update unit details shown in your admin records.</p>
                                </div>
                            </div>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white transition-all hover:bg-white/20" @click="editOpen = false" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <form method="POST" x-bind:action="'{{ $unitPathPrefix }}/' + selectedUnit.id">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit">
                        <input type="hidden" name="edit_unit_id" x-bind:value="selectedUnit.id" />
                        <div class="space-y-4 px-6 py-5">
                            <div>
                                <label for="edit-property-id" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-9H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    Property <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit-property-id" name="property_id" x-model="selectedUnit.property_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('property_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-unit-number" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                    Unit Number <span class="text-rose-500">*</span>
                                </label>
                                <input id="edit-unit-number" name="unit_number" type="text" x-model="selectedUnit.unit_number" required placeholder="e.g. 101, 2A" class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_number') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('unit_number')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-unit-type" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                    Unit Type <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit-unit-type" name="unit_type" x-model="selectedUnit.unit_type" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_type') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="">Select unit type</option>
                                    <option value="Studio">Studio</option>
                                    <option value="1BR">1BR</option>
                                    <option value="2BR">2BR</option>
                                    <option value="3BR">3BR</option>
                                </select>
                                @error('unit_type')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-rent-price" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Rent Price <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="edit-rent-price" name="rent_price" type="number" step="0.01" min="0" x-model="selectedUnit.rent_price" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('rent_price') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('rent_price')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit-status" name="status" x-model="selectedUnit.status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="vacant">Vacant</option>
                                    <option value="occupied">Occupied</option>
                                </select>
                                @error('status')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:opacity-90 active:scale-[0.98]">Update Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete modal --}}
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" aria-labelledby="unit-delete-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="deleteOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white p-8 text-center shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                    <h3 id="unit-delete-title" class="mt-5 text-xl font-bold text-slate-900">Delete unit?</h3>
                    <p class="mt-2 text-sm text-slate-500">This action cannot be undone.</p>
                    <p class="mt-1 text-sm text-slate-600">Unit <span class="font-semibold text-slate-900" x-text="deleteUnit.unit_number"></span> will be soft-deleted.</p>
                    <div class="mt-8 flex flex-col-reverse items-stretch justify-center gap-3 sm:flex-row sm:items-center">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="deleteOpen = false">Cancel</button>
                        <form method="POST" x-bind:action="'{{ $unitPathPrefix }}/' + deleteUnit.id" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:opacity-90 active:scale-[0.98] sm:w-auto">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
