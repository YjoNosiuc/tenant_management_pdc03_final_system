<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RentTrack — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .rt-shell {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            min-height: 0;
        }

        .rt-left-panel {
            position: relative;
            width: 55%;
            height: 100vh;
            flex-shrink: 0;
            overflow: hidden;
            background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 45%, #4f46e5 75%, #7c3aed 100%);
        }

        .rt-circle-1 {
            position: absolute;
            top: -80px;
            right: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .rt-circle-2 {
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            pointer-events: none;
        }

        .rt-circle-3 {
            position: absolute;
            top: 40%;
            left: 30%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .rt-stat-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 12px 16px;
            color: #fff;
        }

        .rt-stat-card-1 {
            top: 24px;
            right: 24px;
            animation: float1 6s ease-in-out infinite;
        }

        .rt-stat-card-2 {
            bottom: 80px;
            left: 24px;
            animation: float2 8s ease-in-out infinite;
        }

        .rt-stat-card-3 {
            bottom: 24px;
            right: 24px;
            animation: float3 7s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes float2 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        @keyframes float3 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .rt-main-copy {
            position: absolute;
            left: 48px;
            top: 50%;
            transform: translateY(-50%);
            max-width: min(340px, calc(100% - 96px));
        }

        .rt-headline {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            max-width: 340px;
        }

        .rt-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
            margin-top: 12px;
            max-width: 300px;
        }

        .rt-feature-list {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rt-feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .rt-feature-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rt-right-panel {
            height: 100vh;
            width: 45%;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            background: #f8faff;
            background-image: radial-gradient(circle, #dde3f0 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .rt-form-card {
            background: #fff;
            border-radius: 24px;
            padding: 36px 40px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef0f6;
            width: 100%;
            max-width: 380px;
            max-height: 100vh;
            overflow: hidden;
        }

        .rt-field-input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 13px;
            color: #0f172a;
            background: #f8faff;
            outline: none;
        }

        .rt-field-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            background: #fff;
        }

        .rt-field-input--password {
            padding-right: 38px;
        }

        .rt-submit-btn {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.15s;
        }

        .rt-submit-btn:hover {
            opacity: 0.92;
            transform: scale(0.99);
        }

        @media (max-width: 768px) {
            .rt-left-panel {
                display: none;
            }

            .rt-right-panel {
                width: 100vw;
                flex: none;
            }

            .rt-form-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div
        class="rt-shell"
        x-data="{ showPassword: false, loading: false }"
    >
        {{-- LEFT 55% --}}
        <aside class="rt-left-panel" aria-hidden="false">
            <div class="rt-circle-1" aria-hidden="true"></div>
            <div class="rt-circle-2" aria-hidden="true"></div>
            <div class="rt-circle-3" aria-hidden="true"></div>

            <div class="absolute left-8 top-8 z-10 flex items-center gap-2">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-indigo-400" aria-hidden="true"></span>
                <span class="text-xl font-bold text-white">RentTrack</span>
            </div>

            <div class="rt-main-copy z-10">
                <h1 class="rt-headline">Manage your properties with confidence</h1>
                <p class="rt-subtitle">A smarter way to handle tenants, leases, and payments.</p>
                <ul class="rt-feature-list list-none">
                    <li class="rt-feature-item">
                        <span class="rt-feature-icon" aria-hidden="true">
                            <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span>Track leases and rental agreements</span>
                    </li>
                    <li class="rt-feature-item">
                        <span class="rt-feature-icon" aria-hidden="true">
                            <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span>Monitor payments and verify proofs</span>
                    </li>
                    <li class="rt-feature-item">
                        <span class="rt-feature-icon" aria-hidden="true">
                            <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <span>Stay notified on important updates</span>
                    </li>
                </ul>
            </div>

            <div class="rt-stat-card rt-stat-card-1 z-10 min-w-[140px]" aria-hidden="true">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60">Active Leases</p>
                        <p class="text-[22px] font-bold leading-tight">3</p>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-white/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
            </div>

            <div class="rt-stat-card rt-stat-card-2 z-10 min-w-[160px]" aria-hidden="true">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60">Rent Collected</p>
                        <p class="text-lg font-bold leading-tight">₱24,500</p>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-white/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
            </div>

            <div class="rt-stat-card rt-stat-card-3 z-10 min-w-[150px]" aria-hidden="true">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/60">Pending</p>
                        <p class="text-base font-bold leading-tight">2 payments</p>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-white/70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <div class="absolute bottom-5 z-10 text-white" style="left: 48px;">
                <p class="text-[13px] font-bold">RentTrack</p>
                <p class="mt-0.5 text-[11px] text-white/40">© 2026 All rights reserved.</p>
            </div>
        </aside>

        {{-- RIGHT 45% --}}
        <main class="rt-right-panel">
            <div class="rt-form-card w-full">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-center text-[11px] font-medium text-emerald-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mb-6 flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] bg-indigo-600" aria-hidden="true">
                        <svg class="h-[18px] w-[18px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </span>
                    <span class="text-lg font-bold text-slate-800">RentTrack</span>
                </div>

                <div class="mb-6">
                    <h2 class="text-[22px] font-extrabold text-slate-900">Welcome back</h2>
                    <p class="mt-1 text-[13px] text-slate-400">Sign in to your account</p>
                </div>

                <form
                    method="POST"
                    action="{{ route('login') }}"
                    class="flex flex-col gap-3.5"
                    @submit="loading = true"
                >
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-600">Email address</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                autofocus
                                required
                                class="rt-field-input"
                            />
                        </div>
                        @error('email')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-600">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="rt-field-input rt-field-input--password"
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                @click="showPassword = !showPassword"
                                :aria-pressed="showPassword"
                                aria-label="Toggle password visibility"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-1 flex items-center justify-between">
                        <label class="flex cursor-pointer select-none items-center">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                class="h-3.5 w-3.5 shrink-0 rounded border-slate-300"
                                style="accent-color: #4f46e5;"
                            />
                            <span class="ml-1.5 text-xs text-slate-500">Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:underline">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="rt-submit-btn mt-5" :disabled="loading">
                        <svg x-show="loading" x-cloak class="h-4 w-4 shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="loading" x-cloak>Signing in...</span>
                        <span x-show="!loading" class="flex items-center gap-2">
                            Sign In
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </button>
                </form>

                <div class="mt-4 flex items-center">
                    <span class="h-px flex-1 bg-slate-100" aria-hidden="true"></span>
                    <span class="px-3 text-[11px] text-slate-300">or</span>
                    <span class="h-px flex-1 bg-slate-100" aria-hidden="true"></span>
                </div>

                <p class="mt-3 text-center text-xs text-slate-400">Having trouble? Contact your property manager.</p>

                <p style="text-align:center; font-size:12px; color:#94a3b8; margin-top:8px;">
                    Don't have an account?
                    <a href="{{ route('register') }}" style="color:#4f46e5; font-weight:600;">Register as Owner</a>
                </p>
            </div>
        </main>
    </div>
</body>
</html>
