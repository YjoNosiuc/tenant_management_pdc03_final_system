@extends('layouts.admin', ['title' => $property->name])

@section('title', $property->name)

@section('content')
    @php
        $lightboxUrls = $property->images->map(fn ($i) => \Illuminate\Support\Facades\Storage::url($i->image_path))->values()->all();
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-8"
        x-data="{
            lightboxOpen: false,
            currentIndex: 0,
            images: @js($lightboxUrls),
            editOpen: false,
            createUnitOpen: false,
            newUnit: {
                unit_number: '',
                unit_type: 'Studio',
                rent_price: '',
                status: 'vacant'
            },
            lateFeeOpen: false,
            lateFeeType: @js($property->late_fee_type ?? 'percentage'),
            flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }},
            openLightbox(index) { this.currentIndex = index; this.lightboxOpen = true; },
            prev() { this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.images.length - 1; },
            next() { this.currentIndex = this.currentIndex < this.images.length - 1 ? this.currentIndex + 1 : 0; }
        }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 3000);
            @endif
            @if(old('_form') === 'edit_show' && $errors->any())
                editOpen = true;
            @endif
            @if(old('_form') === 'create_unit_property' && $errors->any())
                createUnitOpen = true;
            @endif
        "
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

        <a href="{{ route('admin.properties.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-2">
            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Back to Properties
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008ZM15.75 21h-7.5a2.25 2.25 0 0 1-2.25-2.25v-8.844a2.25 2.25 0 0 1 1.183-1.98l7.5-4.166a2.25 2.25 0 0 1 2.134 0l7.5 4.166a2.25 2.25 0 0 1 1.183 1.98V18.75A2.25 2.25 0 0 1 15.75 21Z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $property->name }}</h1>
                    <div class="mt-1.5 space-y-0.5 text-sm text-slate-500">
                        <p class="font-medium text-slate-700">{{ $property->address_line1 }}</p>
                        @if($property->address_line2)
                            <p class="text-slate-600">{{ $property->address_line2 }}</p>
                        @endif
                        <p class="flex items-start gap-1">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <span>{{ $property->barangay }}, {{ $property->city }}, {{ $property->province }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50 active:scale-[0.98]"
                    @click="editOpen = true">
                    Edit
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-slate-900">Property Photos</h2>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $property->images->count() }}/5</span>
                    </div>

                    @if($property->images->isEmpty())
                        <form action="{{ route('admin.properties.images.upload', $property) }}" method="POST" enctype="multipart/form-data" class="block">
                            @csrf
                            <div class="relative flex h-48 w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30">
                                <input type="file" name="images[]" multiple accept="image/*"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    onchange="this.form.submit()" />
                                <svg class="mb-2 h-12 w-12 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008H12V8.25Z" /></svg>
                                <p class="text-sm font-semibold text-slate-500">No photos yet</p>
                                <p class="mt-1 text-xs text-slate-400">Upload up to 5 photos of this property</p>
                            </div>
                        </form>
                    @else
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach($property->images as $image)
                                <div class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl bg-slate-100"
                                    @click="openLightbox({{ $loop->index }})">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        alt="Property photo" />
                                    <div class="absolute inset-0 flex items-end justify-end bg-black/40 p-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                        <form action="{{ route('admin.properties.images.delete', [$property, $image]) }}" method="POST">
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

                            @if($property->images->count() < 5)
                                <div class="aspect-square">
                                    <form action="{{ route('admin.properties.images.upload', $property) }}" method="POST" enctype="multipart/form-data" class="relative flex h-full min-h-0 w-full flex-col">
                                        @csrf
                                        <div class="relative flex h-full min-h-[10rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 transition-all duration-150 hover:border-indigo-400 hover:bg-indigo-50/30 md:min-h-0 md:aspect-square">
                                            <input type="file" name="images[]" multiple accept="image/*"
                                                class="absolute inset-0 cursor-pointer opacity-0"
                                                onchange="this.form.submit()" />
                                            <svg class="mb-2 h-8 w-8 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.02-2.192 4.5 4.5 0 0 1 1.5 8.438M12 16.5V9.75m0 0-3 3m3-3 3 3" /></svg>
                                            <p class="text-xs font-medium text-slate-500">Add Photos</p>
                                            <p class="mt-0.5 text-xs text-slate-400">{{ 5 - $property->images->count() }} remaining</p>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-4 rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-semibold text-slate-900">Units</h2>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $property->units->count() }}</span>
                        </div>
                        <button
                            type="button"
                            @click="createUnitOpen = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 active:scale-[0.98] transition-all duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Unit
                        </button>
                    </div>

                    @if($property->units->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/80 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-600">No units yet</p>
                            <p class="mt-1 text-xs text-slate-400">Use <span class="font-semibold text-slate-600">Add Unit</span> above to create one for this property.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-100">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Unit #</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Type</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Rent</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-400">Current Tenant</th>
                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white">
                                    @foreach($property->units as $unit)
                                        @php
                                            $activeLease = $unit->leases->firstWhere('status', 'active');
                                            $tenantUser = $activeLease?->tenant?->user;
                                        @endphp
                                        <tr class="transition-colors hover:bg-indigo-50/30">
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <a href="{{ route('admin.units.show', $unit) }}" class="font-semibold text-slate-800 hover:text-indigo-600 hover:underline underline-offset-2">{{ $unit->unit_number }}</a>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">{{ $unit->unit_type }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-indigo-700">₱{{ number_format((float) $unit->rent_price, 2) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                @if($unit->status === 'occupied')
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                        Occupied
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                        Vacant
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($tenantUser)
                                                    <span class="font-medium text-slate-800">{{ $tenantUser->name }}</span>
                                                @else
                                                    <span class="text-slate-400">Vacant</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('admin.units.show', $unit) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-indigo-600" title="View unit" aria-label="View unit">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                    <a href="{{ route('admin.units.index') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-amber-50 hover:text-amber-600" title="Edit on Units page" aria-label="Edit on Units page">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                                    </a>
                                                </div>
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
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Details</h2>
                    <ul class="space-y-4 text-sm">
                        <li class="flex gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <span class="text-slate-600">{{ $property->full_address }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                            <span class="text-slate-600"><span class="font-medium text-slate-800">Total Units:</span> {{ $totalUnitsCount }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-600"><span class="font-medium text-slate-800">Occupied:</span> {{ $occupiedUnitsCount }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                            <span class="text-slate-600"><span class="font-medium text-slate-800">Vacant:</span> {{ $vacantUnitsCount }}</span>
                        </li>
                        <li>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Description</p>
                            @if($property->description)
                                <p class="text-sm text-slate-600">{{ $property->description }}</p>
                            @else
                                <p class="text-sm italic text-slate-400">No description</p>
                            @endif
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                            Created {{ $property->created_at->format('M d, Y') }}
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Quick Stats</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between rounded-xl bg-emerald-50/80 px-4 py-3 ring-1 ring-emerald-100">
                            <span class="text-sm font-medium text-emerald-800">Active Leases</span>
                            <span class="text-lg font-bold text-emerald-700">{{ $activeLeasesCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-indigo-50/80 px-4 py-3 ring-1 ring-indigo-100">
                            <span class="text-sm font-medium text-indigo-800">Revenue (this month)</span>
                            <span class="text-lg font-bold text-indigo-700">₱{{ number_format($revenueThisMonth, 2) }}</span>
                        </div>
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs font-medium text-slate-500">
                                <span>Occupancy</span>
                                <span>{{ $occupancyPercent }}%</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all" style="width: {{ $occupancyPercent }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            Late Fee Settings
                        </p>
                        <button type="button" @click="lateFeeOpen = !lateFeeOpen"
                            class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition-colors">
                            <span x-show="!lateFeeOpen">Edit</span>
                            <span x-show="lateFeeOpen" x-cloak>Cancel</span>
                        </button>
                    </div>

                    <div x-show="!lateFeeOpen" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">Fee Type</span>
                            <span class="text-sm font-semibold text-slate-800 capitalize">
                                {{ $property->late_fee_type }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">Fee Value</span>
                            <span class="text-sm font-semibold text-slate-800">
                                @if($property->late_fee_type === 'percentage')
                                    {{ $property->late_fee_value }}%
                                @else
                                    ₱{{ number_format((float) $property->late_fee_value, 2) }}
                                @endif
                            </span>
                        </div>
                        @if((float) $property->late_fee_value > 0)
                        <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                            <p class="text-xs text-amber-700 font-medium">
                                Example: For ₱6,500 rent →
                                Late fee = ₱{{ number_format($property->calculateLateFee(6500), 2) }}
                            </p>
                        </div>
                        @else
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <p class="text-xs text-slate-500">No late fee configured.</p>
                        </div>
                        @endif
                    </div>

                    <form x-show="lateFeeOpen" x-cloak action="{{ route('admin.properties.late-fee.update', $property) }}"
                          method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Fee Type
                            </label>
                            <select name="late_fee_type"
                                x-model="lateFeeType"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 transition-all">
                                <option value="percentage" {{ $property->late_fee_type === 'percentage' ? 'selected' : '' }}>
                                    Percentage of Monthly Rent
                                </option>
                                <option value="fixed" {{ $property->late_fee_type === 'fixed' ? 'selected' : '' }}>
                                    Fixed Amount
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                <span x-show="lateFeeType === 'percentage'">Percentage (%)</span>
                                <span x-show="lateFeeType === 'fixed'" x-cloak>Fixed Amount (₱)</span>
                            </label>
                            <div class="relative">
                                <span x-show="lateFeeType === 'fixed'" x-cloak
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                <input type="number"
                                    name="late_fee_value"
                                    step="0.01"
                                    min="0"
                                    value="{{ old('late_fee_value', $property->late_fee_value) }}"
                                    :class="lateFeeType === 'fixed' ? 'pl-8' : 'pl-4'"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pr-4 text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 transition-all" />
                                <span x-show="lateFeeType === 'percentage'"
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-sm font-semibold text-slate-400">%</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                Set to 0 to disable late fees for this property.
                            </p>
                        </div>

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 active:scale-[0.98] transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lightbox --}}
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
                <span x-text="currentIndex + 1"></span> / {{ $property->images->count() }}
            </div>
        </div>

        {{-- Create Unit Modal --}}
        <div
            x-show="createUnitOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden"
            role="dialog"
            aria-modal="true">

            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="createUnitOpen = false">
            </div>

            {{-- Modal --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100 overflow-hidden"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-white">Add Unit</h3>
                                    <p class="text-xs text-indigo-200">
                                        Add a new unit to {{ $property->name }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="createUnitOpen = false"
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <form action="{{ route('admin.units.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form" value="create_unit_property" />

                        {{-- Hidden: pre-fill property_id with current property --}}
                        <input type="hidden" name="property_id" value="{{ $property->id }}" />

                        <div class="px-6 py-5 space-y-4">

                            {{-- Unit Number --}}
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
                                    </svg>
                                    Unit Number <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="unit_number"
                                    x-model="newUnit.unit_number"
                                    placeholder="e.g. 101, A1, Ground Floor"
                                    required
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
                                />
                                @error('unit_number')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Unit Type --}}
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                    Unit Type <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="unit_type"
                                    x-model="newUnit.unit_type"
                                    required
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20">
                                    <option value="Studio">Studio</option>
                                    <option value="1BR">1BR</option>
                                    <option value="2BR">2BR</option>
                                    <option value="3BR">3BR</option>
                                </select>
                                @error('unit_type')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Rent Price --}}
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                    Rent Price <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">₱</span>
                                    <input
                                        type="number"
                                        name="rent_price"
                                        x-model="newUnit.rent_price"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-8 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
                                    />
                                </div>
                                @error('rent_price')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    name="status"
                                    x-model="newUnit.status"
                                    required
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20">
                                    <option value="vacant">Vacant</option>
                                    <option value="occupied">Occupied</option>
                                </select>
                                @error('status')
                                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- Modal Footer --}}
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button
                                type="button"
                                @click="createUnitOpen = false"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all duration-150 active:scale-[0.98]">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition-all duration-150 active:scale-[0.98]">
                                Add Unit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit property modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto overflow-x-hidden" aria-labelledby="property-edit-show-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-100" @click.stop>
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 id="property-edit-show-title" class="text-base font-bold text-white">Edit property</h3>
                                <p class="text-xs text-indigo-200">Update details for this building.</p>
                            </div>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/10 text-white transition-all hover:bg-white/20" @click="editOpen = false" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.properties.update', $property) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit_show">
                        <div class="space-y-4 px-6 py-5">
                            <div>
                                <label for="show-edit-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Name <span class="text-rose-500">*</span></label>
                                <input id="show-edit-name" name="name" type="text" value="{{ old('_form') === 'edit_show' ? old('name', $property->name) : $property->name }}" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm @error('name') border-rose-300 @enderror" />
                                @error('name')
                                    <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-psgc-address
                                    :province="old('_form') === 'edit_show' ? old('province', $property->province) : $property->province"
                                    :city="old('_form') === 'edit_show' ? old('city', $property->city) : $property->city"
                                    :barangay="old('_form') === 'edit_show' ? old('barangay', $property->barangay) : $property->barangay"
                                    :addressLine1="old('_form') === 'edit_show' ? old('address_line1', $property->address_line1) : $property->address_line1"
                                    :addressLine2="old('_form') === 'edit_show' ? old('address_line2', $property->address_line2 ?? '') : ($property->address_line2 ?? '')"
                                    :required="true"
                                />
                                @foreach (['province', 'city', 'barangay', 'address_line1', 'address_line2'] as $_addrField)
                                    @error($_addrField)
                                        <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </div>
                            <div>
                                <label for="show-edit-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description</label>
                                <textarea id="show-edit-description" name="description" rows="3" class="block w-full resize-none rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm @error('description') border-rose-300 @enderror">{{ old('_form') === 'edit_show' ? old('description', $property->description) : $property->description }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" @click="editOpen = false">Cancel</button>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90">Update Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
