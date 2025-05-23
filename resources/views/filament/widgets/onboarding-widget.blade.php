<x-filament::widget>
    <style>
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.5s ease;
        }
        .onboarding-card {
            transition: all 0.3s ease;
        }
        .onboarding-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>

    <div class="p-2 bg-gradient-to-r from-primary-700 to-primary-500 rounded-xl shadow-md">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Cercle de progression animé -->
                    <div class="relative flex-shrink-0 w-16 h-16">
                        @php
                            $progress = $this->progression;
                            $radius = 45;
                            $circumference = 2 * pi() * $radius;
                            $dashoffset = $circumference - ($progress / 100) * $circumference;
                        @endphp
                        
                        <svg class="progress-ring w-16 h-16" viewBox="0 0 100 100">
                            <!-- Cercle de fond -->
                            <circle 
                                class="text-gray-200 dark:text-gray-700" 
                                stroke="currentColor" 
                                stroke-width="8" 
                                fill="transparent" 
                                r="{{ $radius }}" 
                                cx="50" 
                                cy="50" 
                            />
                            <!-- Cercle de progression -->
                            <circle 
                                class="progress-ring-circle text-primary-500" 
                                stroke="currentColor" 
                                stroke-width="8" 
                                fill="transparent" 
                                r="{{ $radius }}" 
                                cx="50" 
                                cy="50" 
                                stroke-dasharray="{{ $circumference }}" 
                                stroke-dashoffset="{{ $dashoffset }}" 
                                stroke-linecap="round"
                            />
                        </svg>
                        <!-- Pourcentage au centre -->
                        <div class="absolute inset-0 flex items-center justify-center text-lg font-bold text-primary-600 dark:text-primary-400">
                            {{ $progress }}%
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-bold tracking-tight">Configuration de votre compte</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Terminez la configuration de votre espace de travail
                        </p>
                    </div>
                </div>
                
                <a href="{{ route('filament.admin.pages.onboarding') }}" 
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors shadow-sm pulse">
                    <span>Configurer</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
            
            <!-- Barre de progression -->
            <div class="mt-4 w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700 overflow-hidden">
                <div class="bg-primary-600 h-1.5 rounded-full transition-all duration-500 ease-in-out" style="width: {{ $this->progression }}%"></div>
            </div>
            
            <!-- Étapes à compléter -->
            @php
                $etapesIncompletes = collect($this->etapes)->filter(fn ($etape) => !$etape['complete'])->take(1);
                $etapesCompletes = collect($this->etapes)->filter(fn ($etape) => $etape['complete'])->take(1);
            @endphp
            
            @if($etapesIncompletes->count() > 0)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prochaine étape :</h3>
                    @foreach($etapesIncompletes as $etape)
                        <a href="{{ $etape['url'] }}" class="onboarding-card block p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 text-primary-500 dark:text-primary-400">
                                    @svg($etape['icone'], 'h-6 w-6')
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $etape['titre'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $etape['description'] }}</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
            
            @if($this->progression == 100)
                <div class="mt-4 p-3 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 text-success-500">
                            @svg('heroicon-o-check-circle', 'h-6 w-6')
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-success-700 dark:text-success-400">Configuration terminée !</h4>
                            <p class="text-xs text-success-600 dark:text-success-500">Votre espace de travail est entièrement configuré.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament::widget>
