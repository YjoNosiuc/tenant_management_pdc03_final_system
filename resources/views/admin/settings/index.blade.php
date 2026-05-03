@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center gap-2 mb-8">
        <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Settings</h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-indigo-600" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Terms & Conditions</h2>
                            <p class="text-sm text-slate-400">Applies to all your properties and tenants</p>
                        </div>
                    </div>
                    @if($terms)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">
                            Version {{ $terms->version }}
                        </span>
                    @endif
                </div>

                @if($terms)
                    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-100 p-4 flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Important</p>
                            <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                Updating the Terms & Conditions will increment the version number.
                                All tenants will be required to re-read and re-agree before
                                accessing their dashboard.
                            </p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.settings.terms.update') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <textarea
                        name="content"
                        rows="20"
                        placeholder="Write your Terms & Conditions here..."
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 font-mono leading-relaxed focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/20 transition-all duration-150 resize-none"
                    >{{ old('content', $terms?->content ?? $defaultContent) }}</textarea>

                    @error('content')
                        <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-400">Minimum 100 characters required.</p>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 active:scale-[0.98] transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Save Terms & Conditions
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Agreement Status</h3>

                @if($terms)
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-slate-500">Current Version</dt>
                            <dd>
                                <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">v{{ $terms->version }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-slate-500">Last Updated</dt>
                            <dd class="font-medium text-slate-800">{{ $terms->updated_at->format('M d, Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-slate-500">Tenants Agreed</dt>
                            <dd>
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">{{ $agreedCount }}</span>
                            </dd>
                        </div>
                    </dl>
                    <div class="my-4 border-t border-slate-100"></div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Tenants who have not agreed will be prompted on next login.
                    </p>
                @else
                    <div class="flex flex-col items-center text-center py-4">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">No T&amp;C set yet</p>
                        <p class="mt-1 text-xs text-slate-500">Create your terms above.</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 ring-1 ring-slate-100">
                <h3 class="text-sm font-bold text-slate-900 mb-4">What to Include</h3>
                <ul class="space-y-2.5 text-sm text-slate-600">
                    @foreach([
                        'Late payment fee policy',
                        'Security deposit terms',
                        'Property damage policy',
                        'Pet policy',
                        'Move-out requirements',
                        'House rules',
                    ] as $tip)
                        <li class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0 text-emerald-500 mt-0.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
