<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace employé') — GENIUS WORK</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>

    <style>
        :root { --brand: #3a5faa; --brand-dark: #2f4d8c; --brand-soft: #eef2fa; }
        html { font-size: 16px; -webkit-text-size-adjust: 100%; }
        body { font-family: 'Inter', sans-serif; line-height: 1.6; background: #f6f7fb; }
        .scrollbar-none { scrollbar-width: none; } .scrollbar-none::-webkit-scrollbar { display: none; }
        [x-cloak] { display: none !important; }
        .btn-primary { background: var(--brand); transition: background-color .15s; }
        .btn-primary:hover { background: var(--brand-dark); }
        .card { background: #fff; border: 1px solid #e6e9f0; border-radius: 1.25rem; box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 16px -8px rgba(36, 59, 110, .12); }
        @layer base {
            input:not([type=checkbox]):not([type=radio]):not([type=file]), select, textarea {
                background: #fff; border: 1px solid #cbd5e1; padding: .6rem .875rem;
            }
            select { padding-right: 2.25rem; }
        }
        /* Champs plus grands et lisibles : moins d'effort, surtout sur mobile */
        input:not([type=checkbox]):not([type=file]), select, textarea { min-height: 2.75rem; font-size: 1rem !important; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 3px solid rgba(58, 95, 170, .35); outline-offset: 2px;
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
</head>
<body class="min-h-screen font-sans text-slate-700 antialiased">

    <header class="bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-40" x-data="{ open: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('employe.depenses.index') }}" class="flex items-center gap-2 shrink-0">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Genius Work" class="h-8 w-auto">
                    </a>
                    <span class="hidden sm:inline text-sm font-medium text-slate-400 border-l border-slate-200 pl-3">Espace employé</span>
                </div>

                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('employe.depenses.index') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('employe.depenses.*') ? 'bg-[#eef2fa] text-[#3a5faa]' : 'text-slate-600 hover:bg-slate-100' }}">
                        Mes dépenses
                    </a>
                    @if (in_array(auth()->user()->role, ['super_admin', 'support', 'admin', 'manager', 'comptable']))
                        <a href="/admin" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                            Back-office
                        </a>
                    @endif
                </nav>

                <div class="hidden md:flex items-center gap-3">
                    <span class="h-9 w-9 rounded-full bg-[#eef2fa] text-[#3a5faa] flex items-center justify-center text-sm font-semibold">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="text-sm text-slate-600 truncate max-w-[160px]">{{ auth()->user()->name }}</span>
                    <a href="{{ route('logout') }}" class="text-sm font-medium text-slate-500 hover:text-[#3a5faa] px-2 py-1">
                        Déconnexion
                    </a>
                </div>

                <button @click="open = !open" class="md:hidden p-2.5 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Menu">
                    <i data-lucide="menu" class="h-5 w-5" x-show="!open"></i>
                    <i data-lucide="x" class="h-5 w-5" x-show="open" x-cloak></i>
                </button>
            </div>

            <nav x-show="open" x-cloak x-transition class="md:hidden pb-3 space-y-1">
                <a href="{{ route('employe.depenses.index') }}"
                   class="block px-3 py-3 rounded-lg text-base font-medium {{ request()->routeIs('employe.depenses.*') ? 'bg-[#eef2fa] text-[#3a5faa]' : 'text-slate-600' }}">
                    Mes dépenses
                </a>
                @if (in_array(auth()->user()->role, ['super_admin', 'support', 'admin', 'manager', 'comptable']))
                    <a href="/admin" class="block px-3 py-3 rounded-lg text-base font-medium text-slate-600">Back-office</a>
                @endif
                <a href="{{ route('logout') }}" class="block px-3 py-3 rounded-lg text-base font-medium text-red-600">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10 min-h-[calc(100vh-4rem)]">
        @if (session('success'))
            <div x-data="{ ok: true }" x-show="ok" role="status" class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 flex items-start gap-3">
                <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0 mt-0.5 text-emerald-600"></i>
                <span class="flex-1">{{ session('success') }}</span>
                <button type="button" @click="ok = false" class="text-emerald-700/60 hover:text-emerald-800" aria-label="Fermer"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
        @endif
        @if (session('error'))
            <div role="alert" class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-900 px-4 py-3 flex items-start gap-3">
                <i data-lucide="alert-circle" class="h-5 w-5 shrink-0 mt-0.5 text-red-600"></i>
                <span class="flex-1">{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-900 px-4 py-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
    @stack('scripts')
</body>
</html>
