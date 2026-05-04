@extends('layouts.tenant', ['title' => 'My Dashboard'])

@section('title', 'My Dashboard')

@push('head')
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

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

        $leaseDot = fn (?string $status) => match ($status) {
            'active' => 'bg-emerald-500',
            'completed' => 'bg-blue-500',
            'terminated' => 'bg-rose-500',
            default => 'bg-slate-400',
        };
        $leaseShell = fn (?string $status) => match ($status) {
            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'completed' => 'bg-blue-50 text-blue-800 ring-blue-100',
            'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
            default => 'bg-slate-50 text-slate-700 ring-slate-100',
        };
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            uploadOpen: false,
            uploadPaymentId: null,
            uploadRoute: '',
            uploadUnitLabel: '',
            uploadAmountDue: '',
            fileName: '',
            previewUrl: null,
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            resetPreview() {
                this.fileName = '';
                this.previewUrl = null;
                const el = document.getElementById('dashboard-proof-input');
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
                const input = document.getElementById('dashboard-proof-input');
                if (!input || !e.dataTransfer?.files?.length) return;
                input.files = e.dataTransfer.files;
                this.onFileChange({ target: input });
            }
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 4000);
            @endif
            @if($errors->has('proof'))
                @php
                    $errPay = null;
                    foreach ($leaseCards as $c) {
                        $np = $c['nextPayment'] ?? null;
                        if ($np && in_array($np->status, ['pending', 'late', 'verifying', 'verifying_late', 'rejected'], true)) {
                            $errPay = $np;
                            break;
                        }
                    }
                @endphp
                @if($errPay)
                uploadPaymentId = {{ (int) $errPay->id }};
                uploadRoute = @js(route('tenant.payments.submitProof', $errPay));
                uploadUnitLabel = @js('Unit '.($errPay->lease?->unit?->unit_number ?? '—'));
                uploadAmountDue = @js(number_format((float) $errPay->getBreakdown()['total_amount_due'], 2));
                uploadOpen = true;
                @endif
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

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-700 to-violet-700 p-8">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-8 right-20 h-28 w-28 rounded-full bg-white/5"></div>
            <div class="absolute top-1/2 right-8 h-16 w-16 rounded-full bg-white/5"></div>

            <div class="relative flex items-center justify-between gap-6">
                <div>
                    @php
                        $hour = now('Asia/Manila')->hour;
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                    @endphp
                    <p class="text-sm font-medium text-indigo-200">{{ $greeting }} 👋</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-white">{{ auth()->user()->name }}</h1>
                    <p class="mt-2 text-sm text-indigo-200">{{ now('Asia/Manila')->format('l, M d, Y') }}</p>
                </div>
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold text-white ring-2 ring-white/20">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                </div>
            </div>

            @if($activeLeases->isNotEmpty())
                @php $heroLease = $activeLeases->first(); @endphp
                <div class="relative mt-6 flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 backdrop-blur-sm">
                        <svg class="h-4 w-4 shrink-0 text-indigo-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span class="text-sm font-semibold text-white">Unit {{ $heroLease->unit?->unit_number ?? '—' }}{{ $activeLeases->count() > 1 ? ' +' . ($activeLeases->count() - 1) : '' }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 backdrop-blur-sm">
                        <svg class="h-4 w-4 shrink-0 text-indigo-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        <span class="text-sm font-semibold text-white">₱{{ number_format((float) $heroLease->monthly_rent, 0) }}/mo</span>
                    </div>
                    @if($heroLease->end_date)
                        <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 backdrop-blur-sm">
                            <svg class="h-4 w-4 shrink-0 text-indigo-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                            <span class="text-sm font-semibold text-white">Until {{ $heroLease->end_date->format('M Y') }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if($allImages->count() > 0)
            @php
                $galleryUrls = $allImages->map(fn ($i) => \Illuminate\Support\Facades\Storage::url($i->image_path))->values()->all();
            @endphp
            <div class="rounded-3xl bg-white ring-1 ring-slate-100 shadow-sm overflow-hidden mb-6"
                 x-data="{
                     lightboxOpen: false,
                     currentIndex: 0,
                     images: @js($galleryUrls),
                     openLightbox(index) { this.currentIndex = index; this.lightboxOpen = true; },
                     prev() { this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.images.length - 1; },
                     next() { this.currentIndex = this.currentIndex < this.images.length - 1 ? this.currentIndex + 1 : 0; }
                 }">
                <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">My Unit</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            @if($lease && $activeLeases->count() === 1)
                                Unit {{ $lease->unit->unit_number }} · {{ $lease->unit->property->name }}
                            @else
                                Photos from your rented unit(s)
                            @endif
                        </p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $allImages->count() }} photo(s)</span>
                </div>

                <div class="flex gap-3 px-6 pb-6 overflow-x-auto scrollbar-hide">
                    @foreach($allImages as $index => $image)
                        <div class="group relative shrink-0 w-48 h-36 rounded-2xl overflow-hidden bg-slate-100 cursor-pointer"
                             @click="openLightbox({{ $index }})">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}"
                                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                 alt="Unit photo" />
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div x-show="lightboxOpen" x-cloak
                     class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center"
                     @keydown.escape.window="lightboxOpen = false"
                     @click.self="lightboxOpen = false">

                    <button type="button" @click="prev()"
                        class="absolute left-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-all"
                        aria-label="Previous">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>

                    <img :src="images[currentIndex]"
                         class="max-h-[80vh] max-w-4xl w-full object-contain rounded-xl px-16"
                         alt="" />

                    <button type="button" @click="next()"
                        class="absolute right-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-all"
                        aria-label="Next">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>

                    <button type="button" @click="lightboxOpen = false"
                        class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-all"
                        aria-label="Close">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>

                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white text-sm font-medium bg-black/40 rounded-full px-3 py-1">
                        <span x-text="currentIndex + 1"></span> / {{ $allImages->count() }}
                    </div>
                </div>
            </div>
        @endif

        @if($leaseCards->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-12 text-center mb-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mx-auto mb-4">
                    <svg class="h-7 w-7 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                </div>
                <p class="text-base font-semibold text-slate-700">No Active Leases</p>
                <p class="text-sm text-slate-400 mt-1">Contact your property manager to get started.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
                @foreach($leaseCards as $card)
                    @php
                        $cardLease = $card['lease'];
                        $cardPayment = $card['nextPayment'];
                        $breakdown = $cardPayment ? $cardPayment->getBreakdown() : null;
                        $hasLateFee = $cardPayment ? $cardPayment->hasLateFee() : false;
                    @endphp

                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-indigo-200 text-xs font-medium">
                                        {{ $cardLease->unit?->property?->name }}
                                    </p>
                                    <h3 class="text-white font-bold text-xl mt-0.5">
                                        Unit {{ $cardLease->unit?->unit_number }}
                                    </h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-indigo-200 text-xs">Monthly Rent</p>
                                    <p class="text-white font-extrabold text-lg">
                                        ₱{{ number_format((float) $cardLease->monthly_rent, 0) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-3 flex-wrap">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-white/15 px-2.5 py-1 text-xs font-medium text-white">
                                    <svg class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                    {{ $cardLease->start_date?->format('M d, Y') }} → {{ $cardLease->end_date?->format('M d, Y') }}
                                </span>
                                @if($cardLease->unit?->unit_type)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-white/15 px-2.5 py-1 text-xs font-semibold text-white">
                                        <svg class="h-3 w-3 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                        {{ $cardLease->unit->unit_type }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-3">
                                Next Payment Due
                            </p>

                            @if($cardPayment)
                                @if($hasLateFee && $breakdown)
                                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 space-y-2 mb-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500">Original Rent</span>
                                            <span class="text-xs font-semibold text-slate-700">
                                                ₱{{ number_format($breakdown['original_rent'], 2) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-rose-600 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 shrink-0 text-rose-500" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                                Late Fee
                                            </span>
                                            <span class="text-xs font-semibold text-rose-600">
                                                + ₱{{ number_format($breakdown['late_fee_amount'], 2) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-rose-200 pt-2 flex items-center justify-between">
                                            <span class="text-sm font-bold text-slate-900">Total Due</span>
                                            <span class="text-xl font-extrabold text-rose-700">
                                                ₱{{ number_format($breakdown['total_amount_due'], 2) }}
                                            </span>
                                        </div>
                                    </div>
                                @elseif($breakdown)
                                    <p class="text-3xl font-extrabold text-slate-900 mb-2">
                                        ₱{{ number_format($breakdown['total_amount_due'], 2) }}
                                    </p>
                                @endif

                                @php $cfg = $statusConfig[$cardPayment->status] ?? $statusConfig['pending']; @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $cfg[0] }} mb-3">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $cfg[1] }}"></span>
                                    {{ $cfg[2] }}
                                </span>

                                <p class="text-xs text-slate-400 mb-3 flex items-center gap-1">
                                    <svg class="h-3 w-3 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                                    Due: {{ $cardPayment->due_date?->format('M d, Y') }}
                                    @if($cardPayment->due_date && $cardPayment->due_date->copy()->startOfDay()->lt(now()->startOfDay()) && !in_array($cardPayment->status, ['paid'], true))
                                        <span class="text-rose-500 font-semibold">(Overdue)</span>
                                    @endif
                                </p>

                                @php $uploadRoute = route('tenant.payments.submitProof', $cardPayment); @endphp

                                @if(in_array($cardPayment->status, ['pending', 'late'], true))
                                    <button
                                        type="button"
                                        @click="resetPreview(); uploadPaymentId = {{ $cardPayment->id }}; uploadRoute = @js($uploadRoute); uploadUnitLabel = @js('Unit '.($cardLease->unit?->unit_number ?? '—')); uploadAmountDue = @js(number_format((float) $cardPayment->getBreakdown()['total_amount_due'], 2)); uploadOpen = true"
                                        class="w-full flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm active:scale-[0.98] transition-all duration-150
                                               {{ $cardPayment->status === 'late' ? 'bg-rose-600 hover:bg-rose-500' : 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:opacity-90' }}">
                                        <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                        {{ $cardPayment->status === 'late' ? 'Pay Now (Late)' : ($cardPayment->due_date && $cardPayment->due_date->copy()->startOfDay()->gt(now()->startOfDay()) ? 'Pay Early' : 'Upload Proof') }}
                                    </button>

                                @elseif(in_array($cardPayment->status, ['verifying', 'verifying_late'], true))
                                    <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3 mb-3">
                                        <p class="text-xs font-semibold text-indigo-800 flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 shrink-0 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            Under Review
                                        </p>
                                        <p class="text-xs text-indigo-600 mt-1">
                                            Your proof is being reviewed by your landlord.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="resetPreview(); uploadPaymentId = {{ $cardPayment->id }}; uploadRoute = @js($uploadRoute); uploadUnitLabel = @js('Unit '.($cardLease->unit?->unit_number ?? '—')); uploadAmountDue = @js(number_format((float) $cardPayment->getBreakdown()['total_amount_due'], 2)); uploadOpen = true"
                                        class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 active:scale-[0.98] transition-all">
                                        <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                        Resubmit Proof
                                    </button>

                                @elseif($cardPayment->status === 'rejected')
                                    <div class="rounded-xl bg-red-50 border border-red-100 p-3 mb-3">
                                        <p class="text-xs font-semibold text-red-800">Proof Rejected</p>
                                        @if($cardPayment->remarks)
                                            <p class="text-xs text-red-600 mt-1">{{ $cardPayment->remarks }}</p>
                                        @endif
                                    </div>
                                    <button
                                        type="button"
                                        @click="resetPreview(); uploadPaymentId = {{ $cardPayment->id }}; uploadRoute = @js($uploadRoute); uploadUnitLabel = @js('Unit '.($cardLease->unit?->unit_number ?? '—')); uploadAmountDue = @js(number_format((float) $cardPayment->getBreakdown()['total_amount_due'], 2)); uploadOpen = true"
                                        class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 active:scale-[0.98] transition-all">
                                        <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                        Resubmit Proof
                                    </button>
                                @endif

                            @else
                                <div class="flex flex-col items-center justify-center py-6 text-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 mb-3">
                                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-emerald-800">All caught up!</p>
                                    <p class="text-xs text-slate-400 mt-1">No pending payments for this unit.</p>
                                </div>
                            @endif

                            <a href="{{ route('tenant.payments.index', ['lease_id' => $cardLease->id]) }}"
                               class="mt-3 flex items-center justify-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition-colors">
                                View all payments for this unit →
                            </a>
                        </div>

                        @if($cardLease->inclusions && count($cardLease->inclusions) > 0)
                            <div class="border-t border-slate-100 px-5 py-3">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Includes</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($cardLease->inclusions as $inclusion)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-2.5 h-2.5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            {{ $inclusion }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($lease && (!empty($contractPath) || $lease->notes || $lease->end_date))
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-4">Primary lease (Unit {{ $lease->unit?->unit_number ?? '—' }})</p>
                @if($lease->notes)
                    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-amber-600 mb-1">Special Agreement</p>
                        <p class="text-xs text-amber-800 leading-relaxed">{{ $lease->notes }}</p>
                    </div>
                @endif
                @if(!empty($contractPath))
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Lease Contract</p>
                    @php
                        $ext = pathinfo($contractPath, PATHINFO_EXTENSION);
                        $isImg = in_array(strtolower((string) $ext), ['jpg', 'jpeg', 'png', 'webp'], true);
                    @endphp
                    @if($isImg)
                        <div class="mb-3 overflow-hidden rounded-xl ring-1 ring-slate-200">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($contractPath) }}" class="w-full object-cover max-h-40 rounded-xl" alt="Lease contract" />
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($contractPath) }}" target="_blank" rel="noopener noreferrer" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-indigo-50 border border-indigo-100 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition-all">View</a>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($contractPath) }}" download class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all">Download</a>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Uploaded {{ $contractUploadedAt?->format('M d, Y') }}</p>
                @endif
                @if($lease->end_date)
                    @php $daysLeft = (int) now()->startOfDay()->diffInDays($lease->end_date->copy()->startOfDay(), false); @endphp
                    @if($daysLeft < 0)
                        <div class="mt-4 flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 p-3">
                            <p class="text-xs font-medium text-rose-700">This lease has ended.</p>
                        </div>
                    @elseif($daysLeft <= 30)
                        <div class="mt-4 flex items-center gap-2 rounded-xl border border-amber-100 bg-amber-50 p-3">
                            <p class="text-xs font-medium text-amber-700">Lease expires in {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }}</p>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- Recent payments --}}
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Recent Payments</h2>
                    <p class="mt-1 text-xs text-slate-400">Your latest rent activity</p>
                </div>
                <a href="{{ route('tenant.payments.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">View all payments</a>
            </div>

            @if($recentPayments->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 py-14 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-800">No payment records yet</p>
                    <p class="mt-1 text-sm text-slate-500">When you have a lease, your payments will appear here.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Proof</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($recentPayments as $row)
                                <tr class="transition-colors duration-150 hover:bg-indigo-50/30">
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-bold text-slate-900">{{ $row->due_date?->format('M d, Y') }}</p>
                                        @if($row->payment_date)
                                            <p class="text-xs text-slate-400">Paid: {{ $row->payment_date->format('M d, Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-indigo-700">₱{{ number_format((float) $row->total_amount_due, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @php $rc = $paymentCfg($row->status); @endphp
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $rc[0] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $rc[1] }}"></span>
                                            {{ $rc[2] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($row->proof_of_payment)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($row->proof_of_payment) }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View Proof</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('tenant.payments.show', $row) }}" class="inline-flex rounded-lg p-2 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600" aria-label="View payment" title="View">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Upload proof modal --}}
        @php
            $showUploadModal = false;
            foreach ($leaseCards as $c) {
                $p = $c['nextPayment'] ?? null;
                if ($p && in_array($p->status, ['pending', 'late', 'verifying', 'verifying_late', 'rejected'], true)) {
                    $showUploadModal = true;
                    break;
                }
            }
        @endphp
        @if($showUploadModal)
            <div
                x-show="uploadOpen"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm"
                x-transition.opacity
                @click.self="uploadOpen = false; resetPreview()"
                role="dialog"
                aria-modal="true"
                aria-labelledby="upload-modal-title"
            >
                <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="relative flex items-start justify-between gap-3 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            </span>
                            <div>
                                <h3 id="upload-modal-title" class="text-lg font-bold text-white">Submit Proof of Payment</h3>
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
                        class="px-6 py-5"
                        x-bind:action="uploadRoute"
                    >
                        @csrf
                        <div class="mb-4 flex items-start gap-3 rounded-xl bg-indigo-50 p-4">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">Payment for <span x-text="uploadUnitLabel || 'your unit'"></span></p>
                                <p class="mt-0.5 text-xs text-indigo-600">Amount due: ₱<span x-text="uploadAmountDue || '0.00'"></span></p>
                            </div>
                        </div>

                        <label
                            class="relative flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30"
                            x-on:dragover.prevent
                            x-on:drop.prevent="onDrop($event)"
                        >
                            <input
                                id="dashboard-proof-input"
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
                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 sm:w-auto">Submit Proof</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
