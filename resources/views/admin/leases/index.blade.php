@extends('layouts.admin', ['title' => 'Leases'])

@section('title', 'Leases')

@section('content')
    @php
        $leasePathPrefix = parse_url(route('admin.leases.index'), PHP_URL_PATH) ?: '/admin/leases';
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            createOpen: false,
            editOpen: false,
            deleteOpen: false,
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            selectedLease: { id: null, tenant_id: '', unit_id: '', start_date: '', end_date: '', monthly_rent: '', deposit_amount: '', deposit_status: 'held', status: 'active', notes: '', inclusions: [] },
            deleteLease: { id: null, tenant_name: '' }
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
                selectedLease = {
                    id: {{ (int) old('edit_lease_id', 0) }},
                    tenant_id: @js((string) old('tenant_id', '')),
                    unit_id: @js((string) old('unit_id', '')),
                    start_date: @js(old('start_date', '')),
                    end_date: @js(old('end_date', '')),
                    monthly_rent: @js(old('monthly_rent', '')),
                    deposit_amount: @js(old('deposit_amount', '')),
                    deposit_status: @js(old('deposit_status', 'held')),
                    status: @js(old('status', 'active')),
                    notes: @js(old('notes', '')),
                    inclusions: @json(old('inclusions', []))
                };
            @endif
        "
    >
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Leases</h1>
                </div>
                <p class="mt-1 pl-3 text-sm text-slate-500">Track all rental agreements</p>
            </div>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-500 active:scale-[0.98]"
                @click="createOpen = true"
            >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add Lease
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active Leases</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($activeLeases) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Currently in force</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-blue-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Completed Leases</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($completedLeases) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Finished normally</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Terminated Leases</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($terminatedLeases) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Ended early</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50">
                        <svg class="h-6 w-6 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">All leases</h2>
                <p class="mt-1 text-sm text-slate-500">Filter by property, unit, status, or search by tenant or unit. All filters apply on the server.</p>
            </div>

            <form method="GET" action="{{ route('admin.leases.index') }}">
                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                    {{-- Property Filter --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="lease-filter-property_id">
                            Property
                        </label>
                        <select
                            id="lease-filter-property_id"
                            name="property_id"
                            onchange="const f=this.form; const u=f.querySelector('select[name=\'unit_id\']'); if(u) u.value=''; f.submit();"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">All Properties</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                    {{ $property->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unit Filter --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="lease-filter-unit_id">
                            Unit
                        </label>
                        <select
                            id="lease-filter-unit_id"
                            name="unit_id"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 {{ ! request('property_id') ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ ! request('property_id') ? 'disabled' : '' }}
                        >
                            <option value="">All Units</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    Unit {{ $unit->unit_number }} ({{ $unit->unit_type }})
                                </option>
                            @endforeach
                        </select>
                        @if(! request('property_id'))
                            <p class="mt-1 text-xs text-slate-400">Select a property first</p>
                        @endif
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="lease-filter-status">
                            Status
                        </label>
                        <select
                            id="lease-filter-status"
                            name="status"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="lease-filter-search">
                            Search
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input
                                id="lease-filter-search"
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Tenant name or unit..."
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                    </div>
                </div>

                @if(request()->filled('status') || request()->filled('property_id') || request()->filled('unit_id') || request()->filled('search'))
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Active filters:
                        </span>

                        @if(request('property_id'))
                            <span class="inline-flex items-center gap-1 rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                Property: {{ $properties->firstWhere('id', request('property_id'))?->name }}
                            </span>
                        @endif

                        @if(request('unit_id'))
                            <span class="inline-flex items-center gap-1 rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                Unit: {{ $units->firstWhere('id', request('unit_id'))?->unit_number }}
                            </span>
                        @endif

                        @if(request('status'))
                            <span class="inline-flex items-center gap-1 rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                Status: {{ ucfirst(request('status')) }}
                            </span>
                        @endif

                        @if(request('search'))
                            <span class="inline-flex items-center gap-1 rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                Search: "{{ request('search') }}"
                            </span>
                        @endif

                        <a
                            href="{{ route('admin.leases.index') }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 transition-all hover:bg-rose-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Clear all
                        </a>
                    </div>
                @endif
            </form>

            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing
                    <span class="font-semibold text-slate-700">{{ $leases->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-semibold text-slate-700">{{ $leases->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-semibold text-slate-700">{{ $leases->total() }}</span>
                    results
                    @if(request()->filled('status') || request()->filled('property_id') || request()->filled('unit_id') || request()->filled('search'))
                        <span class="font-medium text-indigo-600">(filtered)</span>
                    @endif
                </p>
            </div>

            @if($leases->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-indigo-400 shadow-sm ring-1 ring-slate-100">
                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <p class="mt-5 text-base font-semibold text-slate-800">No leases yet</p>
                    <p class="mt-2 max-w-md text-sm text-slate-500">Create a lease to connect a tenant with a unit, set rent and deposit, and track status from this list.</p>
                    <button type="button" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:scale-[0.98]" @click="createOpen = true">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Add Lease
                    </button>
                </div>
            @else
                <div class="overflow-hidden rounded-xl ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="divide-y divide-slate-100 bg-slate-50/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tenant</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Unit</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Duration</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Rent &amp; Deposit</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                    <th scope="col" class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 bg-white">
                                @foreach($leases as $lease)
                                    @php
                                        $tUser = $lease->tenant?->user;
                                        $unit = $lease->unit;
                                        $prop = $unit?->property;
                                        $tenantName = (string) ($tUser?->name ?? '');
                                        $tenantInitials = strtoupper(substr($tUser?->name ?? 'T', 0, 2));
                                        $unitNo = (string) ($unit?->unit_number ?? '');
                                        $unitTypeLabel = $unit?->unit_type
                                            ? str($unit->unit_type)->replace('_', ' ')->title()->toString()
                                            : '';
                                    @endphp
                                    <tr class="transition-colors duration-150 hover:bg-indigo-50/30">
                                        <td class="min-w-0 px-4 py-3.5 align-top">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">{{ $tenantInitials }}</div>
                                                <div>
                                                    <a href="{{ route('admin.leases.show', $lease) }}"
                                                       class="font-semibold text-slate-800 hover:text-indigo-600 transition-colors duration-150 hover:underline underline-offset-2">
                                                        {{ $tenantName ?: '—' }}
                                                    </a>
                                                    <p class="text-xs text-slate-400 mt-0.5">{{ $prop?->name ?? '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-w-0 px-4 py-3.5 align-top">
                                            <p class="font-semibold text-slate-800">{{ $unitNo ?: '—' }}</p>
                                            @if($unitTypeLabel !== '')
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $unitTypeLabel }}</p>
                                            @endif
                                        </td>
                                        <td class="min-w-0 px-4 py-3.5 align-top">
                                            <div class="flex flex-wrap items-center gap-1.5 text-sm text-slate-700">
                                                <span class="font-medium">{{ $lease->start_date?->format('M d, Y') ?? '—' }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                                <span class="font-medium">{{ $lease->end_date?->format('M d, Y') ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="min-w-0 px-4 py-3.5 align-top">
                                            <p class="text-sm font-bold text-indigo-700">₱{{ number_format((float) $lease->monthly_rent, 2) }} <span class="font-semibold text-slate-500">/ mo</span></p>
                                            @if($lease->deposit_amount !== null)
                                                <p class="text-xs text-slate-500 mt-0.5">Deposit: ₱{{ number_format((float) $lease->deposit_amount, 2) }}</p>
                                            @else
                                                <p class="text-xs text-slate-500 mt-0.5">No deposit</p>
                                            @endif
                                        </td>
                                        <td class="min-w-0 px-4 py-3.5 align-top">
                                            @if($lease->status === 'active')
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Active
                                                </span>
                                            @elseif($lease->status === 'completed')
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                    Completed
                                                </span>
                                            @elseif($lease->status === 'terminated')
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-100">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                    Terminated
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                    {{ ucfirst($lease->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5 align-top text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <a
                                                    href="{{ route('admin.leases.show', $lease) }}"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-150"
                                                    title="View Lease"
                                                    aria-label="View Lease"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                </a>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-all duration-150"
                                                    title="Edit"
                                                    aria-label="Edit"
                                                    @click="selectedLease = @js([
                                                        'id' => $lease->id,
                                                        'tenant_id' => (string) $lease->tenant_id,
                                                        'unit_id' => (string) $lease->unit_id,
                                                        'start_date' => $lease->start_date?->format('Y-m-d'),
                                                        'end_date' => $lease->end_date?->format('Y-m-d'),
                                                        'monthly_rent' => (string) $lease->monthly_rent,
                                                        'deposit_amount' => $lease->deposit_amount !== null ? (string) $lease->deposit_amount : '',
                                                        'deposit_status' => $lease->deposit_status,
                                                        'status' => $lease->status,
                                                        'notes' => $lease->notes ?? '',
                                                        'inclusions' => $lease->inclusions ?? [],
                                                    ]); editOpen = true"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-all duration-150"
                                                    title="Delete"
                                                    aria-label="Delete"
                                                    @click="deleteLease = { id: {{ $lease->id }}, tenant_name: @js($tenantName ?: 'this tenant') }; deleteOpen = true"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-6">
                    {{ $leases->links() }}
                </div>
            @endif
        </div>

        {{-- Create modal --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" role="dialog" aria-modal="true" aria-labelledby="lease-create-title">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="createOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <div>
                                    <h3 id="lease-create-title" class="text-base font-bold text-white">Add lease</h3>
                                    <p class="text-xs text-indigo-200">Assign a tenant to a vacant unit and set lease terms.</p>
                                </div>
                            </div>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white transition-all hover:bg-white/20" @click="createOpen = false" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.leases.store') }}">
                        @csrf
                        <input type="hidden" name="_form" value="create">
                        <div class="max-h-[calc(90vh-10rem)] space-y-4 overflow-y-auto px-6 py-5">
                            <div>
                                <label for="create-tenant" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    Tenant <span class="text-rose-500">*</span>
                                </label>
                                <select id="create-tenant" name="tenant_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('tenant_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="" disabled {{ old('_form') === 'create' && old('tenant_id') ? '' : 'selected' }}>Select tenant</option>
                                    @foreach($allTenants as $tenant)
                                        <option value="{{ $tenant->id }}" @selected(old('_form') === 'create' && (string) old('tenant_id') === (string) $tenant->id)>{{ $tenant->user?->name ?? 'Tenant #'.$tenant->id }}</option>
                                    @endforeach
                                </select>
                                @error('tenant_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-unit" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                    Unit <span class="text-rose-500">*</span>
                                </label>
                                <select id="create-unit" name="unit_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="" disabled {{ old('_form') === 'create' && old('unit_id') ? '' : 'selected' }}>Select vacant unit</option>
                                    @foreach($allVacantUnits as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('_form') === 'create' && (string) old('unit_id') === (string) $unit->id)>{{ $unit->unit_number }} — {{ $unit->property?->name ?? 'Property' }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="create-start" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                        Start date <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="create-start" name="start_date" type="date" value="{{ old('_form') === 'create' ? old('start_date') : '' }}" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('start_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                    @error('start_date')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="create-end" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                        End date <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="create-end" name="end_date" type="date" value="{{ old('_form') === 'create' ? old('end_date') : '' }}" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('end_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                    @error('end_date')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="create-rent" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Monthly rent <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="create-rent" name="monthly_rent" type="number" step="0.01" min="0" value="{{ old('_form') === 'create' ? old('monthly_rent') : '' }}" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('monthly_rent') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('monthly_rent')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="create-deposit" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Deposit amount
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="create-deposit" name="deposit_amount" type="number" step="0.01" min="0" value="{{ old('_form') === 'create' ? old('deposit_amount') : '' }}" placeholder="0.00" class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('deposit_amount') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('deposit_amount')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="create-deposit-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                        Deposit status <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="create-deposit-status" name="deposit_status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('deposit_status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                        <option value="held" @selected(old('_form') === 'create' ? old('deposit_status', 'held') === 'held' : true)>Held</option>
                                        <option value="returned" @selected(old('_form') === 'create' && old('deposit_status') === 'returned')>Returned</option>
                                        <option value="forfeited" @selected(old('_form') === 'create' && old('deposit_status') === 'forfeited')>Forfeited</option>
                                    </select>
                                    @error('deposit_status')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="create-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                        Lease status <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="create-status" name="status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                        <option value="active" @selected(old('_form') === 'create' ? old('status', 'active') === 'active' : true)>Active</option>
                                        <option value="completed" @selected(old('_form') === 'create' && old('status') === 'completed')>Completed</option>
                                        <option value="terminated" @selected(old('_form') === 'create' && old('status') === 'terminated')>Terminated</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-slate-100 pt-4">
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">
                                    Additional Information (Optional)
                                </p>

                                {{-- Inclusions --}}
                                <div class="mb-4">
                                    <label class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Inclusions
                                    </label>
                                    <p class="text-xs text-slate-400 mb-3">Select what is included in the monthly rent</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach(['Water', 'Electricity', 'Internet/WiFi', 'Parking', 'Cable TV', 'Trash Collection'] as $inclusion)
                                        <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-150 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-200">
                                            <input type="checkbox"
                                                   name="inclusions[]"
                                                   value="{{ $inclusion }}"
                                                   @checked(old('_form') === 'create' && in_array($inclusion, old('inclusions', []), true))
                                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600" />
                                            <span class="text-sm font-medium text-slate-700">{{ $inclusion }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @error('inclusions')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                    @error('inclusions.*')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        Notes & Special Agreements
                                    </label>
                                    <textarea
                                        name="notes"
                                        rows="3"
                                        placeholder="e.g. Tenant is allowed to have pets. Parking slot #3 is included."
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 resize-none"
                                    >{{ old('_form') === 'create' ? old('notes') : '' }}</textarea>
                                    <p class="mt-1 text-xs text-slate-400">Optional. Max 1000 characters.</p>
                                    @error('notes')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="createOpen = false">Cancel</button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:opacity-90 active:scale-[0.98]">Create Lease</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" role="dialog" aria-modal="true" aria-labelledby="lease-edit-title">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <div>
                                    <h3 id="lease-edit-title" class="text-base font-bold text-white">Edit lease</h3>
                                    <p class="text-xs text-indigo-200">Update tenant, unit, or lease terms.</p>
                                </div>
                            </div>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white transition-all hover:bg-white/20" @click="editOpen = false" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <form method="POST" x-bind:action="'{{ $leasePathPrefix }}/' + selectedLease.id">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit">
                        <input type="hidden" name="edit_lease_id" x-bind:value="selectedLease.id" />
                        <div class="max-h-[calc(90vh-10rem)] space-y-4 overflow-y-auto px-6 py-5">
                            <div>
                                <label for="edit-tenant" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    Tenant <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit-tenant" name="tenant_id" x-model="selectedLease.tenant_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('tenant_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    @foreach($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->user?->name ?? 'Tenant #'.$tenant->id }}</option>
                                    @endforeach
                                </select>
                                @error('tenant_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-unit" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                    Unit <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit-unit" name="unit_id" x-model="selectedLease.unit_id" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('unit_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    @foreach($unitsForEdit as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->unit_number }} — {{ $unit->property?->name ?? 'Property' }} @if($unit->status !== 'vacant') ({{ ucfirst($unit->status) }}) @endif</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="edit-start" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                        Start date <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="edit-start" name="start_date" type="date" x-model="selectedLease.start_date" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('start_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                    @error('start_date')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="edit-end" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                        End date <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="edit-end" name="end_date" type="date" x-model="selectedLease.end_date" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('end_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                    @error('end_date')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="edit-rent" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Monthly rent <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="edit-rent" name="monthly_rent" type="number" step="0.01" min="0" x-model="selectedLease.monthly_rent" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('monthly_rent') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('monthly_rent')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit-deposit" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    Deposit amount
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input id="edit-deposit" name="deposit_amount" type="number" step="0.01" min="0" x-model="selectedLease.deposit_amount" placeholder="0.00" class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('deposit_amount') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                </div>
                                @error('deposit_amount')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="edit-deposit-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                        Deposit status <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="edit-deposit-status" name="deposit_status" x-model="selectedLease.deposit_status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('deposit_status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                        <option value="held">Held</option>
                                        <option value="returned">Returned</option>
                                        <option value="forfeited">Forfeited</option>
                                    </select>
                                    @error('deposit_status')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="edit-status" class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v3.75C7.5 17.496 6.996 18 6.375 18h-2.25A1.125 1.125 0 0 1 3 16.875v-3.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                        Lease status <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="edit-status" name="status" x-model="selectedLease.status" required class="block w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 @error('status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-slate-100 pt-4">
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">
                                    Additional Information (Optional)
                                </p>

                                {{-- Inclusions --}}
                                <div class="mb-4">
                                    <label class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Inclusions
                                    </label>
                                    <p class="text-xs text-slate-400 mb-3">Select what is included in the monthly rent</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach(['Water', 'Electricity', 'Internet/WiFi', 'Parking', 'Cable TV', 'Trash Collection'] as $inclusion)
                                        <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2.5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-150 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-200">
                                            <input type="checkbox"
                                                   name="inclusions[]"
                                                   value="{{ $inclusion }}"
                                                   :checked="(selectedLease.inclusions ?? []).includes({{ \Illuminate\Support\Js::from($inclusion) }})"
                                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600" />
                                            <span class="text-sm font-medium text-slate-700">{{ $inclusion }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @error('inclusions')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                    @error('inclusions.*')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        Notes & Special Agreements
                                    </label>
                                    <textarea
                                        name="notes"
                                        rows="3"
                                        placeholder="e.g. Tenant is allowed to have pets. Parking slot #3 is included."
                                        x-model="selectedLease.notes"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 resize-none"
                                    ></textarea>
                                    <p class="mt-1 text-xs text-slate-400">Optional. Max 1000 characters.</p>
                                    @error('notes')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:opacity-90 active:scale-[0.98]">Update Lease</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete modal --}}
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" role="dialog" aria-modal="true" aria-labelledby="lease-delete-title">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="deleteOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white p-8 text-center shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                    <h3 id="lease-delete-title" class="mt-5 text-xl font-bold text-slate-900">Delete lease?</h3>
                    <p class="mt-2 text-sm text-slate-500">This action cannot be undone.</p>
                    <p class="mt-1 text-sm text-slate-600">Lease for <span class="font-semibold text-slate-900" x-text="deleteLease.tenant_name"></span> will be removed.</p>
                    <div class="mt-8 flex flex-col-reverse items-stretch justify-center gap-3 sm:flex-row sm:items-center">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="deleteOpen = false">Cancel</button>
                        <form method="POST" x-bind:action="'{{ $leasePathPrefix }}/' + deleteLease.id" class="inline">
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
