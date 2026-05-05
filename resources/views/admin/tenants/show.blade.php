@extends('layouts.admin', ['title' => $tenant->user?->name ?? 'Tenant'])

@section('title', $tenant->user?->name ?? 'Tenant')

@section('content')
    @php
        $u = $tenant->user;
        $initials = collect(preg_split('/\s+/', trim((string) ($u?->name ?? ''))))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        if ($initials === '') {
            $initials = mb_strtoupper(mb_substr((string) ($u?->email ?? 'T'), 0, 2));
        }
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

        <div>
            <a
                href="{{ route('admin.tenants.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-[0.98]"
            >
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Back to Tenants
            </a>
        </div>

        {{-- Profile card --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:p-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-2xl font-bold text-white shadow-md shadow-indigo-500/30 ring-4 ring-indigo-100">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 space-y-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $u?->name }}</h1>
                            <p class="mt-1 text-sm text-slate-500">{{ $u?->email }}</p>
                            <p class="mt-2 text-sm font-medium text-slate-700">{{ $tenant->phone_number }}</p>
                        </div>
                        <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Emergency contact</p>
                                <p class="mt-0.5 font-medium text-slate-800">{{ $tenant->emergency_contact_name ?: '—' }}</p>
                                <p>{{ $tenant->emergency_contact_number ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</p>
                                <p class="mt-0.5">{{ $tenant->full_address !== '' ? $tenant->full_address : '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-[0.98]"
                    @click="editOpen = true"
                >
                    Edit
                </button>
            </div>
        </div>

        {{-- Lease history --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Lease History</h2>
            <p class="mt-1 text-sm text-slate-500">Units, terms, and financial terms for this tenant.</p>

            @if($tenant->leases->isEmpty())
                <div class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-indigo-400 shadow-sm ring-1 ring-slate-100">
                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <p class="mt-4 text-base font-semibold text-slate-800">No leases on file</p>
                    <p class="mt-2 max-w-md text-sm text-slate-500">When a lease is created for this tenant, it will appear here with unit, property, and rent details.</p>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Property</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Start Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">End Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Monthly Rent</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Deposit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($tenant->leases as $lease)
                                    @php
                                        $unit = $lease->unit;
                                        $prop = $unit?->property;
                                        $statusClass = match ($lease->status) {
                                            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                            'completed' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                            'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
                                            default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                        };
                                    @endphp
                                    <tr class="transition-colors duration-200 hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $unit?->unit_number ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $prop?->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $lease->start_date?->format('M d, Y') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $lease->end_date?->format('M d, Y') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-800">₱{{ number_format((float) $lease->monthly_rent, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                            @if($lease->deposit_amount !== null)
                                                ₱{{ number_format((float) $lease->deposit_amount, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ ucfirst($lease->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Payment history --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">Payment History</h2>
            <p class="mt-1 text-sm text-slate-500">Payments linked to this tenant’s leases.</p>

            @if($tenant->payments->isEmpty())
                <div class="mt-10 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-indigo-400 shadow-sm ring-1 ring-slate-100">
                        <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <p class="mt-4 text-base font-semibold text-slate-800">No payments yet</p>
                    <p class="mt-2 max-w-md text-sm text-slate-500">Recorded rent payments will show due dates, amounts, and verification status here.</p>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-xl border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Verified At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($tenant->payments as $payment)
                                    @php
                                        $payStatus = match ($payment->status) {
                                            'paid' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                            'pending' => 'bg-amber-50 text-amber-800 ring-amber-100',
                                            'late' => 'bg-orange-50 text-orange-800 ring-orange-100',
                                            'rejected' => 'bg-rose-50 text-rose-800 ring-rose-100',
                                            default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                        };
                                    @endphp
                                    <tr class="transition-colors duration-200 hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->due_date?->format('M d, Y') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $payStatus }}">{{ ucfirst($payment->status) }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->verified_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Edit modal --}}
        <div
            x-show="editOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden"
            aria-labelledby="tenant-show-edit-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200" @click.stop>
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 id="tenant-show-edit-title" class="text-lg font-semibold text-slate-900">Edit tenant</h3>
                            <p class="mt-1 text-sm text-slate-500">Update account and contact details.</p>
                        </div>
                        <button type="button" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" @click="editOpen = false" aria-label="Close">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit_show">

                        <div class="space-y-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Account info</p>
                            <div>
                                <label for="show-edit-name" class="mb-1.5 block text-sm font-medium text-slate-700">Full name <span class="text-rose-500">*</span></label>
                                <input id="show-edit-name" name="name" type="text" value="{{ old('_form') === 'edit_show' ? old('name') : $u?->name }}" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('name') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('name')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="show-edit-email" class="mb-1.5 block text-sm font-medium text-slate-700">Email <span class="text-rose-500">*</span></label>
                                <input id="show-edit-email" name="email" type="email" value="{{ old('_form') === 'edit_show' ? old('email') : $u?->email }}" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('email') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('email')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact info</p>
                            <div>
                                <label for="show-edit-phone" class="mb-1.5 block text-sm font-medium text-slate-700">Phone number <span class="text-rose-500">*</span></label>
                                <input id="show-edit-phone" name="phone_number" type="text" value="{{ old('_form') === 'edit_show' ? old('phone_number') : $tenant->phone_number }}" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('phone_number') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('phone_number')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="show-edit-emergency-name" class="mb-1.5 block text-sm font-medium text-slate-700">Emergency contact name</label>
                                <input id="show-edit-emergency-name" name="emergency_contact_name" type="text" value="{{ old('_form') === 'edit_show' ? old('emergency_contact_name') : $tenant->emergency_contact_name }}" class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('emergency_contact_name') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('emergency_contact_name')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="show-edit-emergency-phone" class="mb-1.5 block text-sm font-medium text-slate-700">Emergency contact number</label>
                                <input id="show-edit-emergency-phone" name="emergency_contact_number" type="text" value="{{ old('_form') === 'edit_show' ? old('emergency_contact_number') : $tenant->emergency_contact_number }}" class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('emergency_contact_number') border-rose-300 ring-1 ring-rose-200 @enderror" />
                                @error('emergency_contact_number')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-psgc-address
                                    :province="old('_form') === 'edit_show' ? old('province', $tenant->province) : $tenant->province"
                                    :city="old('_form') === 'edit_show' ? old('city', $tenant->city) : $tenant->city"
                                    :barangay="old('_form') === 'edit_show' ? old('barangay', $tenant->barangay) : $tenant->barangay"
                                    :addressLine1="old('_form') === 'edit_show' ? old('address_line1', $tenant->address_line1) : $tenant->address_line1"
                                    :addressLine2="old('_form') === 'edit_show' ? old('address_line2', $tenant->address_line2 ?? '') : ($tenant->address_line2 ?? '')"
                                    :required="false"
                                />
                                @foreach (['province', 'city', 'barangay', 'address_line1', 'address_line2'] as $_addrField)
                                    @error($_addrField)
                                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                            <button type="button" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98]" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="inline-flex justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 active:scale-[0.98]">Update Tenant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
