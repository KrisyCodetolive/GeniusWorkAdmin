<x-filament::page>
    @push('styles')
    <style>
        .onboarding-step {
            transition: all 0.3s ease;
        }
        .onboarding-step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        .step-icon {
            transition: transform 0.3s ease;
        }
        .onboarding-step:hover .step-icon {
            transform: scale(1.2);
        }
        .step-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        .step-details.open {
            max-height: 500px;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
    @endpush

    <div class="space-y-4">
        <!-- Header avec progression -->
        <div class="p-4 bg-gradient-to-r from-primary-700 to-primary-500 rounded-xl shadow-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Configuration de votre compte</h2>
                    <p class="text-sm opacity-80">
                        Suivez ces étapes pour configurer votre espace de travail
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex flex-col items-end">
                        <div class="text-2xl font-bold">{{ $this->progression }}%</div>
                        <div class="text-xs opacity-80">complété</div>
                    </div>
                    <div class="w-24 bg-white/20 rounded-full h-3">
                        <div class="bg-white h-3 rounded-full progress-bar" style="width: {{ $this->progression }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étapes en grille -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->etapes as $index => $etape)
                <div 
                    x-data="{ open: false }"
                    class="onboarding-step p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 
                        @if($etape['complete']) border-success-500 @endif fade-in"
                    style="animation-delay: {{ $index * 0.1 }}s"
                >
                    <div class="flex items-start gap-3 cursor-pointer" @click="open = !open">
                        <div class="flex-shrink-0 @if($etape['complete']) text-success-500 @else text-primary-500 @endif step-icon">
                            @svg($etape['icone'], 'h-7 w-7')
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-medium @if($etape['complete']) text-success-700 dark:text-success-500 @endif">
                                    {{ $etape['titre'] }}
                                    @if($etape['complete'])
                                        <span class="ml-1 text-success-500">@svg('heroicon-o-check-circle', 'h-4 w-4 inline')</span>
                                    @endif
                                </h3>
                                @if(isset($etape['statistiques']))
                                    <span class="text-sm font-medium px-2 py-0.5 rounded-full @if($etape['statistiques']['total'] > 0) bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300 @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                        {{ $etape['statistiques']['total'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $etape['description'] }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-gray-400">
                            <button @click.stop="open = !open">
                                <span x-show="!open">@svg('heroicon-o-chevron-down', 'h-4 w-4')</span>
                                <span x-show="open">@svg('heroicon-o-chevron-up', 'h-4 w-4')</span>
                            </button>
                        </div>
                    </div>

                    <!-- Détails dépliables -->
                    <div class="step-details mt-3" :class="{ 'open': open }">
                        @if(isset($etape['conseils']) && count($etape['conseils']) > 0)
                            <div class="mt-2 border-t border-gray-100 dark:border-gray-700 pt-2">
                                <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Conseils :</h4>
                                <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1 list-disc pl-4">
                                    @foreach($etape['conseils'] as $conseil)
                                        <li>{{ $conseil }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-3 flex justify-end">
                            <a href="{{ $etape['url'] }}" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[1.75rem] px-3 text-xs text-white shadow focus:ring-white border-transparent @if(!$etape['complete']) bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 pulse @else bg-success-600 hover:bg-success-500 focus:bg-success-700 focus:ring-offset-success-700 @endif">
                                @if($etape['complete'])
                                    Modifier
                                @else
                                    Configurer
                                @endif
                                @svg('heroicon-o-arrow-right', 'h-3 w-3 ml-1')
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Carte d'aide -->
        <div class="p-4 bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl shadow-md mt-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-medium">Besoin d'aide pour configurer votre compte ?</h3>
                    <p class="text-xs opacity-80 mt-1">
                        Notre équipe de support est disponible pour vous aider
                    </p>
                </div>
                <a href="{{url('https://wa.me/2250704750465') }}" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[1.75rem] px-3 text-xs bg-primary-600 text-white hover:bg-primary-500 dark:hover:bg-primary-400 border-transparent shadow">
                    @svg('heroicon-o-chat-bubble-left-right', 'h-4 w-4 mr-1')
                    Contacter le support
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animation d'entrée séquentielle pour les étapes
            const steps = document.querySelectorAll('.onboarding-step');
            steps.forEach((step, index) => {
                setTimeout(() => {
                    step.classList.add('fade-in');
                }, index * 100);
            });
        });
    </script>
    @endpush
</x-filament::page>
