@props([
    'province' => '',
    'city' => '',
    'barangay' => '',
    'addressLine1' => '',
    'addressLine2' => '',
    'required' => true,
    'prefix' => '',
    'editHydrate' => null,
])

@php
    $nameProvince = $prefix ? $prefix . '[province]' : 'province';
    $nameCity = $prefix ? $prefix . '[city]' : 'city';
    $nameBarangay = $prefix ? $prefix . '[barangay]' : 'barangay';
    $nameLine1 = $prefix ? $prefix . '[address_line1]' : 'address_line1';
    $nameLine2 = $prefix ? $prefix . '[address_line2]' : 'address_line2';

    $config = [
        'province' => $province,
        'city' => $city,
        'barangay' => $barangay,
        'address_line1' => $addressLine1,
        'address_line2' => $addressLine2,
    ];
@endphp

<div
    @if ($editHydrate === 'property')
        x-data="psgcAddress({})"
        @@psgc-hydrate-admin-property.window="hydrateFromRow($event.detail)"
    @elseif ($editHydrate === 'tenant')
        x-data="psgcAddress({})"
        @@psgc-hydrate-admin-tenant.window="hydrateFromRow($event.detail)"
    @else
        x-data="psgcAddress({{ \Illuminate\Support\Js::from($config) }})"
    @endif
    class="space-y-4"
>
    {{-- Province --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            Province
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="{{ $nameProvince }}"
                x-model="selectedProvince"
                x-on:change="onProvinceChange($event)"
                :disabled="loading"
                @if ($required)
                    required
                @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:opacity-50 disabled:cursor-wait"
            >
                <option value="" @if ($required) disabled @endif x-text="loading ? 'Loading provinces...' : 'Select Province'"></option>
                <template x-for="province in provinces" :key="province.code">
                    <option :value="province.name" x-text="province.name"></option>
                </template>
            </select>
            <div x-show="loading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- City/Municipality --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
            City / Municipality
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="{{ $nameCity }}"
                x-model="selectedCity"
                x-on:change="onCityChange($event)"
                :disabled="!selectedProvince || citiesLoading"
                @if ($required)
                    required
                @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:opacity-50 disabled:cursor-wait"
            >
                <option value="" x-text="!selectedProvince ? 'Select province first' : (citiesLoading ? 'Loading...' : 'Select City/Municipality')"></option>
                <template x-for="city in cities" :key="city.code">
                    <option :value="city.name" x-text="city.name"></option>
                </template>
            </select>
            <div x-show="citiesLoading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <p x-show="!selectedProvince" class="mt-1 text-xs text-slate-400">Please select a province first</p>
    </div>

    {{-- Barangay --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Barangay
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="{{ $nameBarangay }}"
                x-model="selectedBarangay"
                x-on:change="onBarangayChange($event)"
                :disabled="!selectedCity || barangaysLoading"
                @if ($required)
                    required
                @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:opacity-50 disabled:cursor-wait"
            >
                <option value="" x-text="!selectedCity ? 'Select city first' : (barangaysLoading ? 'Loading...' : 'Select Barangay')"></option>
                <template x-for="barangay in barangays" :key="barangay.code">
                    <option :value="barangay.name" x-text="barangay.name"></option>
                </template>
            </select>
            <div x-show="barangaysLoading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <p x-show="!selectedCity" class="mt-1 text-xs text-slate-400">Please select a city first</p>
    </div>

    {{-- Address Line 1 --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
            </svg>
            Address Line 1
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <input
            type="text"
            name="{{ $nameLine1 }}"
            x-model="addressLine1"
            placeholder="House/Unit No., Street Name"
            @if ($required)
                required
            @endif
            class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
        />
    </div>

    {{-- Address Line 2 --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
            </svg>
            Address Line 2
            <span class="text-xs font-normal text-slate-400">(Optional)</span>
        </label>
        <input
            type="text"
            name="{{ $nameLine2 }}"
            x-model="addressLine2"
            placeholder="Landmark, Building name, etc."
            class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
        />
    </div>
</div>
