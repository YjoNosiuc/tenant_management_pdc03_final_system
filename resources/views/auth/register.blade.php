<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RentTrack — Create Account</title>
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

        .rt-right-panel {
            height: 100vh;
            width: 45%;
            flex: 1;
            min-width: 0;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
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
        x-data="{ showPassword: false, showPasswordConfirm: false, loading: false }"
    >
        <aside class="rt-left-panel" aria-hidden="false">
            <div class="rt-circle-1" aria-hidden="true"></div>
            <div class="rt-circle-2" aria-hidden="true"></div>

            <div class="absolute left-8 top-8 z-10 flex items-center gap-2">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-indigo-400" aria-hidden="true"></span>
                <span class="text-xl font-bold text-white">RentTrack</span>
            </div>

            <div class="rt-main-copy z-10">
                <h1 class="rt-headline">Create your owner account</h1>
                <p class="rt-subtitle">Start managing your properties today — leases, tenants, and payments in one place.</p>
            </div>

            <div class="absolute bottom-5 z-10 text-white" style="left: 48px;">
                <p class="text-[13px] font-bold">RentTrack</p>
                <p class="mt-0.5 text-[11px] text-white/40">© 2026 All rights reserved.</p>
            </div>
        </aside>

        <main class="rt-right-panel">
            <div class="rt-form-card w-full">
                <div class="mb-6 flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] bg-indigo-600" aria-hidden="true">
                        <svg class="h-[18px] w-[18px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </span>
                    <span class="text-lg font-bold text-slate-800">RentTrack</span>
                </div>

                <div class="mb-6">
                    <h2 class="text-[22px] font-extrabold text-slate-900">Create your owner account</h2>
                    <p class="mt-1 text-[13px] text-slate-400">Start managing your properties today</p>
                </div>

                <form
                    method="POST"
                    action="{{ route('register') }}"
                    class="flex flex-col gap-3.5"
                    @submit="loading = true"
                >
                    @csrf

                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">Name</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                class="rt-field-input"
                            />
                        </div>
                        @error('name')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

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
                                required
                                autocomplete="username"
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
                                required
                                autocomplete="new-password"
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

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-xs font-semibold text-slate-600">Confirm password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </span>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="rt-field-input rt-field-input--password"
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                :aria-pressed="showPasswordConfirm"
                                aria-label="Toggle confirm password visibility"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="rt-submit-btn mt-4" :disabled="loading">
                        <svg x-show="loading" x-cloak class="h-4 w-4 shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="loading" x-cloak>Creating account...</span>
                        <span x-show="!loading">Create Account</span>
                    </button>
                </form>

                <p class="mt-5 text-center text-xs text-slate-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:underline">Sign in</a>
                </p>
            </div>
        </main>
    </div>
</body>
</html>
