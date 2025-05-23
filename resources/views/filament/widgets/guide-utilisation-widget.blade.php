<x-filament::widget>
    <style>
        .guide-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .guide-icon {
            transition: transform 0.3s ease;
        }
        .guide-card:hover .guide-icon {
            transform: scale(1.2);
        }
        .category-badge {
            transition: all 0.3s ease;
        }
        .guide-card:hover .category-badge {
            background-color: theme('colors.primary.600');
            color: white;
        }
        .guide-video-button {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(10px);
        }
        .guide-card:hover .guide-video-button {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <div class="p-2 bg-gradient-to-r from-indigo-700 to-blue-500 rounded-xl shadow-md">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold tracking-tight flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Guide d'utilisation
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Découvrez comment utiliser toutes les fonctionnalités de l'application
                    </p>
                </div>
                <a href="{{ route('filament.admin.pages.dashboard') }}?all_guides=true" 
                   class="flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-full hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-800 transition-colors">
                    <span>Tous les guides</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach(collect($this->guides)->take(4) as $index => $guide)
                    <div wire:click="ouvrirGuide('{{ $guide['id'] }}')" 
                         class="guide-card relative p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer"
                         style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 text-indigo-500 dark:text-indigo-400 guide-icon">
                                @svg($guide['icone'], 'h-6 w-6')
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $guide['titre'] }}</h4>
                                    <span class="category-badge text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300">
                                        {{ ucfirst($guide['categorie']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $guide['description'] }}
                                </p>
                                
                                @if(isset($guide['video']))
                                    <div class="guide-video-button mt-2 flex justify-end">
                                        <a href="{{ $guide['video'] }}" target="_blank" class="flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300" onclick="event.stopPropagation();">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Voir la vidéo</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4 flex justify-center">
                <a href="#" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Besoin d'aide supplémentaire ?</span>
                </a>
            </div>
        </div>
    </div>
</x-filament::widget>
