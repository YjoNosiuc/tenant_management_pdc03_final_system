@props([
    'province' => '',
    'city' => '',
    'barangay' => '',
    'addressLine1' => '',
    'addressLine2' => '',
    'required' => true,
    'bindParent' => false,
    'parentKey' => null,
])

<div
    x-data="psgcAddressForm({
        initial: {
            province: @js($province),
            city: @js($city),
            barangay: @js($barangay),
        },
        bindParent: @js($bindParent),
        parentKey: @js($parentKey),
    })"
    class="space-y-4"
>
    {{-- Province --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            Province
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="province"
                x-model="selectedProvince"
                @change="onProvinceChange($event)"
                :disabled="loading"
                @if($required) required @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:cursor-wait disabled:opacity-50"
            >
                <option value="" disabled x-text="loading ? 'Loading provinces…' : 'Select Province'"></option>
                <template x-for="p in provinces" :key="p.code">
                    <option :value="p.name" x-text="p.name"></option>
                </template>
            </select>
            <div x-show="loading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="h-4 w-4 animate-spin text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- City/Municipality --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
            City / Municipality
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="city"
                x-model="selectedCity"
                @change="onCityChange($event)"
                :disabled="!selectedProvince || citiesLoading"
                @if($required) required @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:cursor-wait disabled:opacity-50"
            >
                <option value="" x-text="!selectedProvince ? 'Select province first' : (citiesLoading ? 'Loading…' : 'Select City/Municipality')"></option>
                <template x-for="c in cities" :key="c.code">
                    <option :value="c.name" x-text="c.name"></option>
                </template>
            </select>
            <div x-show="citiesLoading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="h-4 w-4 animate-spin text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Barangay
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <div class="relative">
            <select
                name="barangay"
                x-model="selectedBarangay"
                @change="onBarangayChange($event)"
                :disabled="!selectedCity || barangaysLoading"
                @if($required) required @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 disabled:cursor-wait disabled:opacity-50"
            >
                <option value="" x-text="!selectedCity ? 'Select city first' : (barangaysLoading ? 'Loading…' : 'Select Barangay')"></option>
                <template x-for="b in barangays" :key="b.code">
                    <option :value="b.name" x-text="b.name"></option>
                </template>
            </select>
            <div x-show="barangaysLoading" x-cloak class="pointer-events-none absolute inset-y-0 right-8 flex items-center">
                <svg class="h-4 w-4 animate-spin text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
            </svg>
            Address Line 1
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
        <template x-if="!bindParent">
            <input
                type="text"
                name="address_line1"
                value="{{ $addressLine1 }}"
                placeholder="House/Unit No., Street Name"
                @if($required) required @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
            />
        </template>
        <template x-if="bindParent">
            <input
                type="text"
                name="address_line1"
                x-bind:value="parentModel()?.address_line1"
                @input="parentModel() && (parentModel().address_line1 = $event.target.value)"
                placeholder="House/Unit No., Street Name"
                @if($required) required @endif
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
            />
        </template>
    </div>

    {{-- Address Line 2 --}}
    <div>
        <label class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
            </svg>
            Address Line 2
            <span class="text-xs font-normal text-slate-400">(Optional)</span>
        </label>
        <template x-if="!bindParent">
            <input
                type="text"
                name="address_line2"
                value="{{ $addressLine2 }}"
                placeholder="Landmark, Building name, etc."
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
            />
        </template>
        <template x-if="bindParent">
            <input
                type="text"
                name="address_line2"
                x-bind:value="parentModel()?.address_line2"
                @input="parentModel() && (parentModel().address_line2 = $event.target.value)"
                placeholder="Landmark, Building name, etc."
                class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition-all duration-150 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20"
            />
        </template>
    </div>
</div>
