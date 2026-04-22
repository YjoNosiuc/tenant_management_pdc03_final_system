@extends('layouts.admin', ['title' => 'Payment Details'])

@section('title', 'Payment Details')

@section('content')
    @php
        $user = $payment->lease?->tenant?->user;
        $tenant = $payment->lease?->tenant;
        $unit = $payment->lease?->unit;
        $property = $unit?->property;
        $status = $payment->status;
        if ($status === 'paid' && $payment->verified_at) {
            $badgeDot = 'bg-blue-500';
            $badgeShell = 'bg-blue-50 text-blue-800 ring-blue-100';
            $badgeLabel = 'Verified';
        } elseif ($status === 'paid') {
            $badgeDot = 'bg-emerald-500';
            $badgeShell = 'bg-emerald-50 text-emerald-800 ring-emerald-100';
            $badgeLabel = 'Paid';
        } else {
            $badgeDot = match ($status) {
                'pending' => 'bg-amber-500',
                'late', 'rejected' => 'bg-rose-500',
                default => 'bg-slate-400',
            };
            $badgeShell = match ($status) {
                'pending' => 'bg-amber-50 text-amber-800 ring-amber-100',
                'late', 'rejected' => 'bg-rose-50 text-rose-800 ring-rose-100',
                default => 'bg-slate-50 text-slate-700 ring-slate-100',
            };
            $badgeLabel = match ($status) {
                'pending' => 'Pending',
                'late' => 'Late',
                'rejected' => 'Rejected',
                default => ucfirst((string) $status),
            };
        }
        $isOverdue = $payment->due_date
            && $payment->due_date->lt(now()->startOfDay())
            && $status !== 'paid';
        $proofBasename = $payment->proof_of_payment ? basename($payment->proof_of_payment) : '';
        $tenantInitials = collect(preg_split('/\s+/', trim((string) ($user?->name ?? ''))))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        $tenantInitials = $tenantInitials !== '' ? $tenantInitials : mb_strtoupper(mb_substr((string) ($user?->email ?? 'TN'), 0, 2));
    @endphp

    <div
        class="mx-auto max-w-6xl"
        x-data="{
            rejectOpen: false,
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }}
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 3000);
            @endif
            @if($errors->has('remarks'))
                rejectOpen = true;
            @endif
        "
        @keydown.escape.window="rejectOpen = false"
    >
        <div x-show="flashVisible" x-cloak class="mb-6 space-y-3">
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

        <a href="{{ route('admin.payments.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors duration-150 hover:text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Payments
        </a>

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-indigo-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Payment #{{ $payment->id }}</h1>
                    <p class="text-sm text-slate-400">{{ $payment->created_at->format('M d, Y · h:i A') }}</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold ring-1 {{ $badgeShell }}">
                <span class="h-2 w-2 rounded-full {{ $badgeDot }}"></span>
                {{ $badgeLabel }}
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                {{-- Parties --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Parties Involved</h2>
                    <div class="grid gap-8 sm:grid-cols-2">
                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Tenant</p>
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                    {{ $tenantInitials }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-base font-semibold text-slate-900">{{ $user?->name ?? '—' }}</p>
                                    <p class="text-sm text-slate-500">{{ $user?->email ?? '—' }}</p>
                                    <p class="mt-1 flex items-center gap-1 text-sm text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                        {{ $tenant?->phone_number ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Unit</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ $unit?->unit_number ?? '—' }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">{{ $property?->name ?? '—' }}</p>
                            @if($unit?->unit_type)
                                <span class="mt-2 inline-block rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ ucfirst(str_replace('_', ' ', $unit->unit_type)) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payment information --}}
                <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Payment Information</h2>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Amount due</p>
                        <p class="mt-1 text-4xl font-extrabold tracking-tight text-slate-900">₱{{ number_format((float) $payment->amount_paid, 2) }}</p>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Due date</span>
                            <span class="flex items-center gap-1.5 text-sm font-medium {{ $isOverdue ? 'text-rose-600' : 'text-slate-700' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                @if($isOverdue)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-rose-500" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                @endif
                                {{ $payment->due_date?->format('M d, Y') ?? '—' }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment date</span>
                            <span class="flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('M d, Y') }}
                                @else
                                    <span class="italic text-slate-400">Not yet paid</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Verified at</span>
                            <span class="flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @if($payment->verified_at)
                                    {{ $payment->verified_at->format('M d, Y · h:i A') }}
                                @else
                                    <span class="italic text-slate-400">Awaiting verification</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @if($payment->remarks && $payment->status !== 'rejected')
                        <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-amber-600">Remarks</p>
                            <p class="text-sm text-amber-800">{{ $payment->remarks }}</p>
                        </div>
                    @endif
                </div>

                {{-- Proof --}}
                <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Proof of Payment</h2>
                        @if($proofUrl)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Submitted</span>
                        @endif
                    </div>
                    @if($proofUrl)
                        <div class="overflow-hidden rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                            <img src="{{ $proofUrl }}" alt="Proof of payment" class="max-h-80 w-full rounded-2xl object-contain" />
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2 text-sm text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m6.713-8.963 1.158 1.158a1.5 1.5 0 0 1-2.122 2.122L13.5 13.5" /></svg>
                                <span class="truncate font-medium text-slate-700">{{ $proofBasename }}</span>
                            </div>
                            <a href="{{ $proofUrl }}" download class="inline-flex items-center justify-center rounded-xl border-2 border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition-all duration-150 hover:bg-indigo-50">Download</a>
                        </div>
                    @else
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="mx-auto h-12 w-12 text-slate-300" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008H12V8.25Z" /></svg>
                            <p class="mt-4 font-medium text-slate-500">No proof submitted yet</p>
                            <p class="mt-1 text-sm text-slate-400">The tenant hasn't uploaded a receipt yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4 lg:col-span-1">
                <div class="sticky top-6 space-y-4">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Actions</h2>
                        @if($payment->status === 'pending')
                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-emerald-500 active:scale-[0.98]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    Verify Payment
                                </button>
                            </form>
                            <button type="button" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition-all duration-150 hover:bg-rose-50 active:scale-[0.98]" @click="rejectOpen = true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                Reject Payment
                            </button>
                            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                                <p class="text-xs leading-relaxed text-slate-500">
                                    <span class="font-semibold text-slate-700">Verify</span> if the proof matches the amount due.
                                    <span class="font-semibold text-slate-700">Reject</span> if the proof is unclear or incorrect.
                                </p>
                            </div>
                        @elseif($payment->status === 'paid')
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mb-2 h-8 w-8 text-emerald-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <p class="font-semibold text-emerald-800">Payment Verified</p>
                                <p class="mt-1 text-xs text-emerald-600">{{ $payment->verified_at?->format('M d, Y · h:i A') ?? 'Recorded as paid' }}</p>
                            </div>
                        @elseif($payment->status === 'rejected')
                            <div class="rounded-xl border border-rose-100 bg-rose-50 p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mb-2 h-8 w-8 text-rose-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <p class="font-semibold text-rose-800">Payment Rejected</p>
                                <p class="mt-1 text-xs text-rose-600">{{ $payment->remarks }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Timeline</h2>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500"></div>
                                <div class="mt-1 min-h-[2.5rem] w-px flex-1 bg-slate-100"></div>
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-semibold text-slate-700">Payment Record Created</p>
                                <p class="text-xs text-slate-400">{{ $payment->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $payment->payment_date ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                                <div class="mt-1 min-h-[2.5rem] w-px flex-1 bg-slate-100"></div>
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-semibold text-slate-700">Proof Submitted</p>
                                <p class="text-xs text-slate-400">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'Pending' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                @php
                                    $t3 = $payment->status === 'rejected' ? 'bg-rose-500' : ($payment->verified_at ? 'bg-emerald-500' : 'bg-slate-200');
                                    $t3label = $payment->status === 'rejected' ? 'Rejected' : ($payment->verified_at ? $payment->verified_at->format('M d, Y') : 'Awaiting');
                                @endphp
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $t3 }}"></div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Admin Verification</p>
                                <p class="text-xs text-slate-400">{{ $t3label }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject modal --}}
        <div
            x-show="rejectOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reject-modal-title"
            @click.self="rejectOpen = false"
        >
            <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-100" @click.stop>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-rose-600" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <h3 id="reject-modal-title" class="mt-4 text-center text-xl font-bold text-slate-900">Reject this payment?</h3>
                <p class="mt-2 text-center text-sm text-slate-500">Please provide a reason. The tenant will be notified.</p>

                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="reject-remarks" class="mb-2 block text-sm font-semibold text-slate-700">
                            Reason for rejection <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="reject-remarks"
                            name="remarks"
                            rows="3"
                            required
                            placeholder="e.g. Proof of payment is unclear, amount doesn't match..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition-all duration-150 focus:border-rose-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400/20"
                        >{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-3">
                        <button type="button" class="w-full rounded-xl border border-slate-200 bg-white py-3 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 active:scale-[0.98]" @click="rejectOpen = false">Cancel</button>
                        <button type="submit" class="w-full rounded-xl bg-rose-600 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-rose-500 active:scale-[0.98]">Confirm Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
