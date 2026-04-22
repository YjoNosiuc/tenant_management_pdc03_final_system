@extends('layouts.admin', ['title' => 'Lease Details'])

@section('title', 'Lease Details')

@section('content')
    @php
        $t = $lease->tenant;
        $u = $t?->user;
        $unit = $lease->unit;
        $prop = $unit?->property;
        $leaseStatusClass = match ($lease->status) {
            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'completed' => 'bg-blue-50 text-blue-800 ring-blue-100',
            'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
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

        <div>
            <a
                href="{{ route('admin.leases.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-[0.98]"
            >
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Back to Leases
            </a>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Lease Details</h1>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $leaseStatusClass }}">{{ ucfirst($lease->status) }}</span>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-[0.98]"
                    @click="editOpen = true"
                >
                    Edit
                </button>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/40 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $u?->name ?? '—' }}</p>
                    <dl class="mt-4 space-y-2 text-sm text-slate-600">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                            <dd class="font-medium text-slate-800">{{ $u?->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt>
                            <dd class="font-medium text-slate-800">{{ $t?->phone_number ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50/40 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Unit</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $unit?->unit_number ? 'Unit '.$unit->unit_number : '—' }}</p>
                    <dl class="mt-4 space-y-2 text-sm text-slate-600">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Property</dt>
                            <dd class="font-medium text-slate-800">{{ $prop?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Type</dt>
                            <dd class="font-medium text-slate-800">{{ $unit?->unit_type ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-100 pt-8">
                <p class="text-sm font-semibold text-slate-900">Lease terms</p>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Start date</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $lease->start_date?->format('M d, Y') }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">End date</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $lease->end_date?->format('M d, Y') }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Monthly rent</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">₱{{ number_format((float) $lease->monthly_rent, 2) }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deposit amount</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            @if($lease->deposit_amount !== null)
                                ₱{{ number_format((float) $lease->deposit_amount, 2) }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deposit status</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $lease->deposit_status)) }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-white p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lease status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $leaseStatusClass }}">{{ ucfirst($lease->status) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:p-8">
            <h2 class="text-lg font-semibold text-slate-900">Payment Records</h2>
            <p class="mt-1 text-sm text-slate-500">Payments linked to this lease.</p>

            @if($lease->payments->isEmpty())
                <div class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-indigo-400 shadow-sm ring-1 ring-slate-100">
                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <p class="mt-4 text-base font-semibold text-slate-800">No payment records</p>
                    <p class="mt-2 max-w-md text-sm text-slate-500">When payments are recorded for this lease, they will appear here with due dates, methods, and verification status.</p>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Proof</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Verified At</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($lease->payments as $payment)
                                    @php
                                        $payStatus = match ($payment->status) {
                                            'paid' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                            'pending' => 'bg-amber-50 text-amber-800 ring-amber-100',
                                            'late' => 'bg-orange-50 text-orange-800 ring-orange-100',
                                            'rejected' => 'bg-rose-50 text-rose-800 ring-rose-100',
                                            default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                        };
                                        $methodLabel = $payment->payment_method
                                            ? \Illuminate\Support\Str::headline(str_replace('_', ' ', $payment->payment_method))
                                            : null;
                                        $proofUrl = $payment->proof_of_payment
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_of_payment)
                                            : null;
                                    @endphp
                                    <tr class="transition-colors duration-200 hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->due_date?->format('M d, Y') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $methodLabel ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $payStatus }}">{{ ucfirst($payment->status) }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if($proofUrl)
                                                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-indigo-600 underline decoration-indigo-200 underline-offset-2 hover:text-indigo-800">View Proof</a>
                                            @else
                                                <span class="text-sm text-slate-500">No proof</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->verified_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <a
                                                href="{{ route('admin.payments.show', $payment) }}"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-95"
                                            >View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
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
