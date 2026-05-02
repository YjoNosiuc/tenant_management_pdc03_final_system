@extends('layouts.tenant', ['title' => 'Payment Details'])

@section('title', 'Payment Details')

@section('content')
    @php
        $lease = $payment->lease;
        $unit = $lease?->unit;
        $property = $unit?->property;
        $proofPath = $payment->proof_of_payment;
        $isPdf = $proofPath && str_ends_with(strtolower($proofPath), '.pdf');
        $proofUrl = $proofPath ? \Illuminate\Support\Facades\Storage::url($proofPath) : null;

        $status = $payment->status;
        $statusConfig = [
            'pending' => ['bg-amber-50 text-amber-700 ring-1 ring-amber-100', 'bg-amber-400', 'Pending'],
            'verifying' => ['bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100', 'bg-indigo-400 animate-pulse', 'Verifying'],
            'paid' => ['bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100', 'bg-emerald-400', 'Paid'],
            'late' => ['bg-rose-50 text-rose-700 ring-1 ring-rose-100', 'bg-rose-400', 'Late'],
            'rejected' => ['bg-red-50 text-red-700 ring-1 ring-red-100', 'bg-red-400', 'Rejected'],
        ];
        $cfg = $statusConfig[$status] ?? $statusConfig['pending'];
        $badgeDot = $cfg[1];
        $badgeShell = $cfg[0];
        $badgeLabel = $cfg[2];

        $isOverdue = $payment->due_date
            && $payment->due_date->lt(now()->startOfDay())
            && $status !== 'paid';

        $paymentCfg = fn (string $s) => $statusConfig[$s] ?? $statusConfig['pending'];

        $leaseDot = fn (?string $s) => match ($s) {
            'active' => 'bg-emerald-500',
            'completed' => 'bg-blue-500',
            'terminated' => 'bg-rose-500',
            default => 'bg-slate-400',
        };
        $leaseShell = fn (?string $s) => match ($s) {
            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'completed' => 'bg-blue-50 text-blue-800 ring-blue-100',
            'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
            default => 'bg-slate-50 text-slate-700 ring-slate-100',
        };
    @endphp

    <div
        class="mx-auto max-w-6xl"
        x-data="{
            fileName: '',
            previewUrl: null,
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            resetPreview() {
                this.fileName = '';
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
                ['show-proof-input', 'show-proof-input-resubmit'].forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            },
            onFileChange(e) {
                const file = e.target.files[0];
                this.fileName = file?.name || '';
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
                if (file && file.type.startsWith('image/')) {
                    this.previewUrl = URL.createObjectURL(file);
                }
            },
            onDrop(e) {
                const input = document.getElementById('show-proof-input') || document.getElementById('show-proof-input-resubmit');
                if (!input || !e.dataTransfer?.files?.length) return;
                input.files = e.dataTransfer.files;
                this.onFileChange({ target: input });
            }
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 4000);
            @endif
        "
    >
        <div x-show="flashVisible" x-cloak class="mb-6 space-y-3">
            @if(session('success'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm ring-1 ring-emerald-100" role="alert">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="rounded-lg p-1 text-emerald-600 hover:bg-emerald-100" @click="flashVisible = false" aria-label="Dismiss">×</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm ring-1 ring-rose-100" role="alert">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="rounded-lg p-1 text-rose-600 hover:bg-rose-100" @click="flashVisible = false" aria-label="Dismiss">×</button>
                </div>
            @endif
        </div>

        <a href="{{ route('tenant.payments.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-indigo-600">
            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to My Payments
        </a>

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100">
                    <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Payment #{{ $payment->id }}</h1>
                    <p class="text-sm text-slate-400">{{ $payment->created_at->format('M d, Y · h:i A') }}</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold {{ $badgeShell }}">
                <span class="h-2 w-2 rounded-full {{ $badgeDot }}"></span>
                {{ $badgeLabel }}
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <p class="mb-4 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment information</p>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Amount due</p>
                        <p class="mt-1 text-4xl font-extrabold tracking-tight text-slate-900">₱{{ number_format((float) $payment->amount_paid, 2) }}</p>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Due date</span>
                            <span class="flex flex-wrap items-center gap-1.5 text-sm font-medium {{ $isOverdue ? 'text-rose-600' : 'text-slate-700' }}">
                                <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                @if($isOverdue)
                                    <svg class="h-4 w-4 shrink-0 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                @endif
                                {{ $payment->due_date?->format('M d, Y') ?? '—' }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Payment date</span>
                            <span class="flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('M d, Y') }}
                                @else
                                    <span class="italic text-slate-400">Not yet paid</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Status</span>
                            @php $psc = $paymentCfg($payment->status); @endphp
                            <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $psc[0] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $psc[1] }}"></span>
                                {{ $psc[2] }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Verified at</span>
                            <span class="flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @if($payment->verified_at)
                                    {{ $payment->verified_at->format('M d, Y · h:i A') }}
                                @else
                                    <span class="italic text-slate-400">Awaiting verification</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($payment->status === 'paid' && $payment->verified_at)
                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <svg class="h-5 w-5 shrink-0 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Payment Verified</p>
                                <p class="text-xs text-emerald-600">{{ $payment->verified_at->format('M d, Y · h:i A') }}</p>
                            </div>
                        </div>
                    @elseif($payment->status === 'paid')
                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <svg class="h-5 w-5 shrink-0 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Payment recorded</p>
                                <p class="text-xs text-emerald-600">Your payment is marked as paid.</p>
                            </div>
                        </div>
                    @endif

                    @if($payment->status === 'rejected')
                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-100 bg-red-50 p-4">
                            <svg class="h-5 w-5 shrink-0 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Payment Rejected</p>
                                <p class="text-xs text-red-600">{{ $payment->remarks ?: 'Please upload a new proof or contact your property manager.' }}</p>
                            </div>
                        </div>
                    @elseif($payment->status === 'verifying')
                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                            <svg class="h-5 w-5 shrink-0 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">Under Review</p>
                                <p class="text-xs text-indigo-600">Your landlord is reviewing your proof of payment.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Proof of Payment</p>
                        @if($payment->proof_of_payment)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Submitted</span>
                        @endif
                    </div>

                    @if($payment->proof_of_payment)
                        @if($isPdf)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <svg class="h-8 w-8 shrink-0 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">PDF Receipt</p>
                                    <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-indigo-600 hover:underline">Open PDF →</a>
                                </div>
                            </div>
                        @else
                            <div class="overflow-hidden rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                                <img src="{{ $proofUrl }}" alt="Proof of payment" class="max-h-72 w-full object-contain" />
                            </div>
                        @endif
                        @if($payment->payment_date)
                            <p class="mt-3 text-xs text-slate-400">Submitted on {{ $payment->payment_date->format('M d, Y') }}</p>
                        @endif
                    @elseif(in_array($payment->status, ['pending', 'verifying', 'rejected'], true))
                        @if(! $isEarliestUnpaid && $payment->status === 'pending' && $earliestUnpaid)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-amber-600" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <h3 class="text-center text-base font-bold text-amber-900">
                                    Complete your previous payment first
                                </h3>
                                <p class="mt-2 text-center text-sm leading-relaxed text-amber-700">
                                    Please settle your
                                    <span class="font-bold">
                                        {{ $earliestUnpaid->due_date->format('M d, Y') }}
                                    </span>
                                    payment first before submitting proof for this month.
                                    Payments must be made in order.
                                </p>
                                <div class="my-4 border-t border-amber-200"></div>
                                <a
                                    href="{{ route('tenant.payments.show', $earliestUnpaid) }}"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-amber-400 active:scale-[0.98]"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 21Z" />
                                    </svg>
                                    Go to {{ $earliestUnpaid->due_date->format('M d, Y') }} Payment →
                                </a>
                                <p class="mt-3 text-center text-xs text-amber-600">
                                    You can pay ahead of time — just settle each month in order.
                                </p>
                            </div>
                        @else
                            @if(in_array($payment->status, ['verifying', 'rejected'], true))
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    @if($payment->status === 'verifying')
                                        Resubmit Proof
                                    @else
                                        Upload New Proof
                                    @endif
                                </p>
                            @endif
                            <form method="POST" action="{{ route('tenant.payments.submitProof', $payment) }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <label
                                    class="relative flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30"
                                    x-on:dragover.prevent
                                    x-on:drop.prevent="onDrop($event)"
                                >
                                    <input
                                        id="show-proof-input"
                                        type="file"
                                        name="proof"
                                        required
                                        accept="image/*,application/pdf"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        @change="onFileChange($event)"
                                    />
                                    <div x-show="!fileName" class="flex flex-col items-center px-4 text-center" x-cloak>
                                        <svg class="mb-2 h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.192 4.5 4.5 0 0 1 1.5 8.438M12 16.5V9.75m0 0-3 3m3-3 3 3" /></svg>
                                        <p class="text-sm font-medium text-slate-600">Drop your file here or <span class="text-indigo-600">browse</span></p>
                                        <p class="mt-1 text-xs text-slate-400">JPG, PNG or PDF · Max 5MB</p>
                                    </div>
                                    <div x-show="fileName" class="flex flex-col items-center px-4 text-center" x-cloak>
                                        <svg class="mb-2 h-10 w-10 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        <p class="text-sm font-semibold text-emerald-700" x-text="fileName"></p>
                                        <p class="mt-1 text-xs text-slate-400">Click to change file</p>
                                    </div>
                                </label>
                                @error('proof')
                                    <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                                <div x-show="previewUrl" x-cloak class="rounded-xl border border-slate-100 bg-slate-50 p-2">
                                    <p class="mb-2 text-xs font-medium text-slate-500">Preview</p>
                                    <img :src="previewUrl" alt="Preview" class="max-h-48 w-full rounded-lg object-contain" />
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 active:scale-[0.98] sm:w-auto">Submit Proof</button>
                            </form>
                        @endif
                    @else
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-12 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-600">No proof submitted</p>
                            <p class="mt-1 text-xs text-slate-400">No receipt has been uploaded for this payment.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-6 space-y-4">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <p class="mb-4 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Your Lease</p>
                        <p class="text-3xl font-extrabold text-indigo-600">{{ $unit?->unit_number ?? '—' }}</p>
                        <p class="mt-2 flex items-center gap-1 text-sm font-medium text-slate-600">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H18v-.008Zm0 3h.008v.008H18v-.008Zm0 3h.008v.008H18v-.008ZM15.75 21h-7.5a2.25 2.25 0 0 1-2.25-2.25v-8.844a2.25 2.25 0 0 1 1.183-1.98l7.5-4.166a2.25 2.25 0 0 1 2.134 0l7.5 4.166a2.25 2.25 0 0 1 1.183 1.98V18.75A2.25 2.25 0 0 1 15.75 21Z" /></svg>
                            {{ $property?->name ?? '—' }}
                        </p>
                        @if($unit?->unit_type)
                            <span class="mt-2 inline-block rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $unit->unit_type }}</span>
                        @endif
                        <div class="my-4 border-t border-slate-100"></div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Monthly rent</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">₱{{ $lease ? number_format((float) $lease->monthly_rent, 2) : '—' }}</p>
                        @if($lease)
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                <span>{{ $lease->start_date?->format('M d, Y') }}</span>
                                <span class="text-slate-300">→</span>
                                <span>{{ $lease->end_date?->format('M d, Y') }}</span>
                            </div>
                            <div class="mt-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $leaseShell($lease->status) }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $leaseDot($lease->status) }}"></span>
                                    {{ ucfirst($lease->status) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <p class="mb-4 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Timeline</p>
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500"></div>
                                <div class="mt-1 min-h-[2.5rem] w-px flex-1 bg-slate-100"></div>
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-semibold text-slate-700">Payment Created</p>
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
                                    $t3 = match (true) {
                                        $payment->status === 'rejected' => 'bg-red-500',
                                        $payment->status === 'verifying' => 'bg-indigo-500 animate-pulse',
                                        $payment->status === 'paid' => 'bg-emerald-500',
                                        default => 'bg-slate-200',
                                    };
                                    $t3label = match (true) {
                                        $payment->status === 'rejected' => 'Rejected',
                                        $payment->status === 'verifying' => 'Under review',
                                        $payment->status === 'paid' => ($payment->verified_at ? $payment->verified_at->format('M d, Y') : 'Completed'),
                                        default => 'Awaiting',
                                    };
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
    </div>
@endsection
