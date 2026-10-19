@php
    $cleDepartement = \App\Http\Controllers\DepenseAccesController::SESSION_DEPARTEMENT;
    $cleDemandeur = \App\Http\Controllers\DepenseAccesController::SESSION_DEMANDEUR;

    $portailDepartement = auth()->user()?->estCompteDepartement();

    $departementActif = $portailDepartement && session($cleDepartement)
        ? \App\Models\Departement::withoutGlobalScopes()->whereKey(session($cleDepartement))->value('nom')
        : null;
    $personneActive = $portailDepartement && session($cleDemandeur)
        ? \App\Models\Employeur::withoutGlobalScopes()->whereKey(session($cleDemandeur))->first(['nom', 'prenom'])
        : null;
    $personneActive = $personneActive ? trim($personneActive->prenom.' '.$personneActive->nom) : (auth()->user()?->name);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mes demandes') — GENIUS WORK</title>

    <link rel="manifest" href="{{ route('employe.depenses.manifeste') }}">
    <meta name="theme-color" content="#3a5faa">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Dépenses">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/png" href="{{ asset('images/depenses/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/depenses/icon-192.png') }}">

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

    @auth
    <header class="bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-40 pt-[env(safe-area-inset-top)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14 gap-3">
                <a href="{{ route('employe.depenses.index') }}" class="min-w-0 leading-tight">
                    @if ($departementActif)
                        <span class="block text-sm font-semibold text-slate-900 truncate">{{ $departementActif }}</span>
                    @endif
                    <span class="block {{ $departementActif ? 'text-xs text-slate-500' : 'text-sm font-semibold text-slate-900' }} truncate">{{ $personneActive }}</span>
                </a>

                <form method="POST" action="{{ route('employe.depenses.quitter') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-500 hover:text-[#3a5faa] hover:bg-slate-100">
                        <i data-lucide="log-out" class="h-4 w-4"></i> Quitter
                    </button>
                </form>
            </div>
        </div>
    </header>
    @endauth

    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-5 sm:py-8 pb-[calc(2rem+env(safe-area-inset-bottom))]">
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

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register(@json(route('employe.depenses.sw', [], false)), { scope: '/depense' }).catch(() => {});
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
