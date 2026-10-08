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

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .btn-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); }
        .btn-primary:hover { filter: brightness(1.05); }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-gray-800 antialiased">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-40" x-data="{ open: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('employe.depenses.index') }}" class="flex items-center gap-2 shrink-0">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Genius Work" class="h-8 w-auto">
                    </a>
                    <span class="hidden sm:inline text-sm font-semibold text-gray-400">Espace employé</span>
                </div>

                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('employe.depenses.index') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('employe.depenses.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                        Mes dépenses
                    </a>
                    @if (in_array(auth()->user()->role, ['super_admin', 'support', 'admin', 'manager', 'comptable']))
                        <a href="/admin" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                            Back-office
                        </a>
                    @endif
                </nav>

                <div class="hidden md:flex items-center gap-3">
                    <span class="text-sm text-gray-500 truncate max-w-[160px]">{{ auth()->user()->name }}</span>
                    <a href="{{ route('logout') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        Déconnexion
                    </a>
                </div>

                <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                    <i data-lucide="menu" class="h-5 w-5" x-show="!open"></i>
                    <i data-lucide="x" class="h-5 w-5" x-show="open" x-cloak></i>
                </button>
            </div>

            <nav x-show="open" x-cloak x-transition class="md:hidden pb-3 space-y-1">
                <a href="{{ route('employe.depenses.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('employe.depenses.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600' }}">
                    Mes dépenses
                </a>
                @if (in_array(auth()->user()->role, ['super_admin', 'support', 'admin', 'manager', 'comptable']))
                    <a href="/admin" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600">Back-office</a>
                @endif
                <a href="{{ route('logout') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
        @if (session('success'))
            <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-start gap-2">
                <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0 mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flex items-start gap-2">
                <i data-lucide="alert-circle" class="h-5 w-5 shrink-0 mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
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
