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
        $navInitials = collect(preg_split('/\s+/', trim(auth()->user()->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        $navInitials = $navInitials !== '' ? $navInitials : mb_strtoupper(mb_substr(auth()->user()->email, 0, 2));
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
        aria-label="Admin navigation"
    >
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">RentTrack</p>
                <p class="text-xs text-slate-400">Admin Panel</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-4">
            <p class="px-5 text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-500 mb-2 mt-2">Main</p>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <p class="px-5 text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-500 mb-2 mt-6">Management</p>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('admin.properties.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.properties.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-9H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        <span>Properties</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.units.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.units.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                        <span>Units</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tenants.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.tenants.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        <span>Tenants</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.leases.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.leases.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <span>Leases</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.payments.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.payments.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl mx-2 transition-all duration-150
                              {{ request()->routeIs('admin.reports.*')
                                 ? 'bg-indigo-500 text-white border-l-2 border-white'
                                 : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span class="text-sm font-medium">Reports</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.notifications.index') }}" class="mx-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.notifications.*') ? 'border-l-2 border-white bg-indigo-600 text-white shadow-sm shadow-indigo-900/30' : 'border-l-2 border-transparent text-slate-400 hover:bg-white/5 hover:text-white' }}">
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

            <p class="px-5 text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-500 mb-2 mt-6">Account</p>
            <ul class="space-y-0.5 mb-2">
                <li>
                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl mx-2 transition-all duration-150
                              {{ request()->routeIs('admin.settings.*')
                                 ? 'bg-indigo-500 text-white border-l-2 border-white'
                                 : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span class="text-sm font-medium">Settings</span>
                    </a>
                </li>
            </ul>
            <p class="mx-2 rounded-xl px-3 py-1.5 text-xs font-medium capitalize text-slate-400">Role: {{ auth()->user()->role }}</p>
        </nav>

        <div class="border-t border-white/10 px-4 pb-5 pt-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white ring-2 ring-white/10">
                    {{ $navInitials }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition-all duration-150 hover:bg-white/10 hover:text-white" title="Sign out" aria-label="Sign out">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="min-h-full lg:pl-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-100 bg-white/80 px-4 backdrop-blur-sm md:px-8">
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 transition-all duration-200 hover:bg-slate-50 hover:text-slate-900 active:scale-95 lg:hidden" @click="sidebarOpen = true" aria-label="Open navigation menu">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">{{ $pageHeading }}</h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('admin.notifications.index') }}" class="relative inline-flex rounded-xl p-2 text-slate-500 transition-all duration-150 hover:bg-slate-100 hover:text-slate-800" aria-label="Notifications">
                    @if($unread > 0)
                        <span class="absolute inset-0 rounded-xl bg-indigo-400/20 animate-ping pointer-events-none" aria-hidden="true"></span>
                        <span class="absolute -inset-0.5 rounded-xl ring-2 ring-indigo-400/40 animate-pulse pointer-events-none" aria-hidden="true"></span>
                    @endif
                    <svg class="relative h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.8569 17.0817C16.7514 16.857 18.5783 16.4116 20.3111 15.7719C18.8743 14.177 17.9998 12.0656 17.9998 9.75V9.04919C17.9999 9.03281 18 9.01641 18 9C18 5.68629 15.3137 3 12 3C8.68629 3 6 5.68629 6 9L5.9998 9.75C5.9998 12.0656 5.12527 14.177 3.68848 15.7719C5.4214 16.4116 7.24843 16.857 9.14314 17.0818M14.8569 17.0817C13.92 17.1928 12.9666 17.25 11.9998 17.25C11.0332 17.25 10.0799 17.1929 9.14314 17.0818M14.8569 17.0817C14.9498 17.3711 15 17.6797 15 18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18C9 17.6797 9.05019 17.3712 9.14314 17.0818" /></svg>
                    @if($unread > 0)
                        <span class="absolute right-0 top-0 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-0.5 text-[9px] font-bold text-white ring-2 ring-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                    @endif
                </a>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md shadow-indigo-500/25 ring-2 ring-white transition-transform duration-150 hover:scale-[1.02] active:scale-95"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Account menu"
                    >
                        {{ $navInitials }}
                    </button>
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        @click.outside="open = false"
                        class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-slate-100 bg-white py-1 shadow-lg ring-1 ring-slate-100"
                        role="menu"
                    >
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-indigo-50 hover:text-indigo-700" role="menuitem">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" role="none">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-slate-50" role="menuitem">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6 md:p-8">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/psgc.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @stack('scripts')
</body>
</html>
