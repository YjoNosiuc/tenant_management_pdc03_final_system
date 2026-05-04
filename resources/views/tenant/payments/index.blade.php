@extends('layouts.tenant', ['title' => 'My Payments'])

@section('title', 'My Payments')

@section('content')
    @php
        $statusConfig = [
            'pending' => ['bg-amber-50 text-amber-700 ring-1 ring-amber-100', 'bg-amber-400', 'Pending'],
            'verifying' => ['bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100', 'bg-indigo-400 animate-pulse', 'Verifying'],
            'verifying_late' => ['bg-rose-50 text-rose-700 ring-1 ring-rose-100', 'bg-rose-400 animate-pulse', 'Verifying (Late)'],
            'paid' => ['bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100', 'bg-emerald-400', 'Paid'],
            'late' => ['bg-rose-50 text-rose-700 ring-1 ring-rose-100', 'bg-rose-400', 'Late'],
            'rejected' => ['bg-red-50 text-red-700 ring-1 ring-red-100', 'bg-red-400', 'Rejected'],
        ];
        $paymentCfg = fn (string $status) => $statusConfig[$status] ?? $statusConfig['pending'];
        $hasAnyPayments = collect($paymentsByLease ?? [])->contains(fn ($g) => $g['payments']->total() > 0);
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            uploadOpen: false,
            uploadPaymentId: null,
            uploadRoute: '',
            fileName: '',
            previewUrl: null,
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            resetPreview() {
                this.fileName = '';
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
                const el = document.getElementById('payments-proof-input');
                if (el) el.value = '';
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
            handleFile(e) {
                this.onFileChange(e);
            },
            onDrop(e) {
                const input = document.getElementById('payments-proof-input');
                if (!input || !e.dataTransfer?.files?.length) return;
                input.files = e.dataTransfer.files;
                this.onFileChange({ target: input });
            }
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 4000);
            @endif
            @if($errors->has('proof') && old('upload_payment_id'))
                @php $oldUploadPid = (int) old('upload_payment_id'); @endphp
                uploadPaymentId = {{ $oldUploadPid }};
                uploadRoute = @js(url('/tenant/payments/'.$oldUploadPid.'/proof'));
                uploadOpen = true;
            @endif
        "
        @keydown.escape.window="uploadOpen = false; resetPreview()"
    >
        <div x-show="flashVisible" x-cloak class="space-y-3">
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

        <div>
            <div class="flex items-center gap-2">
                <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Payments</h1>
            </div>
            <p class="mt-1 pl-3 text-sm text-slate-500">Your complete payment history</p>
        </div>

        @if(count($leases) > 1)
            <form method="GET" action="{{ route('tenant.payments.index') }}" class="mb-6">
                <div class="flex items-center gap-3 flex-wrap">
                    <label class="text-sm font-semibold text-slate-700">Filter by Unit:</label>
                    <select name="lease_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">All Units</option>
                        @foreach($leases as $lease)
                            <option value="{{ $lease->id }}"
                                {{ (string) $selectedLeaseId === (string) $lease->id ? 'selected' : '' }}>
                                Unit {{ $lease->unit?->unit_number }} — {{ $lease->unit?->property?->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($selectedLeaseId)
                        <a href="{{ route('tenant.payments.index') }}"
                           class="text-xs font-semibold text-rose-600 hover:text-rose-500">Clear</a>
                    @endif
                </div>
            </form>
        @endif

        @foreach($paymentsByLease as $group)
            @php
                $groupLease = $group['lease'];
                $groupPayments = $group['payments'];
                if ($selectedLeaseId && (string) $selectedLeaseId !== (string) $groupLease->id) {
                    continue;
                }
                $earliestUnpaidId = $groupLease->payments()
                    ->whereNotIn('status', ['paid'])
                    ->orderBy('due_date')
                    ->value('id');
            @endphp

            <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100">
                        <svg class="h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">
                            Unit {{ $groupLease->unit?->unit_number }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ $groupLease->unit?->property?->name }} ·
                            ₱{{ number_format((float) $groupLease->monthly_rent, 0) }}/mo
                        </p>
                    </div>
                </div>
                <span class="text-xs text-slate-400">
                    {{ $groupPayments->total() }} payments
                </span>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden mb-6">
                @if($groupPayments->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <p class="text-sm font-semibold text-slate-600">No payment records yet</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Due Date</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Amount</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Proof</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-400">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 bg-white">
                                @foreach($groupPayments as $payment)
                                    @php
                                        $cfg = $paymentCfg($payment->status);
                                        $isEarliest = $payment->id === $earliestUnpaidId;
                                    @endphp
                                    <tr class="hover:bg-indigo-50/30 transition-colors duration-150">
                                        <td class="whitespace-nowrap px-4 py-3.5">
                                            <p class="font-semibold {{ $payment->isLateType() ? 'text-rose-600' : 'text-slate-800' }}">
                                                {{ $payment->due_date?->format('M d, Y') }}
                                            </p>
                                            @if($payment->payment_date)
                                                <p class="text-xs text-slate-400">
                                                    Paid: {{ $payment->payment_date?->format('M d, Y') }}
                                                </p>
                                            @else
                                                <p class="text-xs text-slate-400 italic">Not yet paid</p>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5">
                                            <p class="font-bold text-indigo-700">
                                                ₱{{ number_format((float) $payment->total_amount_due, 2) }}
                                            </p>
                                            @if($payment->hasLateFee())
                                                <p class="text-xs text-rose-500">
                                                    +₱{{ number_format((float) $payment->late_fee_amount, 2) }} late fee
                                                </p>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $cfg[0] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $cfg[1] }}"></span>
                                                {{ $cfg[2] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5">
                                            @if($payment->status === 'paid')
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                                    <svg class="h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                    Paid
                                                </span>
                                            @elseif(in_array($payment->status, ['verifying', 'verifying_late'], true))
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs font-semibold text-indigo-600 flex items-center gap-1">
                                                        <svg class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                        Verifying
                                                    </span>
                                                    @if($isEarliest)
                                                        <button type="button"
                                                            @click="resetPreview(); uploadPaymentId = {{ $payment->id }}; uploadRoute = @js(route('tenant.payments.submitProof', $payment)); uploadOpen = true"
                                                            class="text-xs font-medium text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                                            Resubmit
                                                        </button>
                                                    @endif
                                                </div>
                                            @elseif($payment->status === 'rejected')
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs font-semibold text-red-600">Rejected</span>
                                                    @if($isEarliest)
                                                        <button type="button"
                                                            @click="resetPreview(); uploadPaymentId = {{ $payment->id }}; uploadRoute = @js(route('tenant.payments.submitProof', $payment)); uploadOpen = true"
                                                            class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                                            Resubmit
                                                        </button>
                                                    @endif
                                                </div>
                                            @elseif(in_array($payment->status, ['pending', 'late'], true))
                                                @if($isEarliest)
                                                    <button type="button"
                                                        @click="resetPreview(); uploadPaymentId = {{ $payment->id }}; uploadRoute = @js(route('tenant.payments.submitProof', $payment)); uploadOpen = true"
                                                        class="inline-flex items-center gap-1.5 rounded-lg {{ $payment->status === 'late' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }} px-3 py-1.5 text-xs font-semibold transition-all">
                                                        <svg class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                                        {{ $payment->status === 'late' ? 'Pay Now' : ($payment->due_date && $payment->due_date->copy()->startOfDay()->gt(now()->startOfDay()) ? 'Pay Early' : 'Pay Now') }}
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400 cursor-not-allowed">
                                                        <svg class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                        Pay previous first
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                            <a href="{{ route('tenant.payments.show', $payment) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-150"
                                               title="View" aria-label="View">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($groupPayments->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100">
                            {{ $groupPayments->appends(request()->query())->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endforeach

        @if($leases->isNotEmpty() && $hasAnyPayments)
            <div
                x-show="uploadOpen"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm"
                x-transition.opacity
                @click.self="uploadOpen = false; resetPreview()"
                role="dialog"
                aria-modal="true"
            >
                <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="relative flex items-start justify-between gap-3 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-white">Submit Proof of Payment</h3>
                                <p class="mt-0.5 text-sm text-indigo-100">Upload your receipt or screenshot</p>
                            </div>
                        </div>
                        <button type="button" class="rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20" @click="uploadOpen = false; resetPreview()" aria-label="Close">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        x-bind:action="uploadRoute"
                        class="px-6 py-5"
                    >
                        @csrf
                        <input type="hidden" name="upload_payment_id" :value="uploadPaymentId" />
                        <label
                            class="relative flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30"
                            x-on:dragover.prevent
                            x-on:drop.prevent="onDrop($event)"
                        >
                            <input
                                id="payments-proof-input"
                                type="file"
                                name="proof"
                                required
                                accept="image/*,application/pdf"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                @change="handleFile($event)"
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
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                        <div x-show="previewUrl" x-cloak class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-2">
                            <p class="mb-2 text-xs font-medium text-slate-500">Preview</p>
                            <img :src="previewUrl" alt="Preview" class="max-h-48 w-full rounded-lg object-contain" />
                        </div>
                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto" @click="uploadOpen = false; resetPreview()">Cancel</button>
                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 sm:w-auto" :disabled="!uploadPaymentId">Submit Proof</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
