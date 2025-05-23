<x-filament::widget>
    <style>
        .resource-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .resource-icon {
            transition: all 0.3s ease;
        }
        .resource-card:hover .resource-icon {
            transform: scale(1.2);
        }
        .resource-button {
            transition: all 0.2s ease;
            opacity: 0;
            transform: translateY(10px);
        }
        .resource-card:hover .resource-button {
            opacity: 1;
            transform: translateY(0);
        }
        .badge-animation {
            animation: pulse-subtle 2s infinite;
        }
        @keyframes pulse-subtle {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }
    </style>

    <div class="p-2 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-md">
        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold tracking-tight flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ressources Externes
                </h2>
                <span class="badge-animation text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                    {{ count($this->ressources) }} ressources
                </span>
            </div>
            
            <div class="space-y-2">
                @foreach($this->ressources as $index => $ressource)
                    <div wire:click="ouvrirRessource('{{ $ressource['url'] }}')" 
                         class="resource-card cursor-pointer p-2 rounded-lg border border-gray-200 dark:border-gray-700 
                            @if($ressource['couleur'] === 'indigo') bg-indigo-50 dark:bg-indigo-900/20 @endif
                            @if($ressource['couleur'] === 'amber') bg-amber-50 dark:bg-amber-900/20 @endif
                            @if($ressource['couleur'] === 'emerald') bg-emerald-50 dark:bg-emerald-900/20 @endif
                            @if($ressource['couleur'] === 'blue') bg-blue-50 dark:bg-blue-900/20 @endif
                            @if($ressource['couleur'] === 'purple') bg-purple-50 dark:bg-purple-900/20 @endif">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 
                                @if($ressource['couleur'] === 'indigo') text-indigo-500 @endif
                                @if($ressource['couleur'] === 'amber') text-amber-500 @endif
                                @if($ressource['couleur'] === 'emerald') text-emerald-500 @endif
                                @if($ressource['couleur'] === 'blue') text-blue-500 @endif
                                @if($ressource['couleur'] === 'purple') text-purple-500 @endif
                                resource-icon">
                                @svg($ressource['icone'], 'h-5 w-5')
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium truncate 
                                    @if($ressource['couleur'] === 'indigo') text-indigo-700 dark:text-indigo-300 @endif
                                    @if($ressource['couleur'] === 'amber') text-amber-700 dark:text-amber-300 @endif
                                    @if($ressource['couleur'] === 'emerald') text-emerald-700 dark:text-emerald-300 @endif
                                    @if($ressource['couleur'] === 'blue') text-blue-700 dark:text-blue-300 @endif
                                    @if($ressource['couleur'] === 'purple') text-purple-700 dark:text-purple-300 @endif">
                                    {{ $ressource['titre'] }}
                                </h4>
                            </div>
                        </div>
                        
                        <div class="mt-1 pl-8">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $ressource['description'] }}
                            </p>
                            
                            <div class="resource-button mt-2 flex justify-end">
                                <span class="inline-flex items-center gap-1 text-xs 
                                    @if($ressource['couleur'] === 'indigo') text-indigo-600 dark:text-indigo-400 @endif
                                    @if($ressource['couleur'] === 'amber') text-amber-600 dark:text-amber-400 @endif
                                    @if($ressource['couleur'] === 'emerald') text-emerald-600 dark:text-emerald-400 @endif
                                    @if($ressource['couleur'] === 'blue') text-blue-600 dark:text-blue-400 @endif
                                    @if($ressource['couleur'] === 'purple') text-purple-600 dark:text-purple-400 @endif">
                                    <span>Accéder</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-3 text-center">
                <a href="https://resources.geniuswork.com" target="_blank" class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 flex items-center justify-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    <span>Toutes les ressources</span>
                </a>
            </div>
        </div>
    </div>
</x-filament::widget>
