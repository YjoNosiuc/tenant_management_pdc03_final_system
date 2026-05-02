@extends('layouts.admin', ['title' => 'Unit '.$unit->unit_number])

@section('title', 'Unit '.$unit->unit_number)

@section('content')
    @php
        $lightboxUrls = $unit->images->map(fn ($i) => \Illuminate\Support\Facades\Storage::url($i->image_path))->values()->all();
        $paymentShell = fn (string $status) => match ($status) {
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'verifying' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
            'late' => 'bg-rose-50 text-rose-700 ring-rose-100',
            'rejected' => 'bg-red-50 text-red-700 ring-red-100',
            default => 'bg-slate-50 text-slate-700 ring-slate-100',
        };
        $paymentDot = fn (string $status) => match ($status) {
            'paid' => 'bg-emerald-500',
            'pending' => 'bg-amber-500',
            'verifying' => 'bg-indigo-500',
            'late' => 'bg-rose-500',
            'rejected' => 'bg-red-500',
            default => 'bg-slate-400',
        };
        $paymentLabel = fn (string $status) => match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'verifying' => 'Verifying',
            'late' => 'Late',
            'rejected' => 'Rejected',
            default => ucfirst($status),
        };
        $leaseShell = fn (?string $status) => match ($status) {
            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'completed' => 'bg-blue-50 text-blue-800 ring-blue-100',
            'terminated' => 'bg-rose-50 text-rose-800 ring-rose-100',
            default => 'bg-slate-50 text-slate-700 ring-slate-100',
        };
        $leaseDot = fn (?string $status) => match ($status) {
            'active' => 'bg-emerald-500',
            'completed' => 'bg-blue-500',
            'terminated' => 'bg-rose-500',
            default => 'bg-slate-400',
        };
        $tenantUser = $activeLease?->tenant?->user;
        $initials = $tenantUser
            ? collect(preg_split('/\s+/', trim((string) $tenantUser->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('')
            : '';
        if ($initials === '' && $tenantUser) {
            $initials = mb_strtoupper(mb_substr((string) $tenantUser->email, 0, 2));
        }
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            lightboxOpen: false,
            currentIndex: 0,
            images: @js($lightboxUrls),
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            openLightbox(index) { this.currentIndex = index; this.lightboxOpen = true; },
            prev() { this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.images.length - 1; },
            next() { this.currentIndex = this.currentIndex < this.images.length - 1 ? this.currentIndex + 1 : 0; }
        }"
        x-init="@if(session()->has('success') || session()->has('error')) setTimeout(() => { flashVisible = false }, 3000); @endif"
        @keydown.escape.window="lightboxOpen = false"
        @keydown.arrow-left.window="if (lightboxOpen && images.length) { $event.preventDefault(); prev(); }"
        @keydown.arrow-right.window="if (lightboxOpen && images.length) { $event.preventDefault(); next(); }"
    >
        <div x-show="flashVisible" x-cloak class="space-y-3">
            @if(session('success'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm ring-1 ring-emerald-100" role="alert">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="rounded-lg p-1 text-emerald-600 transition hover:bg-emerald-100" @click="flashVisible = false" aria-label="Dismiss">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm ring-1 ring-rose-100" role="alert">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="rounded-lg p-1 text-rose-600 transition hover:bg-rose-100" @click="flashVisible = false" aria-label="Dismiss">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        </div>

        <a href="{{ route('admin.properties.show', $unit->property) }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-2">
            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to {{ $unit->property->name }}
        </a>

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Unit {{ $unit->unit_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $unit->property->name }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if($unit->status === 'occupied')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Occupied
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Vacant
                        </span>
                    @endif
                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $unit->unit_type }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-slate-900">Unit Photos</h2>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $unit->images->count() }}/5</span>
                    </div>

                    @if($unit->images->isEmpty())
                        <form action="{{ route('admin.units.images.upload', $unit) }}" method="POST" enctype="multipart/form-data" class="block">
                            @csrf
                            <div class="relative flex h-48 w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30">
                                <input type="file" name="images[]" multiple accept="image/*"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    onchange="this.form.submit()" />
                                <svg class="mb-2 h-12 w-12 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008H12V8.25Z" /></svg>
                                <p class="text-sm font-semibold text-slate-500">No photos yet</p>
                                <p class="mt-1 text-xs text-slate-400">Upload up to 5 photos of this unit</p>
                            </div>
                        </form>
                    @else
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach($unit->images as $image)
                                <div class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl bg-slate-100"
                                    @click="openLightbox({{ $loop->index }})">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        alt="Unit photo" />
                                    <div class="absolute inset-0 flex items-end justify-end bg-black/40 p-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                        <form action="{{ route('admin.units.images.delete', [$unit, $image]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Delete this image?')"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500 text-white transition-all hover:bg-red-600"
                                                aria-label="Delete image">
                                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach

                            @if($unit->images->count() < 5)
                                <div class="aspect-square">
                                    <form action="{{ route('admin.units.images.upload', $unit) }}" method="POST" enctype="multipart/form-data" class="relative flex h-full min-h-0 w-full flex-col">
                                        @csrf
                                        <div class="relative flex h-full min-h-[10rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30 md:min-h-0 md:aspect-square">
                                            <input type="file" name="images[]" multiple accept="image/*"
                                                class="absolute inset-0 cursor-pointer opacity-0"
                                                onchange="this.form.submit()" />
                                            <svg class="mb-2 h-8 w-8 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.192 4.5 4.5 0 0 1 1.5 8.438M12 16.5V9.75m0 0-3 3m3-3 3 3" /></svg>
                                            <p class="text-xs font-medium text-slate-500">Add Photos</p>
                                            <p class="mt-0.5 text-xs text-slate-400">{{ 5 - $unit->images->count() }} remaining</p>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Lease History</h2>
                    @if($unit->leases->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/80 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-600">No leases yet</p>
                            <p class="mt-1 text-xs text-slate-400">Leases for this unit will appear here.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tenant</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Start Date</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">End Date</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Monthly Rent</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white">
                                    @foreach($unit->leases as $lease)
                                        <tr class="transition-colors hover:bg-indigo-50/30">
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $lease->tenant?->user?->name ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $lease->start_date?->format('M d, Y') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $lease->end_date?->format('M d, Y') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-indigo-700">₱{{ number_format((float) $lease->monthly_rent, 2) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $leaseShell($lease->status) }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $leaseDot($lease->status) }}"></span>
                                                    {{ ucfirst($lease->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Recent Payments</h2>
                    @if($recentPayments->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/80 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-600">No payments yet</p>
                            <p class="mt-1 text-xs text-slate-400">Payments linked to this unit will show here.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Tenant</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Due Date</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Amount</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white">
                                    @foreach($recentPayments as $payment)
                                        <tr class="transition-colors hover:bg-indigo-50/30">
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $payment->lease?->tenant?->user?->name ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $payment->due_date?->format('M d, Y') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-indigo-700">₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $paymentShell($payment->status) }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $paymentDot($payment->status) }}"></span>
                                                    {{ $paymentLabel($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4 lg:col-span-1">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Unit Number</p>
                    <p class="mt-1 text-3xl font-extrabold text-indigo-600">{{ $unit->unit_number }}</p>
                    <div class="mt-4 flex items-center gap-2 text-sm">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                        <a href="{{ route('admin.properties.show', $unit->property) }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline underline-offset-2">{{ $unit->property->name }}</a>
                    </div>
                    <p class="mt-3">
                        <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $unit->unit_type }}</span>
                    </p>
                    <p class="mt-4 text-2xl font-bold text-slate-900">₱{{ number_format((float) $unit->rent_price, 2) }} <span class="text-sm font-medium text-slate-400">/ month</span></p>
                    <div class="mt-4">
                        @if($unit->status === 'occupied')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Occupied
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                Vacant
                            </span>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Current Tenant</h2>
                    @if($activeLease && $tenantUser)
                        <div class="flex items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 ring-2 ring-indigo-50">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900">{{ $tenantUser->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $tenantUser->email }}</p>
                                @if($activeLease->tenant?->phone_number)
                                    <p class="text-sm text-slate-500">{{ $activeLease->tenant->phone_number }}</p>
                                @endif
                                <p class="mt-2 text-xs text-slate-400">
                                    {{ $activeLease->start_date?->format('M d, Y') }} — {{ $activeLease->end_date?->format('M d, Y') }}
                                </p>
                                <a href="{{ route('admin.tenants.show', $activeLease->tenant) }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">View Tenant</a>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <svg class="h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            <p class="mt-3 text-sm font-semibold text-slate-600">No current tenant</p>
                            <p class="mt-1 text-xs text-slate-400">This unit is available for lease.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="lightboxOpen && images.length" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm"
            @click.self="lightboxOpen = false">
            <button type="button" @click="lightboxOpen = false" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Close">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
            <button type="button" @click="prev()" class="absolute left-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Previous">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            </button>
            <button type="button" @click="next()" class="absolute right-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Next">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </button>
            <img :src="images[currentIndex]" class="max-h-[80vh] max-w-4xl rounded-xl object-contain px-16" alt="" />
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-sm text-white">
                <span x-text="currentIndex + 1"></span> / {{ $unit->images->count() }}
            </div>
        </div>
    </div>
@endsection
