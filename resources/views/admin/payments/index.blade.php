@extends('layouts.admin', ['title' => 'Payments'])

@section('title', 'Payments')

@section('content')
    @php
        $paymentMethodLabels = [
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'bank_transfer' => 'Bank transfer',
            'maya' => 'Maya',
        ];
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            search: '',
            filterStatus: '',
            rowMatches(tenantName, status) {
                if (this.filterStatus && status !== this.filterStatus) return false;
                const s = this.search.trim().toLowerCase();
                if (!s) return true;
                return (tenantName || '').toLowerCase().includes(s);
            }
        }"
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

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pending</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($pendingCount) }}</p>
                        <p class="mt-1 text-xs text-slate-400">Awaiting verification</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
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
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">All payments</h2>
                    <p class="mt-1 text-sm text-slate-500">Filter by status and search by tenant name</p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:min-w-[28rem]">
                    <div class="w-full sm:max-w-[11rem]">
                        <label for="payment-status-filter" class="sr-only">Filter by status</label>
                        <select
                            id="payment-status-filter"
                            x-model="filterStatus"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="late">Late</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="relative w-full flex-1">
                        <label for="payment-search" class="sr-only">Search by tenant name</label>
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </span>
                        <input
                            id="payment-search"
                            type="search"
                            x-model="search"
                            placeholder="Search tenant name…"
                            class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 shadow-sm transition-all duration-150 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:shadow-md"
                        />
                    </div>
                </div>
            </div>

            @if($payments->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 py-16 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-800">No payments yet</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500">When tenants submit rent payments, they will appear here for review.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl ring-1 ring-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                            <thead class="divide-y divide-slate-100 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Tenant</th>
                                    <th scope="col" class="px-4 py-3">Unit &amp; property</th>
                                    <th scope="col" class="px-4 py-3">Amount</th>
                                    <th scope="col" class="px-4 py-3">Method</th>
                                    <th scope="col" class="px-4 py-3">Due date</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 bg-white">
                                @foreach($payments as $row)
                                    @php
                                        $user = $row->lease?->tenant?->user;
                                        $tenantName = $user?->name ?? '—';
                                        $tenantInitials = strtoupper(substr($user?->name ?? 'T', 0, 2));
                                        $tenantEmail = $user?->email ?? '';
                                        $unit = $row->lease?->unit;
                                        $property = $unit?->property;
                                        $methodLabel = $row->payment_method ? ($paymentMethodLabels[$row->payment_method] ?? ucfirst(str_replace('_', ' ', $row->payment_method))) : null;
                                        $status = $row->status;
                                        if ($status === 'paid' && $row->verified_at) {
                                            $dotClass = 'bg-blue-500';
                                            $badgeShell = 'bg-blue-50 text-blue-700 ring-blue-100';
                                            $badgeLabel = 'Verified';
                                        } elseif ($status === 'paid') {
                                            $dotClass = 'bg-emerald-500';
                                            $badgeShell = 'bg-emerald-50 text-emerald-700 ring-emerald-100';
                                            $badgeLabel = 'Paid';
                                        } else {
                                            $dotClass = match ($status) {
                                                'pending' => 'bg-amber-500',
                                                'late', 'rejected' => 'bg-rose-500',
                                                default => 'bg-slate-400',
                                            };
                                            $badgeShell = match ($status) {
                                                'pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                                'late', 'rejected' => 'bg-rose-50 text-rose-700 ring-rose-100',
                                                default => 'bg-slate-50 text-slate-700 ring-slate-100',
                                            };
                                            $badgeLabel = match ($status) {
                                                'pending' => 'Pending',
                                                'late' => 'Late',
                                                'rejected' => 'Rejected',
                                                default => ucfirst((string) $status),
                                            };
                                        }
                                        $isOverdue = $row->due_date
                                            && $row->due_date->lt(now()->startOfDay())
                                            && $status !== 'paid';
                                    @endphp
                                    <tr
                                        class="transition-colors duration-150 hover:bg-indigo-50/30"
                                        x-show="rowMatches(@js($tenantName), @js($status))"
                                        x-transition.opacity.duration.150ms
                                    >
                                        <td class="px-4 py-3.5 align-top">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">{{ $tenantInitials }}</div>
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $tenantName }}</p>
                                                    @if($tenantEmail)
                                                        <p class="text-xs text-slate-400 mt-0.5">{{ $tenantEmail }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 align-top">
                                            <p class="font-semibold text-slate-800">{{ $unit?->unit_number ?? '—' }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $property?->name ?? '—' }}</p>
                                        </td>
                                        <td class="px-4 py-3.5 align-top">
                                            <p class="text-sm font-bold text-indigo-700">₱{{ number_format((float) $row->amount_paid, 2) }}</p>
                                        </td>
                                        <td class="px-4 py-3.5 align-top">
                                            @if($methodLabel)
                                                <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $methodLabel }}</span>
                                            @else
                                                <span class="text-sm text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 align-top">
                                            <div class="flex items-center gap-1.5">
                                                @if($isOverdue)
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-rose-500" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                                @endif
                                                <div>
                                                    <p class="text-sm font-medium {{ $isOverdue ? 'text-rose-600' : 'text-slate-700' }}">{{ $row->due_date?->format('M d, Y') ?? '—' }}</p>
                                                    @if($row->payment_date)
                                                        <p class="text-xs text-slate-400 mt-0.5">Paid: {{ $row->payment_date->format('M d, Y') }}</p>
                                                    @else
                                                        <p class="text-xs text-slate-400 mt-0.5">Not yet paid</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 align-top">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $badgeShell }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                                {{ $badgeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 align-top text-right">
                                            <a
                                                href="{{ route('admin.payments.show', $row) }}"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-150"
                                                title="View"
                                                aria-label="View"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
