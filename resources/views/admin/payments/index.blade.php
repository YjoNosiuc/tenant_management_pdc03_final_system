@extends('layouts.admin', ['title' => 'Payments'])

@section('title', 'Payments')

@section('content')
    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{ flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }} }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 3000);
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

        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payments</h1>
                </div>
                <p class="mt-1 pl-3 text-sm text-slate-500">Review and verify tenant rent submissions</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pending</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($pendingCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">No proof yet</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Verifying</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($verifyingCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Proof submitted</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-6 w-6 animate-pulse text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100 transition-all duration-200 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Verifying (Late)</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($verifyingLateCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Late with proof submitted</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50">
                        <svg class="h-6 w-6 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Paid</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($paidCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Cleared payments</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rose-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Late</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($lateCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Past due attention</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50">
                        <svg class="h-6 w-6 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-red-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Rejected</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($rejectedCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Declined submissions</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-50">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">All payments</h2>
                <p class="mt-1 text-sm text-slate-500">Filter by property, unit, status, or tenant name. All filters apply on the server.</p>
            </div>

            {{-- Filter Row --}}
            <form method="GET" action="{{ route('admin.payments.index') }}">
                @csrf
                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Property Filter --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="filter-property_id">
                            Property
                        </label>
                        <select
                            id="filter-property_id"
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

                    {{-- Unit Filter (cascades from property) --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="filter-unit_id">
                            Unit
                        </label>
                        <select
                            id="filter-unit_id"
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
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="filter-status">
                            Status
                        </label>
                        <select
                            id="filter-status"
                            name="status"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verifying" {{ request('status') === 'verifying' ? 'selected' : '' }}>Verifying</option>
                            <option value="verifying_late" {{ request('status') === 'verifying_late' ? 'selected' : '' }}>Verifying (Late)</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- Tenant Search --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400" for="filter-search">
                            Search Tenant
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input
                                id="filter-search"
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Tenant name..."
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                    </div>
                </div>

                {{-- Active filters row + Clear button --}}
                @if(request()->filled('status') || request()->filled('property_id') || request()->filled('unit_id') || request()->filled('search'))
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Active filters:</span>

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
                            href="{{ route('admin.payments.index') }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 transition-all hover:bg-rose-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Clear all
                        </a>
                    </div>
                @endif
            </form>

            {{-- Results summary --}}
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing <span class="font-semibold text-slate-700">{{ $payments->firstItem() ?? 0 }}</span>
                    to <span class="font-semibold text-slate-700">{{ $payments->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-slate-700">{{ $payments->total() }}</span> results
                    @if(request()->filled('status') || request()->filled('property_id') || request()->filled('unit_id') || request()->filled('search'))
                        <span class="font-medium text-indigo-600">(filtered)</span>
                    @endif
                </p>
            </div>

            {{-- Table --}}
            @if($payments->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-slate-300" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 21Z" />
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-slate-700">No payments found</p>
                    <p class="mt-1 text-sm text-slate-400">Try adjusting your filters or search term.</p>
                    <a
                        href="{{ route('admin.payments.index') }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition-all hover:bg-indigo-100"
                    >
                        Clear filters
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tenant</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Unit &amp; Property</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Amount</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Due Date</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            @foreach($payments as $payment)
                                @php
                                    $tenantUser = $payment->lease?->tenant?->user;
                                    $unit = $payment->lease?->unit;
                                    $property = $unit?->property;
                                    $isOverdue = $payment->isLateType()
                                        || ($payment->status === 'pending' && $payment->due_date && $payment->due_date->lt(now()->startOfDay()));
                                    $statusConfig = [
                                        'pending' => ['bg-amber-50 text-amber-700 ring-1 ring-amber-100', 'bg-amber-400', 'Pending'],
                                        'verifying' => ['bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100', 'bg-indigo-400 animate-pulse', 'Verifying'],
                                        'verifying_late' => ['bg-rose-50 text-rose-700 ring-1 ring-rose-100', 'bg-rose-400 animate-pulse', 'Verifying (Late)'],
                                        'paid' => ['bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100', 'bg-emerald-400', 'Paid'],
                                        'late' => ['bg-rose-50 text-rose-700 ring-1 ring-rose-100', 'bg-rose-400', 'Late'],
                                        'rejected' => ['bg-red-50 text-red-700 ring-1 ring-red-100', 'bg-red-400', 'Rejected'],
                                    ];
                                    $cfg = $statusConfig[$payment->status] ?? $statusConfig['pending'];
                                @endphp
                                <tr class="transition-colors duration-150 hover:bg-indigo-50/30">
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
                                                {{ strtoupper(substr($tenantUser?->name ?? 'T', 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ $tenantUser?->name ?? '—' }}</p>
                                                <p class="text-xs text-slate-400">{{ $tenantUser?->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <p class="font-semibold text-slate-800">Unit {{ $unit?->unit_number ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">{{ $property?->name ?? '—' }}</p>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <p class="font-semibold text-indigo-700">
                                            ₱{{ number_format($payment->getBreakdown()['total_amount_due'], 2) }}
                                        </p>
                                        @if($payment->hasLateFee())
                                            <p class="text-xs text-rose-500">
                                                +₱{{ number_format((float) $payment->late_fee_amount, 2) }} late fee
                                            </p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <p class="{{ $isOverdue ? 'font-semibold text-rose-600' : 'text-slate-600' }}">
                                            {{ $payment->due_date?->format('M d, Y') }}
                                        </p>
                                        @if($payment->payment_date)
                                            <p class="text-xs text-slate-400">
                                                Paid: {{ $payment->payment_date?->format('M d, Y') }}
                                            </p>
                                        @else
                                            <p class="text-xs italic text-slate-400">Not yet paid</p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $cfg[0] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $cfg[1] }}"></span>
                                            {{ $cfg[2] }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                        <a
                                            href="{{ route('admin.payments.show', $payment) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-all duration-150 hover:bg-indigo-50 hover:text-indigo-600"
                                            title="View Payment"
                                            aria-label="View Payment"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
