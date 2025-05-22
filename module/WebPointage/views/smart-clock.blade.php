@extends('layouts.smart-clock')

@section('content')
<div class="h-screen w-screen flex items-center justify-center relative overflow-hidden">
    <!-- Particules d'arrière-plan -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for ($i = 0; $i < 20; $i++)
            <div class="absolute rounded-full bg-blue-200/20 animate-float"
                 style="
                    width: {{ rand(4, 12) }}px;
                    height: {{ rand(4, 12) }}px;
                    left: {{ rand(0, 100) }}%;
                    top: {{ rand(0, 100) }}%;
                    animation-delay: -{{ rand(0, 5000) }}ms;
                 ">
            </div>
        @endfor
    </div>
    
    <!-- Carte principale -->
    <div class="w-[95vw] h-[95vh] max-w-7xl glass-morphism rounded-[2.5rem] shadow-2xl border border-white/20 p-4 lg:p-8 relative overflow-hidden flex flex-col">
        <!-- Effet de brillance -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-400/5 via-transparent to-purple-400/5"></div>
        
        <!-- Conteneur principal avec scroll si nécessaire -->
        <div class="flex-1 flex flex-col items-center justify-start overflow-y-auto custom-scrollbar">
            @include('components.smart-clock.clock')

            <!-- Grille principale -->
            <div class="grid lg:grid-cols-2 gap-6 lg:gap-8 w-full px-4">
                @include('components.smart-clock.scanner')
                @include('components.smart-clock.recent-logs')
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-auto pt-4 pb-2 w-full text-center text-gray-500">
            <p class="text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </footer>
    </div>

    @include('components.smart-clock.notification')
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/smart-clock.js') }}"></script>
<script src="{{ asset('assets/js/scanner.js') }}" ></script>
@endpush

@push('styles')
<style>
    @keyframes scan {
        0% { transform: translateY(0); opacity: 0.8; }
        100% { transform: translateY(320px); opacity: 0; }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes glow {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .animate-scan {
        animation: scan 2s linear infinite;
        transition: all 0.3s ease;
    }

    .scan-success {
        background: linear-gradient(to right, transparent, #10B981, transparent) !important;
        box-shadow: 0 0 20px #10B981;
        height: 4px !important;
    }

    .glass-morphism {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.5);
        border-radius: 3px;
    }

    #notification-area {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .translate-y-[-150%] {
        transform: translateY(-150%);
    }

    .translate-y-0 {
        transform: translateY(0);
    }
</style>
@endpush