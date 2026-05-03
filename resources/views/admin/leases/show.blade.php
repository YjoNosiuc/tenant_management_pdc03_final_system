@extends('layouts.admin', ['title' => 'Lease Details'])

@section('title', 'Lease Details')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $t = $lease->tenant;
    $u = $t?->user;
    $unit = $lease->unit;
    $prop = $unit?->property;
    $tenantInitials = strtoupper(mb_substr($u?->name ?? 'T', 0, 2));
    $unitTypeLabel = $unit?->unit_type
        ? str($unit->unit_type)->replace('_', ' ')->title()->toString()
        : '';
    $leaseStatusShell = match ($lease->status) {
        'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
        'completed' => 'bg-blue-50 text-blue-800 ring-blue-100',
        'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
        default => 'bg-slate-50 text-slate-700 ring-slate-100',
    };
    $leaseStatusDot = match ($lease->status) {
        'active' => 'bg-emerald-500',
        'completed' => 'bg-blue-500',
        'terminated' => 'bg-rose-500',
        default => 'bg-slate-400',
    };
    $depositStatusShell = match ($lease->deposit_status) {
        'held' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'returned' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
        'forfeited' => 'bg-rose-50 text-rose-800 ring-rose-100',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
    $depositStatusDot = match ($lease->deposit_status) {
        'held' => 'bg-slate-400',
        'returned' => 'bg-emerald-500',
        'forfeited' => 'bg-rose-500',
        default => 'bg-slate-400',
    };
    $daysLeft = $lease->end_date
        ? (int) now()->startOfDay()->diffInDays($lease->end_date->copy()->startOfDay(), false)
        : 0;
    $inclusionIcons = [
        'Water' => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z',
        'Electricity' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
        'Internet/WiFi' => 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z',
        'Parking' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
        'Cable TV' => 'M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z',
        'Trash Collection' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',
    ];
@endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{ editOpen: false, flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }} }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 3000);
            @endif
            @if(old('_form') === 'edit_show' && $errors->any())
                editOpen = true;
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

        <a href="{{ route('admin.leases.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Leases
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-600" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        Lease #{{ $lease->id }} — {{ $lease->tenant?->user?->name ?? '—' }}
                    </h1>
                    <p class="text-sm text-slate-400">
                        {{ $lease->unit?->property?->name ?? '—' }} · Unit {{ $lease->unit?->unit_number ?? '—' }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $leaseStatusShell }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $leaseStatusDot }}"></span>
                    {{ ucfirst($lease->status) }}
                </span>
                <a href="{{ route('admin.leases.pdf', $lease) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 active:scale-[0.98] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Download PDF
                </a>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-[0.98]"
                    @click="editOpen = true"
                >
                    Edit
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                {{-- Parties --}}
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-4">Parties involved</p>
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 mb-3">Tenant</p>
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                    {{ $tenantInitials }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $u?->name ?? '—' }}</p>
                                    <p class="text-sm text-slate-500 mt-0.5">{{ $u?->email ?? '—' }}</p>
                                    <p class="text-sm text-slate-500 mt-1 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0 text-slate-400" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                        </svg>
                                        {{ $t?->phone_number ?? '—' }}
                                    </p>
                                    @if($t)
                                        <a href="{{ route('admin.tenants.show', $t) }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                            View Tenant Profile
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 mb-3">Unit</p>
                            <p class="text-3xl font-extrabold text-indigo-600">{{ $unit?->unit_number ?? '—' }}</p>
                            <p class="text-sm text-slate-600 mt-2 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H18v-.008Zm0 3h.008v.008H18v-.008Zm0 3h.008v.008H18v-.008ZM15.75 21h-7.5a2.25 2.25 0 0 1-2.25-2.25v-8.844a2.25 2.25 0 0 1 1.183-1.98l7.5-4.166a2.25 2.25 0 0 1 2.134 0l7.5 4.166a2.25 2.25 0 0 1 1.183 1.98V18.75A2.25 2.25 0 0 1 15.75 21Z" />
                                </svg>
                                {{ $prop?->name ?? '—' }}
                            </p>
                            @if($unitTypeLabel !== '')
                                <span class="mt-2 inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $unitTypeLabel }}</span>
                            @endif
                            @if($unit)
                                <a href="{{ route('admin.units.show', $unit) }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                    View Unit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Lease terms --}}
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-4">Lease terms</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Start date</p>
                            <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 shrink-0" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                                </svg>
                                {{ $lease->start_date?->format('M d, Y') ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">End date</p>
                            <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 shrink-0" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                                </svg>
                                {{ $lease->end_date?->format('M d, Y') ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Monthly rent</p>
                            <p class="text-sm font-bold text-indigo-700 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-indigo-500 shrink-0" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                ₱{{ number_format((float) $lease->monthly_rent, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Deposit</p>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400 shrink-0" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                @if($lease->deposit_amount !== null)
                                    ₱{{ number_format((float) $lease->deposit_amount, 2) }}
                                @else
                                    <span class="font-semibold text-slate-500">None</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Deposit status</p>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $depositStatusShell }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $depositStatusDot }}"></span>
                                {{ ucfirst($lease->deposit_status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Lease status</p>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $leaseStatusShell }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $leaseStatusDot }}"></span>
                                {{ ucfirst($lease->status) }}
                            </span>
                        </div>
                    </div>

                    @if($daysLeft > 0 && $lease->status === 'active')
                        <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-100 p-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0 text-emerald-600" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-xs font-medium text-emerald-700">{{ $daysLeft }} days remaining</p>
                        </div>
                    @elseif($lease->end_date && $daysLeft <= 0)
                        <div class="mt-4 rounded-xl bg-rose-50 border border-rose-100 p-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0 text-rose-600" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <p class="text-xs font-medium text-rose-700">Lease has ended</p>
                        </div>
                    @endif
                </div>

                {{-- Inclusions & notes --}}
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-4">Inclusions &amp; notes</p>
                    @if($lease->inclusions && count($lease->inclusions) > 0)
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($lease->inclusions as $inclusion)
                                @php $iconPath = $inclusionIcons[$inclusion] ?? 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'; @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 border border-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                                    </svg>
                                    {{ $inclusion }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic mb-4">No inclusions specified.</p>
                    @endif
                    @if($lease->notes)
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Notes</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $lease->notes }}</p>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">No notes added.</p>
                    @endif
                </div>

                {{-- Payments --}}
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment records</p>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $lease->payments->count() }}</span>
                    </div>
                    @if($lease->payments->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700">No payments yet</p>
                            <p class="text-xs text-slate-500 mt-1">Payments for this lease will appear here.</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl ring-1 ring-slate-100">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead class="bg-slate-50/80">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Due date</th>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Payment date</th>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Amount</th>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 bg-white">
                                        @foreach($lease->payments as $payment)
                                            @php
                                                $isOverdueDue = $payment->due_date
                                                    && $payment->due_date->lt(now()->startOfDay())
                                                    && $payment->status !== 'paid';
                                                $payShell = match ($payment->status) {
                                                    'paid' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                                    'pending' => 'bg-amber-50 text-amber-800 ring-amber-100',
                                                    'verifying' => 'bg-indigo-50 text-indigo-800 ring-indigo-100',
                                                    'late' => 'bg-rose-50 text-rose-800 ring-rose-100',
                                                    'rejected' => 'bg-red-50 text-red-800 ring-red-100',
                                                    default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                                };
                                                $payDot = match ($payment->status) {
                                                    'paid' => 'bg-emerald-500',
                                                    'pending' => 'bg-amber-500',
                                                    'verifying' => 'bg-indigo-500',
                                                    'late' => 'bg-rose-500',
                                                    'rejected' => 'bg-red-500',
                                                    default => 'bg-slate-400',
                                                };
                                            @endphp
                                            <tr class="transition-colors duration-150 hover:bg-indigo-50/30">
                                                <td class="px-4 py-3 whitespace-nowrap font-medium {{ $isOverdueDue ? 'text-rose-700' : 'text-slate-900' }}">
                                                    {{ $payment->due_date?->format('M d, Y') ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($payment->payment_date)
                                                        <span class="text-slate-700">{{ $payment->payment_date->format('M d, Y') }}</span>
                                                    @else
                                                        <span class="italic text-slate-400">Not yet paid</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-indigo-700">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $payShell }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $payDot }}"></span>
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contract sidebar --}}
            <div class="lg:col-span-1">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100 lg:sticky lg:top-6">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-4">Contract</p>

                    @error('contract')
                        <p class="mb-3 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror

                    @if($lease->contract_path)
                        @php
                            $extension = pathinfo($lease->contract_path, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower((string) $extension), ['jpg', 'jpeg', 'png', 'webp'], true);
                            $isPdf = strtolower((string) $extension) === 'pdf';
                        @endphp

                        @if($isImage)
                            <div class="mb-4 overflow-hidden rounded-xl bg-slate-50 ring-1 ring-slate-200">
                                <img src="{{ Storage::url($lease->contract_path) }}"
                                     class="w-full object-cover max-h-48 rounded-xl"
                                     alt="Contract" />
                            </div>
                        @elseif($isPdf)
                            <div class="mb-4 flex items-center gap-3 rounded-xl bg-red-50 border border-red-100 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-red-800">PDF Contract</p>
                                    <p class="text-xs text-red-600">Click below to view</p>
                                </div>
                            </div>
                        @else
                            <div class="mb-4 flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-200 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-500" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Contract File</p>
                                    <p class="text-xs text-slate-400">{{ strtoupper((string) $extension) }} file</p>
                                </div>
                            </div>
                        @endif

                        <p class="text-xs text-slate-400 mb-4 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Uploaded {{ $lease->contract_uploaded_at?->format('M d, Y · h:i A') }}
                        </p>

                        <a href="{{ Storage::url($lease->contract_path) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 active:scale-[0.98] transition-all duration-150 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            View Contract
                        </a>

                        <a href="{{ Storage::url($lease->contract_path) }}"
                           download
                           class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition-all duration-150 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download
                        </a>

                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-xs font-semibold text-slate-500 mb-2">Replace Contract</p>
                            <form action="{{ route('admin.leases.contract.upload', $lease) }}"
                                  method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <label class="flex flex-col items-center justify-center w-full h-24 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-150 relative">
                                    <input type="file"
                                           name="contract"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                                           class="absolute inset-0 opacity-0 cursor-pointer"
                                           onchange="this.form.submit()" />
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-300 mb-1" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.192 4.5 4.5 0 0 1 1.5 8.438M12 16.5V9.75m0 0-3 3m3-3 3 3" />
                                    </svg>
                                    <p class="text-xs font-medium text-slate-500">Click to replace</p>
                                    <p class="text-xs text-slate-400">PDF, Image, DOC · Max 10MB</p>
                                </label>
                            </form>
                            <p class="mt-2 text-xs text-amber-600 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 shrink-0" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                Replacing will overwrite the current contract.
                            </p>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">No contract uploaded</p>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Upload a signed contract or photo of the agreement.
                            </p>
                        </div>

                        <form action="{{ route('admin.leases.contract.upload', $lease) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <label class="flex flex-col items-center justify-center w-full h-36 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all duration-150 relative">
                                <input type="file"
                                       name="contract"
                                       accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
                                       class="absolute inset-0 opacity-0 cursor-pointer"
                                       onchange="this.form.submit()" />
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-300 mb-2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.192 4.5 4.5 0 0 1 1.5 8.438M12 16.5V9.75m0 0-3 3m3-3 3 3" />
                                </svg>
                                <p class="text-sm font-medium text-slate-500">Upload Contract</p>
                                <p class="text-xs text-slate-400 mt-1">PDF, Image, DOC · Max 10MB</p>
                            </label>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" role="dialog" aria-modal="true" aria-labelledby="lease-show-edit-title">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200" @click.stop>
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 id="lease-show-edit-title" class="text-lg font-semibold text-slate-900">Edit lease</h3>
                            <p class="mt-1 text-sm text-slate-500">Update tenant, unit, or lease terms.</p>
                        </div>
                        <button type="button" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" @click="editOpen = false" aria-label="Close">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.leases.update', $lease) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit_show">

                        <div>
                            <label for="show-tenant" class="mb-1.5 block text-sm font-medium text-slate-700">Tenant <span class="text-rose-500">*</span></label>
                            <select id="show-tenant" name="tenant_id" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('tenant_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" @selected((string) old('_form') === 'edit_show' ? (string) old('tenant_id') === (string) $tenant->id : (string) $lease->tenant_id === (string) $tenant->id)>{{ $tenant->user?->name ?? 'Tenant #'.$tenant->id }}</option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="show-unit" class="mb-1.5 block text-sm font-medium text-slate-700">Unit <span class="text-rose-500">*</span></label>
                            <select id="show-unit" name="unit_id" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('unit_id') border-rose-300 ring-1 ring-rose-200 @enderror">
                                @foreach($unitsForEdit as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) old('_form') === 'edit_show' ? (string) old('unit_id') === (string) $unit->id : (string) $lease->unit_id === (string) $unit->id)>{{ $unit->unit_number }} — {{ $unit->property?->name ?? 'Property' }} @if($unit->status !== 'vacant') ({{ ucfirst($unit->status) }}) @endif</option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="show-start" class="mb-1.5 block text-sm font-medium text-slate-700">Start date <span class="text-rose-500">*</span></label>
                                <input id="show-start" name="start_date" type="date" value="{{ old('_form') === 'edit_show' ? old('start_date') : $lease->start_date?->format('Y-m-d') }}" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('start_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('start_date')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="show-end" class="mb-1.5 block text-sm font-medium text-slate-700">End date <span class="text-rose-500">*</span></label>
                                <input id="show-end" name="end_date" type="date" value="{{ old('_form') === 'edit_show' ? old('end_date') : $lease->end_date?->format('Y-m-d') }}" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('end_date') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('end_date')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="show-rent" class="mb-1.5 block text-sm font-medium text-slate-700">Monthly rent <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500">₱</span>
                                <input id="show-rent" name="monthly_rent" type="number" step="0.01" min="0" value="{{ old('_form') === 'edit_show' ? old('monthly_rent') : $lease->monthly_rent }}" required class="block w-full rounded-xl border border-slate-200 py-2.5 pl-8 pr-3 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('monthly_rent') border-rose-300 ring-1 ring-rose-200 @enderror" />
                            </div>
                            @error('monthly_rent')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="show-deposit" class="mb-1.5 block text-sm font-medium text-slate-700">Deposit amount</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-semibold text-slate-500">₱</span>
                                <input id="show-deposit" name="deposit_amount" type="number" step="0.01" min="0" value="{{ old('_form') === 'edit_show' ? old('deposit_amount') : $lease->deposit_amount }}" class="block w-full rounded-xl border border-slate-200 py-2.5 pl-8 pr-3 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('deposit_amount') border-rose-300 ring-1 ring-rose-200 @enderror" />
                            </div>
                            @error('deposit_amount')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="show-deposit-status" class="mb-1.5 block text-sm font-medium text-slate-700">Deposit status <span class="text-rose-500">*</span></label>
                                <select id="show-deposit-status" name="deposit_status" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('deposit_status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="held" @selected(old('_form') === 'edit_show' ? old('deposit_status', $lease->deposit_status) === 'held' : $lease->deposit_status === 'held')>Held</option>
                                    <option value="returned" @selected(old('_form') === 'edit_show' ? old('deposit_status', $lease->deposit_status) === 'returned' : $lease->deposit_status === 'returned')>Returned</option>
                                    <option value="forfeited" @selected(old('_form') === 'edit_show' ? old('deposit_status', $lease->deposit_status) === 'forfeited' : $lease->deposit_status === 'forfeited')>Forfeited</option>
                                </select>
                                @error('deposit_status')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="show-status" class="mb-1.5 block text-sm font-medium text-slate-700">Lease status <span class="text-rose-500">*</span></label>
                                <select id="show-status" name="status" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('status') border-rose-300 ring-1 ring-rose-200 @enderror">
                                    <option value="active" @selected(old('_form') === 'edit_show' ? old('status', $lease->status) === 'active' : $lease->status === 'active')>Active</option>
                                    <option value="completed" @selected(old('_form') === 'edit_show' ? old('status', $lease->status) === 'completed' : $lease->status === 'completed')>Completed</option>
                                    <option value="terminated" @selected(old('_form') === 'edit_show' ? old('status', $lease->status) === 'terminated' : $lease->status === 'terminated')>Terminated</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                            <button type="button" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98]" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="inline-flex justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:scale-[0.98]">Update Lease</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
