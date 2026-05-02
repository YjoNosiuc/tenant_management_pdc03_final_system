@extends('layouts.admin', ['title' => 'Dashboard'])

@section('title', 'Dashboard')

@push('head')
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')
    @php
        $hour = now('Asia/Manila')->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $paymentBadge = fn (string $status) => match ($status) {
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'verifying' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
            'late' => 'bg-rose-50 text-rose-700 ring-rose-100',
            'rejected' => 'bg-red-50 text-red-700 ring-red-100',
            default => 'bg-slate-50 text-slate-700 ring-slate-100',
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-8">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $greeting }}, {{ auth()->user()->name }}!</h1>
                </div>
                <p class="mt-1 pl-3 text-sm text-slate-500">Here's what's happening with your properties today.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md" style="animation: fadeUp 0.4s ease forwards; animation-delay: 0s; opacity: 0;">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Properties</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalProperties) }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $propertiesTrend }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-9H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md" style="animation: fadeUp 0.4s ease forwards; animation-delay: 0.1s; opacity: 0;">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Units</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalUnits) }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $unitsTrend }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50">
                        <svg class="h-6 w-6 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md" style="animation: fadeUp 0.4s ease forwards; animation-delay: 0.2s; opacity: 0;">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active Leases</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($activeLeases) }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $leasesTrend }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50">
                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md" style="animation: fadeUp 0.4s ease forwards; animation-delay: 0.3s; opacity: 0;">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500 opacity-10"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pending Payments</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($pendingPayments) }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $paymentsTrend }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:shadow-md">
                    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Recent Payments</h2>
                            <p class="mt-1 text-sm text-slate-500">Latest rent activity across your portfolio</p>
                        </div>
                        <a href="{{ route('admin.payments.index') }}" class="text-sm font-semibold text-indigo-600 transition-all duration-200 hover:text-indigo-500">View all</a>
                    </div>

                    @if($recentPayments->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-100">
                                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-slate-800">No payments yet</p>
                            <p class="mt-1 max-w-sm text-sm text-slate-500">When tenants submit rent, you will see verification tasks and history here.</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl ring-1 ring-slate-100">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead class="divide-y divide-slate-100 bg-slate-50/80">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tenant</th>
                                            <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Unit</th>
                                            <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Amount Due</th>
                                            <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Due Date</th>
                                            <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                            <th scope="col" class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-400">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 bg-white">
                                        @foreach($recentPayments as $payment)
                                            @php
                                                $tenantName = $payment->lease?->tenant?->user?->name ?? 'Tenant';
                                                $unitLabel = $payment->lease?->unit
                                                    ? ($payment->lease->unit->property?->name ?? 'Property').' · Unit '.$payment->lease->unit->unit_number
                                                    : '—';
                                            @endphp
                                            <tr class="transition-colors duration-150 hover:bg-indigo-50/30">
                                                <td class="whitespace-nowrap px-4 py-3.5">
                                                    <p class="text-sm font-semibold text-slate-800">{{ $tenantName }}</p>
                                                </td>
                                                <td class="px-4 py-3.5 text-sm text-slate-500">{{ $unitLabel }}</td>
                                                <td class="whitespace-nowrap px-4 py-3.5 text-sm font-bold text-indigo-700">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                                <td class="whitespace-nowrap px-4 py-3.5 text-sm text-slate-500">{{ $payment->due_date?->format('M d, Y') }}</td>
                                                <td class="whitespace-nowrap px-4 py-3.5">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $paymentBadge($payment->status) }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ match ($payment->status) {
                                                            'paid' => 'bg-emerald-400',
                                                            'pending' => 'bg-amber-400',
                                                            'verifying' => 'bg-indigo-400 animate-pulse',
                                                            'late' => 'bg-rose-400',
                                                            'rejected' => 'bg-red-400',
                                                            default => 'bg-slate-400',
                                                        } }}"></span>
                                                        {{ match ($payment->status) {
                                                            'pending' => 'Pending',
                                                            'verifying' => 'Verifying',
                                                            'paid' => 'Paid',
                                                            'late' => 'Late',
                                                            'rejected' => 'Rejected',
                                                            default => ucfirst($payment->status),
                                                        } }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                                    <a href="{{ route('admin.payments.show', $payment) }}"
                                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-150"
                                                       title="View Payment"
                                                       aria-label="View Payment">
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
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 transition-all duration-300 hover:shadow-md">
                    <div class="mb-6">
                        <h2 class="text-base font-semibold text-slate-900">Occupancy</h2>
                        <p class="mt-1 text-sm text-slate-500">Live snapshot of unit availability</p>
                    </div>

                    <ul class="space-y-4">
                        <li class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-indigo-500 ring-4 ring-indigo-100"></span>
                                <span class="text-sm font-medium text-slate-700">Total units</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($totalUnits) }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
                                <span class="text-sm font-medium text-slate-700">Occupied</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($occupiedUnits) }}</span>
                        </li>
                        <li class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300 ring-4 ring-slate-100"></span>
                                <span class="text-sm font-medium text-slate-700">Vacant</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($vacantUnits) }}</span>
                        </li>
                    </ul>

                    <div class="mt-8">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                            <span>Occupancy rate</span>
                            <span class="text-slate-800">{{ $occupancyPercent }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all duration-500" style="width: {{ min(100, (int) $occupancyPercent) }}%"></div>
                        </div>
                        <p class="mt-3 text-xs text-slate-400">Vacant units are ready for marketing and showings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
