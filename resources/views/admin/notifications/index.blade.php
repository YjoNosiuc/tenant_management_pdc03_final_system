@extends('layouts.admin', ['title' => 'Notifications'])

@section('title', 'Notifications')

@section('content')
    <div
        class="mx-auto max-w-4xl space-y-8"
        x-data="{ flashVisible: {{ session()->has('success') || session()->has('error') ? 'true' : 'false' }} }"
        x-init="
            @if(session()->has('success') || session()->has('error'))
                setTimeout(() => { flashVisible = false }, 4000);
            @endif
        "
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-1 rounded-full bg-indigo-500"></div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Notifications</h1>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 ring-1 ring-indigo-200/80">{{ $unreadCount }} unread</span>
                </div>
                <p class="mt-1 pl-3 text-sm text-slate-500">Stay on top of payments, leases, and tenant activity.</p>
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications.markAllAsRead') }}" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-indigo-500 sm:w-auto"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            @if($notifications->isEmpty())
                <div class="relative flex flex-col items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-b from-slate-50 to-white px-6 py-20 text-center">
                    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                        <span class="absolute left-[8%] top-[20%] h-2 w-2 rounded-full bg-indigo-200/80"></span>
                        <span class="absolute left-[18%] top-[65%] h-1.5 w-1.5 rounded-full bg-amber-200/90"></span>
                        <span class="absolute right-[12%] top-[30%] h-2.5 w-2.5 rounded-full bg-violet-200/80"></span>
                        <span class="absolute right-[22%] top-[70%] h-1.5 w-1.5 rounded-full bg-emerald-200/90"></span>
                        <span class="absolute left-[45%] top-[12%] h-1.5 w-1.5 rounded-full bg-rose-200/80"></span>
                        <span class="absolute right-[40%] bottom-[18%] h-2 w-2 rounded-full bg-indigo-300/60"></span>
                    </div>
                    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-400 ring-1 ring-indigo-100">
                        <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.8569 17.0817C16.7514 16.857 18.5783 16.4116 20.3111 15.7719C18.8743 14.177 17.9998 12.0656 17.9998 9.75V9.04919C17.9999 9.03281 18 9.01641 18 9C18 5.68629 15.3137 3 12 3C8.68629 3 6 5.68629 6 9L5.9998 9.75C5.9998 12.0656 5.12527 14.177 3.68848 15.7719C5.4214 16.4116 7.24843 16.857 9.14314 17.0818M14.8569 17.0817C13.92 17.1928 12.9666 17.25 11.9998 17.25C11.0332 17.25 10.0799 17.1929 9.14314 17.0818M14.8569 17.0817C14.9498 17.3711 15 17.6797 15 18C15 19.6569 13.6569 21 12 21C10.3431 21 9 19.6569 9 18C9 17.6797 9.05019 17.3712 9.14314 17.0818" /></svg>
                    </div>
                    <p class="relative mt-6 text-lg font-semibold text-slate-900">You're all caught up!</p>
                    <p class="relative mt-2 max-w-sm text-sm text-slate-500">No notifications yet — we'll show updates here when something needs your attention.</p>
                </div>
            @else
                <ul class="space-y-3">
                    @foreach($notifications as $notification)
                        @php
                        $iconConfig = match($notification->type) {
                            'payment_submitted' => ['bg-indigo-100 text-indigo-600', 'banknote'],
                            'payment_verified'  => ['bg-emerald-100 text-emerald-600', 'check-circle'],
                            'payment_rejected'  => ['bg-rose-100 text-rose-600', 'x-circle'],
                            'lease_expiring'    => ['bg-amber-100 text-amber-600', 'calendar'],
                            default             => ['bg-slate-100 text-slate-500', 'bell'],
                        };
                        $data = json_decode($notification->data, true);
                        @endphp
                        <li>
                            <a href="{{ route('admin.notifications.redirect', $notification->id) }}"
                               class="block cursor-pointer rounded-xl transition-all duration-200 hover:shadow-md
                                      {{ is_null($notification->read_at)
                                         ? 'bg-indigo-50/40 border-l-4 border-indigo-400'
                                         : 'bg-white hover:bg-slate-50/80 border-l-4 border-transparent' }}">
                                <div class="flex items-start gap-4 p-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconConfig[0] }}">
                                        @if($iconConfig[1] === 'banknote')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                            </svg>
                                        @elseif($iconConfig[1] === 'check-circle')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @elseif($iconConfig[1] === 'x-circle')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @elseif($iconConfig[1] === 'calendar')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm {{ is_null($notification->read_at) ? 'font-semibold text-slate-900' : 'font-medium text-slate-500' }}">
                                            {{ $data['message'] ?? $data['body'] ?? $data['title'] ?? '' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if(is_null($notification->read_at))
                                        <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500 animate-pulse" aria-hidden="true"></div>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
@endsection
