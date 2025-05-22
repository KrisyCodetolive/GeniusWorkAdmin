@extends('layouts.smart-clock')

@section('content')
<div class="h-screen w-screen flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Particules d'arrière-plan améliorées -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for ($i = 0; $i < 30; $i++)
            <div class="absolute rounded-full bg-blue-200/20 animate-float"
                 style="
                    width: {{ rand(4, 16) }}px;
                    height: {{ rand(4, 16) }}px;
                    left: {{ rand(0, 100) }}%;
                    top: {{ rand(0, 100) }}%;
                    animation-delay: -{{ rand(0, 5000) }}ms;
                    animation-duration: {{ rand(3000, 8000) }}ms;
                 ">
            </div>
        @endfor
    </div>

    <!-- Carte principale avec design amélioré -->
    <div class="w-[95vw] h-[95vh] max-w-7xl glass-morphism rounded-[2.5rem] shadow-2xl border border-white/20 p-4 lg:p-8 relative overflow-hidden flex flex-col">
        <!-- Effet de brillance amélioré -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 via-transparent to-purple-400/10"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-300/10 rounded-full filter blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-300/10 rounded-full filter blur-3xl translate-y-1/2 -translate-x-1/3"></div>
        
        <!-- Header avec logo et horloge -->
        <div class="relative z-10 flex justify-between items-center mb-6">
            <!-- Logo Entreprise -->
            <div class="flex items-center">
                <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-12 w-auto">
                <div class="ml-3 hidden md:block">
                    <h1 class="text-xl font-bold text-gray-800">{{ config('app.name') }}</h1>
                    <p class="text-sm text-gray-500">Système de pointage intelligent</p>
                </div>
            </div>
            
            <!-- Horloge en temps réel -->
            <div class="flex flex-col items-center">
                <div class="flex items-baseline">
                    <div class="text-4xl font-bold text-blue-600" id="current-time">{{ date('H:i') }}</div>
                    <div class="ml-1 text-lg text-blue-400" id="current-seconds">{{ date('s') }}</div>
                </div>
                <div class="text-sm text-gray-500" id="current-date">{{ date('d F Y') }}</div>
            </div>
        </div>

      
        <!-- Conteneur principal avec scroll si nécessaire -->
        <div class="flex-1 flex flex-col items-center justify-start overflow-y-auto custom-scrollbar">
          
            
            <!-- Grille principale avec deux colonnes sur grand écran -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 w-full max-w-6xl mx-auto px-4 mt-6">
                <!-- Scanner (colonne gauche sur desktop) -->
                <div class="flex flex-col">
                    @include('components.smart-clock.scanner')
                </div>
                
                <!-- Activité récente (colonne droite sur desktop) -->
                <div class="flex flex-col bg-white/90 backdrop-blur-sm rounded-xl shadow-md p-6 border border-blue-100/50 hover:shadow-lg transition-all duration-300">
                    @include('components.smart-clock.site-info')
                    
                    <!-- Champ caché pour l'ID du site -->
                    <input type="hidden" id="site-id" value="{{ $site->id ?? '' }}">
                </div>
            </div>
            
            <!-- Zone de notification -->
            <div id="notification-area" class="fixed top-0 left-0 right-0 transform translate-y-[-150%] opacity-0 transition-all duration-500 z-50">
                <div class="max-w-lg mx-auto mt-4 p-4 bg-white rounded-lg shadow-lg flex items-center">
                    <div id="notification-icon" class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg bg-gradient-to-br from-blue-400 to-blue-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 id="notification-title" class="text-lg font-medium text-gray-900">Notification</h3>
                        <p id="notification-message" class="text-sm text-gray-600">Message de notification</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer amélioré -->
        <div class="mt-auto pt-4 text-center">
            <div class="text-xs text-gray-500">
                {{ config('app.name') }} {{ date('Y') }}. Tous droits réservés.
            </div>
            <div class="flex items-center justify-center mt-1 space-x-2">
                <a href="#" class="text-blue-500 hover:text-blue-700 text-xs">Aide</a>
                <span class="text-gray-300">|</span>
                <a href="#" class="text-blue-500 hover:text-blue-700 text-xs">Support</a>
                <span class="text-gray-300">|</span>
                <a href="#" class="text-blue-500 hover:text-blue-700 text-xs">Confidentialité</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
  <!-- Script pour l'horloge en temps réel -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateClock() {
            const now = new Date();
            
            // Format de l'heure (HH:MM)
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const timeString = `${hours}:${minutes}`;
            
            // Format des secondes
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            // Format de la date (jour mois année)
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const dateString = now.toLocaleDateString('fr-FR', options);
            
            // Mise à jour des éléments HTML
            document.getElementById('current-time').textContent = timeString;
            document.getElementById('current-seconds').textContent = seconds;
            document.getElementById('current-date').textContent = dateString;
        }
        
        // Mettre à jour l'horloge immédiatement
        updateClock();
        
        // Puis mettre à jour toutes les secondes
        setInterval(updateClock, 1000);
    });
</script>

<!-- Inclusion d'Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="{{ asset('js/smart-clock-fixed.js') }}"></script>
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
    
    @keyframes float-random {
        0%, 100% { transform: translate(0, 0); }
        25% { transform: translate(5px, -5px); }
        50% { transform: translate(0, -10px); }
        75% { transform: translate(-5px, -5px); }
    }

    @keyframes glow {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .animate-scan {
        animation: scan 2s linear infinite;
        transition: all 0.3s ease;
    }
    
    .animate-float-random {
        animation: float-random 5s ease-in-out infinite;
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
    
    .clock-time {
        text-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
    }
</style>
@endpush