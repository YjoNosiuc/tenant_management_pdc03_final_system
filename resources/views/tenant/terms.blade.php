<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Terms & Conditions — RentTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Inter', sans-serif; background: #F8FAFF; }
        .terms-content h2 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 16px 0 8px; }
        .terms-content p { font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 8px; }
        .terms-content ul { font-size: 13px; color: #475569; line-height: 1.6; padding-left: 16px; margin-bottom: 8px; list-style: disc; }
    </style>
</head>
<body>
<div class="min-h-screen flex items-center justify-center p-4"
     x-data="{ agreed: false }">
    <div class="w-full max-w-2xl">

        <div class="flex items-center justify-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <span class="text-xl font-bold text-slate-900">RentTrack</span>
        </div>

        <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">

            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-white" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Terms & Conditions</h1>
                        <p class="text-sm text-indigo-200">
                            Please read and agree before continuing
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 sm:gap-4">
                    <div class="rounded-xl bg-white/10 px-3 py-1.5 text-xs font-medium text-white">
                        Version {{ $terms->version }}
                    </div>
                    <div class="rounded-xl bg-white/10 px-3 py-1.5 text-xs font-medium text-white">
                        {{ $activeLease->unit->property->name }}
                    </div>
                    <div class="rounded-xl bg-white/10 px-3 py-1.5 text-xs font-medium text-white">
                        Unit {{ $activeLease->unit->unit_number }}
                    </div>
                </div>
            </div>

            <div class="px-8 py-6">
                <div class="h-80 overflow-y-auto rounded-2xl bg-slate-50 border border-slate-200 p-6 terms-content">
                    {!! nl2br(e($terms->content)) !!}
                </div>

                <form action="{{ route('terms.agree') }}" method="POST" class="mt-6">
                    @csrf

                    @if($errors->any())
                        <div class="mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3">
                            @foreach($errors->all() as $error)
                                <p class="text-xs font-medium text-red-600">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative mt-0.5">
                            <input type="checkbox"
                                   name="agreed"
                                   value="1"
                                   x-model="agreed"
                                   class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer"
                                   aria-required="true" />
                        </div>
                        <span class="text-sm text-slate-700 leading-relaxed">
                            I, <span class="font-semibold">{{ auth()->user()->name }}</span>,
                            have read and fully understood the Terms and Conditions above.
                            I agree to comply with all rules, including the
                            <span class="font-semibold text-indigo-600">late payment policy</span>
                            and <span class="font-semibold text-indigo-600">property damage policy</span>.
                        </span>
                    </label>

                    <button type="submit"
                        :disabled="!agreed"
                        :class="agreed
                            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:opacity-90 cursor-pointer'
                            : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                        class="mt-6 w-full flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm active:scale-[0.98] transition-all duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        I Agree & Continue
                    </button>
                </form>

                <div class="text-center mt-4">
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                            Not {{ auth()->user()->name }}? Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-4">© 2026 RentTrack. All rights reserved.</p>
    </div>
</div>
</body>
</html>
