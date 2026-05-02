<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim($__env->yieldContent('title')) ?: ($title ?? 'Dashboard') }} — RentTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    @php
        $unread = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $notificationColumns = \Illuminate\Support\Facades\Schema::getColumnListing('notifications');
            if (in_array('user_id', $notificationColumns, true) && in_array('read_at', $notificationColumns, true)) {
                $unread = (int) \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', auth()->id())->whereNull('read_at')->count();
            }
        }
        if ($unread === 0) {
            $unread = (int) ($unreadNotificationCount ?? 0);
        }
        $pageHeading = trim($__env->yieldContent('title')) ?: ($title ?? 'Dashboard');
    @endphp

    <div
        x-show="sidebarOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
        x-cloak
        @click="sidebarOpen = false"
        aria-hidden="true"
    ></div>

    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-white/5 bg-gradient-to-b from-[#1E2A3A] via-[#1a2433] to-[#151d2a] text-white shadow-xl transition-transform duration-200 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        aria-label="Tenant navigation"
    >
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">RentTrack</p>
                <p class="text-xs text-slate-400">Tenant Portal</p>
            </div>
        </div>

        <nav class="flex-1 space-y-8 overflow-y-auto px-3 py-6">
            <div>
                <p class="mb-3 px-2 text-xs font-semibold uppercase tracking-widest text-slate-500">Main</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('tenant.dashboard') }}" class="group flex items-center gap-3 rounded-xl border-l-4 px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.dashboard') ? 'border-[#6366F1] bg-indigo-500 text-white shadow-sm shadow-indigo-900/30' : 'border-transparent text-slate-400 hover:bg-white/10 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tenant.payments.index') }}" class="group flex items-center gap-3 rounded-xl border-l-4 px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.payments.*') ? 'border-[#6366F1] bg-indigo-500 text-white shadow-sm shadow-indigo-900/30' : 'border-transparent text-slate-400 hover:bg-white/10 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                            <span>My Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tenant.notifications.index') }}" class="group flex items-center gap-3 rounded-xl border-l-4 px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.notifications.*') ? 'border-[#6366F1] bg-indigo-500 text-white shadow-sm shadow-indigo-900/30' : 'border-transparent text-slate-400 hover:bg-white/10 hover:text-white' }}">
                            <span class="relative inline-flex">
                                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.8569 17.0817C16.7514 16.857 18.5783 16.4116 20.3111 15.7719C18.8743 14.177 17.9998 12.0656 17.9998 9.75V9.04919C17.9999 9.03281 18 9.01641 18 9C18 5.68629 15.3137 3 12 3C8.68629 3 6 5.68629 6 9L5.9998 9.75C5.9998 12.0656 5.12527 14.177 3.68848 15.7719C5.4214 16.4116 7.24843 16.857 9.14314 17.0818M14.8569 17.0817C13.92 17.1928 12.9666 17.25 11.9998 17.25C11.0332 17.25 10.0799 17.1929 9.14314 17.0818M14.8569 17.0817C14.9498 17.3711 15 17.6797 15 18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18C9 17.6797 9.05019 17.3712 9.14314 17.0818" /></svg>
                                @if($unread > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-[#1E2A3A]">{{ $unread > 9 ? '9+' : $unread }}</span>
                                @endif
                            </span>
                            <span>Notifications</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <p class="mb-3 px-2 text-xs font-semibold uppercase tracking-widest text-slate-500">Account</p>
                <p class="px-3 text-xs font-medium capitalize text-slate-400">Role: {{ auth()->user()->role }}</p>
            </div>
        </nav>

        <div class="border-t border-white/10 pt-4 mt-4 px-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white transition-all duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="min-h-full lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 shadow-sm backdrop-blur md:px-8">
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 transition-all duration-200 hover:bg-slate-50 hover:text-slate-900 active:scale-95 lg:hidden" @click="sidebarOpen = true" aria-label="Open navigation menu">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">{{ $pageHeading }}</h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('tenant.notifications.index') }}" class="relative inline-flex rounded-xl p-2 text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-800 active:scale-95" aria-label="Notifications">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.8569 17.0817C16.7514 16.857 18.5783 16.4116 20.3111 15.7719C18.8743 14.177 17.9998 12.0656 17.9998 9.75V9.04919C17.9999 9.03281 18 9.01641 18 9C18 5.68629 15.3137 3 12 3C8.68629 3 6 5.68629 6 9L5.9998 9.75C5.9998 12.0656 5.12527 14.177 3.68848 15.7719C5.4214 16.4116 7.24843 16.857 9.14314 17.0818M14.8569 17.0817C13.92 17.1928 12.9666 17.25 11.9998 17.25C11.0332 17.25 10.0799 17.1929 9.14314 17.0818M14.8569 17.0817C14.9498 17.3711 15 17.6797 15 18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18C9 17.6797 9.05019 17.3712 9.14314 17.0818" /></svg>
                    @if($unread > 0)
                        <span class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                    @endif
                </a>
                @php
                    $initials = collect(preg_split('/\s+/', trim(auth()->user()->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                    $initials = $initials !== '' ? $initials : mb_strtoupper(mb_substr(auth()->user()->email, 0, 2));
                @endphp
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md shadow-indigo-500/30 ring-2 ring-white" title="{{ auth()->user()->name }}">
                    {{ $initials }}
                </div>
            </div>
        </header>

        <main class="p-6 md:p-8">
            @yield('content')
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @stack('scripts')
</body>
</html>
