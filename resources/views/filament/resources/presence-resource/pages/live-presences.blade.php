<x-filament-panels::page>
    @include('filament.resources.presence-resource.pages.notification-sound')
    
    <!-- Inclusion du fichier CSS personnalisé -->
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/live-presences.css') }}">
        <link rel="stylesheet" href="{{ asset('css/presence-stats.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <style>
            /* Styles spécifiques à cette page */
            .presence-card {
                transition: all 0.3s ease;
            }
            
            .presence-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .stats-item {
                position: relative;
                overflow: hidden;
            }
            
            .stats-item::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transform: translateX(-100%);
            }
            
            .stats-item:hover::after {
                animation: shimmer 1.5s infinite;
            }
            
            @keyframes shimmer {
                100% {
                    transform: translateX(100%);
                }
            }
        </style>
    @endpush
    
    <div class="space-y-6">
        <!-- En-tête avec sélection de site -->
        <div class="p-4 bg-white rounded-lg shadow-md sm:p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Présences en temps réel
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Suivez les entrées, sorties et pauses en temps réel</p>
                </div>
                
                <div class="w-full sm:w-auto flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <!-- Bouton pour basculer entre les vues -->
                    <button wire:click="toggleViewMode" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-md hover:from-purple-700 hover:to-indigo-700 transition-colors text-sm flex items-center justify-center">
                        @if($viewMode === 'standard')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            Vue hiérarchique
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Vue standard
                        @endif
                    </button>
                    
                    <select id="site-selector" wire:model.live="selectedSiteId" wire:change="updateSite($event.target.value)" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @foreach($sites as $site)
                            <option value="{{ $site['id'] }}">{{ $site['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Section des statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stats-item bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">Entrées aujourd'hui</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $statsToday['entree'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-blue-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($statsToday['entree'] / max(1, $statsToday['total'])) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-item bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Sorties aujourd'hui</h3>
                            <p class="text-2xl font-bold text-red-900">{{ $statsToday['sortie'] }}</p>
                        </div>
                        <div class="p-3 bg-red-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-red-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ min(100, ($statsToday['sortie'] / max(1, $statsToday['total'])) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-item bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-amber-800">Pauses aujourd'hui</h3>
                            <p class="text-2xl font-bold text-amber-900">{{ $statsToday['pause_debut'] + $statsToday['pause_fin'] }}</p>
                        </div>
                        <div class="p-3 bg-amber-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-amber-200 rounded-full h-2">
                            <div class="bg-amber-600 h-2 rounded-full" style="width: {{ min(100, (($statsToday['pause_debut'] + $statsToday['pause_fin']) / max(1, $statsToday['total'])) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-item bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-emerald-800">Total aujourd'hui</h3>
                            <p class="text-2xl font-bold text-emerald-900">{{ $statsToday['total'] }}</p>
                        </div>
                        <div class="p-3 bg-emerald-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-emerald-200 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques rapides (visibles dans les deux modes) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="stats-item bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Entrées aujourd'hui</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $presenceStats->entrees_today ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stats-item bg-gradient-to-br from-red-50 to-pink-50 p-4 rounded-lg border border-red-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sorties aujourd'hui</p>
                        <p class="text-2xl font-bold text-red-600">{{ $presenceStats->sorties_today ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stats-item bg-gradient-to-br from-green-50 to-teal-50 p-4 rounded-lg border border-green-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Présents actuellement</p>
                        <p class="text-2xl font-bold text-green-600">{{ $presenceStats->currently_present ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stats-item bg-gradient-to-br from-yellow-50 to-amber-50 p-4 rounded-lg border border-yellow-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">En pause</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $presenceStats->on_break ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        @if($viewMode === 'standard')
        <!-- Statistiques des présences (vue standard) -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques des présences</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="stats-item bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Entrées aujourd'hui</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $presenceStats->entrees_today ?? 0 }}</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-item bg-gradient-to-br from-red-50 to-pink-50 p-4 rounded-lg border border-red-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Sorties aujourd'hui</p>
                                <p class="text-2xl font-bold text-red-600">{{ $presenceStats->sorties_today ?? 0 }}</p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-item bg-gradient-to-br from-green-50 to-teal-50 p-4 rounded-lg border border-green-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Présents actuellement</p>
                                <p class="text-2xl font-bold text-green-600">{{ $presenceStats->currently_present ?? 0 }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-item bg-gradient-to-br from-yellow-50 to-amber-50 p-4 rounded-lg border border-yellow-100 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">En pause</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $presenceStats->on_break ?? 0 }}</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques de statistiques -->
                <div class="mt-6">
                    <div x-data="{ activeTab: 'daily' }" class="border-b border-gray-200">
                        <nav class="flex -mb-px space-x-8">
                            <button @click="activeTab = 'daily'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'daily', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'daily' }" class="py-4 px-1 border-b-2 font-medium text-sm">
                                Statistiques quotidiennes
                            </button>
                            <button @click="activeTab = 'weekly'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'weekly', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'weekly' }" class="py-4 px-1 border-b-2 font-medium text-sm">
                                Répartition par jour
                            </button>
                            <button @click="activeTab = 'hourly'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'hourly', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'hourly' }" class="py-4 px-1 border-b-2 font-medium text-sm">
                                Répartition horaire
                            </button>
                        </nav>
                    </div>
                    
                    <div class="mt-4">
                        <div x-show="activeTab === 'daily'" class="h-64">
                            <canvas id="dailyStatsChart"></canvas>
                        </div>
                        <div x-show="activeTab === 'weekly'" class="h-64" style="display: none;">
                            <canvas id="weeklyStatsChart"></canvas>
                        </div>
                        <div x-show="activeTab === 'hourly'" class="h-64" style="display: none;">
                            <canvas id="hourlyStatsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Section des statistiques avancées (vue standard) -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden stats-section">
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 px-4 py-3">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Statistiques détaillées
                </h2>
            </div>
            
            <!-- Élément caché contenant les données des statistiques -->
            <div id="presence-stats-data" data-stats="{{ json_encode($presenceStats) }}" class="hidden"></div>
            
            <div class="p-6">
                <!-- Cartes de statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="stats-card bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-green-800">Entrées aujourd'hui</h3>
                                <p class="text-2xl font-bold text-green-600" id="entrees-count">{{ $presenceStats->entrees_today ?? 0 }}</p>
                            </div>
                            <div class="text-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Sorties aujourd'hui</h3>
                                <p class="text-2xl font-bold text-red-600" id="sorties-count">{{ $presenceStats->sorties_today ?? 0 }}</p>
                            </div>
                            <div class="text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-blue-800">Actuellement présents</h3>
                                <p class="text-2xl font-bold text-blue-600" id="present-count">{{ $presenceStats->currently_present ?? 0 }}</p>
                            </div>
                            <div class="text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-yellow-800">En pause</h3>
                                <p class="text-2xl font-bold text-yellow-600" id="pause-count">{{ $presenceStats->on_break ?? 0 }}</p>
                            </div>
                            <div class="text-yellow-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Graphique des entrées/sorties par jour -->
                    <div class="chart-container bg-white p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Entrées et sorties par jour</h3>
                        <canvas id="dailyChart" height="200"></canvas>
                    </div>
                    
                    <!-- Graphique de distribution hebdomadaire -->
                    <div class="chart-container bg-white p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Distribution hebdomadaire</h3>
                        <canvas id="weeklyChart" height="200"></canvas>
                    </div>
                    
                    <!-- Graphique des entrées/sorties par heure -->
                    <div class="chart-container bg-white p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Entrées et sorties par heure</h3>
                        <canvas id="hourlyChart" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Info-bulle explicative -->
                <div class="info-card mt-4">
                    <div class="info-card-title flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Information
                    </div>
                    <div class="info-card-content">
                        Les statistiques sont mises à jour en temps réel et reflètent les données de la semaine en cours. Utilisez ces informations pour analyser les tendances de présence et optimiser la gestion du personnel.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- File d'attente des présences (vue standard) -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-3">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    File d'attente des présences
                    <span class="ml-2 px-2 py-0.5 bg-white text-indigo-700 rounded-full text-xs font-medium">{{ count($queuedPresences) }}</span>
                </h2>
            </div>
            
            <div class="p-4">
                <div class="flex mb-4 space-x-2">
                    <button wire:click="filterPresencesByType(null)" class="px-3 py-1 text-sm {{ is_null($selectedType) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                        Tous
                    </button>
                    <button wire:click="filterPresencesByType('entree')" class="px-3 py-1 text-sm {{ $selectedType === 'entree' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                        Entrées
                    </button>
                    <button wire:click="filterPresencesByType('sortie')" class="px-3 py-1 text-sm {{ $selectedType === 'sortie' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                        Sorties
                    </button>
                    <button wire:click="filterPresencesByType('pause_debut')" class="px-3 py-1 text-sm {{ $selectedType === 'pause_debut' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                        Début pause
                    </button>
                    <button wire:click="filterPresencesByType('pause_fin')" class="px-3 py-1 text-sm {{ $selectedType === 'pause_fin' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                        Fin pause
                    </button>
                </div>
                
                @if(count($queuedPresences) > 0)
                    <div id="presence-queue" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($queuedPresences as $presence)
                            <div class="presence-card bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg shadow-sm overflow-hidden border border-gray-200">
                                <div class="p-4">
                                    <div class="flex items-center mb-3">
                                        <div class="relative">
                                            @if($presence->user && $presence->user->photo)
                                                <img src="{{ asset('storage/' . $presence->user->photo) }}" alt="{{ $presence->user->name }}" class="employee-avatar h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm">
                                            @else
                                                <div class="employee-avatar h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold">
                                                    {{ $presence->user ? substr($presence->user->name, 0, 1) : '?' }}
                                                </div>
                                            @endif
                                            
                                            <div class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full 
                                                @if($presence->type == 'entree') bg-green-500
                                                @elseif($presence->type == 'sortie') bg-red-500
                                                @elseif($presence->type == 'pause_debut') bg-yellow-500
                                                @elseif($presence->type == 'pause_fin') bg-blue-500
                                                @endif
                                                border-2 border-white">
                                            </div>
                                        </div>
                                        
                                        <div class="ml-3">
                                            <h3 class="text-sm font-semibold text-gray-800">{{ $presence->user->name ?? 'Utilisateur inconnu' }}</h3>
                                            <p class="text-xs text-gray-500">{{ $presence->site->nom ?? 'Site inconnu' }}</p>
                                        </div>
                                        
                                        <div class="ml-auto">
                                            <span class="badge-type inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($presence->type == 'entree') bg-green-100 text-green-800
                                                @elseif($presence->type == 'sortie') bg-red-100 text-red-800
                                                @elseif($presence->type == 'pause_debut') bg-yellow-100 text-yellow-800
                                                @elseif($presence->type == 'pause_fin') bg-blue-100 text-blue-800
                                                @endif">
                                                @if($presence->type == 'entree') 
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Entrée
                                                @elseif($presence->type == 'sortie')
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Sortie
                                                @elseif($presence->type == 'pause_debut')
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Début pause
                                                @elseif($presence->type == 'pause_fin')
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.752 11.168l-3.197-2.132A1 1 0 0112 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 011.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Fin pause
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $presence->created_at->format('H:i:s') }}
                                        </div>
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $presence->distance_site ? number_format($presence->distance_site, 0) . 'm' : 'N/A' }}
                                        </div>
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            {{ ucfirst($presence->methode_pointage) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between space-x-2">
                                        <button wire:click="validatePresence({{ $presence->id }})" class="action-button flex-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Valider
                                        </button>
                                        <button wire:click="rejectPresence({{ $presence->id }})" class="action-button flex-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Rejeter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-2 text-gray-500">Aucune présence en attente de validation</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Historique des présences (vue standard) -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-4 py-3">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Historique des présences
                    <span class="ml-2 px-2 py-0.5 bg-white text-indigo-700 rounded-full text-xs font-medium">{{ count($presences) }}</span>
                </h2>
            </div>
            
            <div class="p-4">
                <div class="flex flex-wrap items-center justify-between mb-4">
                    <div class="flex space-x-2 mb-2 sm:mb-0">
                        <button wire:click="filterPresencesByType(null)" class="px-3 py-1 text-sm {{ is_null($selectedType) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                            Tous
                        </button>
                        <button wire:click="filterPresencesByType('entree')" class="px-3 py-1 text-sm {{ $selectedType === 'entree' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                            Entrées
                        </button>
                        <button wire:click="filterPresencesByType('sortie')" class="px-3 py-1 text-sm {{ $selectedType === 'sortie' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                            Sorties
                        </button>
                        <button wire:click="filterPresencesByType('pause_debut')" class="px-3 py-1 text-sm {{ $selectedType === 'pause_debut' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                            Début pause
                        </button>
                        <button wire:click="filterPresencesByType('pause_fin')" class="px-3 py-1 text-sm {{ $selectedType === 'pause_fin' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-full transition-colors">
                            Fin pause
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        @if($isAdvancedSearch)
                            <div class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-full flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Recherche avancée active
                            </div>
                        @endif
                        
                        <select wire:model="selectedSite" wire:change="filterPresencesBySite($event.target.value)" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Tous les sites</option>
                            @foreach($sitesForFilter as $site)
                                <option value="{{ $site->id }}">{{ $site->nom }}</option>
                            @endforeach
                        </select>
                        
                        <button wire:click="resetFilters" class="p-1.5 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                        
                        <button x-data="{}" x-on:click="$dispatch('open-modal', { id: 'search-modal' })" class="p-1.5 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10" style="display: none;">
                                <button wire:click="exportPresences('csv')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Exporter en CSV
                                    </div>
                                </button>
                                <button wire:click="exportPresences('xlsx')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Exporter en Excel
                                    </div>
                                </button>
                                <button wire:click="exportPresences('pdf')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M7 21h10a2 2 0 012 2v-2a2 2 0 012-2h-2M7 21H2v-2a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Exporter en PDF
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if(count($presences) === 0)
                    <div class="text-center py-4">
                        <p class="text-gray-500">Aucune présence trouvée</p>
                    </div>
                @else
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Affichage des 100 dernières présences</p>
                    </div>
                @endif
                
                <div class="flex justify-between items-center mb-2">
                    <p class="text-sm text-gray-500">{{ count($presences) }} résultat(s) trouvé(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($presences as $presence)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 relative">
                                                @if($presence->user && $presence->user->photo)
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $presence->user->photo) }}" alt="{{ $presence->user->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold">
                                                        {{ $presence->user ? substr($presence->user->name, 0, 1) : '?' }}
                                                    </div>
                                                @endif
                                                
                                                <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full 
                                                    @if($presence->type == 'entree') bg-green-500
                                                    @elseif($presence->type == 'sortie') bg-red-500
                                                    @elseif($presence->type == 'pause_debut') bg-yellow-500
                                                    @elseif($presence->type == 'pause_fin') bg-blue-500
                                                    @endif
                                                    border-2 border-white">
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $presence->user->name ?? 'Utilisateur inconnu' }}</div>
                                                <div class="text-xs text-gray-500">{{ $presence->user->email ?? 'Email inconnu' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($presence->type == 'entree') bg-green-100 text-green-800
                                            @elseif($presence->type == 'sortie') bg-red-100 text-red-800
                                            @elseif($presence->type == 'pause_debut') bg-yellow-100 text-yellow-800
                                            @elseif($presence->type == 'pause_fin') bg-blue-100 text-blue-800
                                            @endif">
                                            @if($presence->type == 'entree') 
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Entrée
                                            @elseif($presence->type == 'sortie')
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Sortie
                                            @elseif($presence->type == 'pause_debut')
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Début pause
                                            @elseif($presence->type == 'pause_fin')
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.752 11.168l-3.197-2.132A1 1 0 0112 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 011.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Fin pause
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $presence->site->nom ?? 'Site inconnu' }}</div>
                                        <div class="text-xs text-gray-500">{{ $presence->site->adresse ?? 'Adresse inconnue' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $presence->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $presence->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            @if($presence->statut == 'validé') 
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Validé
                                            @elseif($presence->statut == 'rejeté') 
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Rejeté
                                            @else 
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                En attente
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($presence->statut == 'en attente')
                                                <button wire:click="validatePresence({{ $presence->id }})" class="text-green-600 hover:text-green-900 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <button wire:click="rejectPresence({{ $presence->id }})" class="text-red-600 hover:text-red-900 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                            <a href="{{ route('filament.resources.presences.view', $presence->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Modal de recherche avancée (vue standard) -->
        <x-filament::modal id="search-modal" width="md">
            <x-slot name="heading">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Recherche avancée
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div>
                    <label for="search-employee" class="block text-sm font-medium text-gray-700 mb-1">Employé</label>
                    <input type="text" id="search-employee" wire:model.defer="searchEmployee" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Nom ou email de l'employé">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="search-date-from" class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" id="search-date-from" wire:model.defer="searchDateFrom" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    
                    <div>
                        <label for="search-date-to" class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" id="search-date-to" wire:model.defer="searchDateTo" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="search-status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select id="search-status" wire:model.defer="searchStatus" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="valide">Validé</option>
                            <option value="rejete">Rejeté</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search-method" class="block text-sm font-medium text-gray-700 mb-1">Méthode de pointage</label>
                        <select id="search-method" wire:model.defer="searchMethod" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Toutes les méthodes</option>
                            <option value="qrcode">QR Code</option>
                            <option value="nfc">NFC</option>
                            <option value="photo">Photo</option>
                            <option value="signature">Signature</option>
                            <option value="biometrie">Biométrie</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Distance du site</label>
                    <div class="flex items-center space-x-2">
                        <input type="range" wire:model.defer="searchDistance" min="0" max="1000" step="50" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                        <span class="text-sm text-gray-500">{{ $searchDistance ?? 0 }} m</span>
                    </div>
                </div>
            </div>
            
            <x-slot name="footer">
                <div class="flex justify-end space-x-2">
                    <x-filament::button color="secondary" x-on:click="$dispatch('close-modal', { id: 'search-modal' })">
                        Annuler
                    </x-filament::button>
                    
                    <x-filament::button wire:click="applyAdvancedSearch" x-on:click="$dispatch('close-modal', { id: 'search-modal' })">
                        Rechercher
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
        @else
        <!-- Vue hiérarchique -->
        <div class="bg-white rounded-lg shadow-md p-4 overflow-hidden">
            <div id="hierarchical-graph" class="w-full" style="height: 700px;"></div>
        </div>
        
        <!-- Légende -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Légende</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                    <span class="text-sm">Entreprise</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                    <span class="text-sm">Site</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                    <span class="text-sm">Département</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-gray-500 mr-2"></div>
                    <span class="text-sm">Employé</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-green-300 mr-2"></div>
                    <span class="text-sm">Présent</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-yellow-300 mr-2"></div>
                    <span class="text-sm">En pause</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-red-300 mr-2"></div>
                    <span class="text-sm">Absent</span>
                </div>
            </div>
        </div>
        @endif
        <!-- JavaScript pour les animations et le polling -->
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            
            @if($viewMode === 'hierarchical')
            <!-- Script D3.js pour la visualisation hiérarchique -->
            <script src="https://d3js.org/d3.v7.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Récupération des données hiérarchiques depuis le contrôleur
                    const hierarchicalData = @json($hierarchicalData);
                    
                    // Configuration du graphe
                    const width = document.getElementById('hierarchical-graph').offsetWidth;
                    const height = 700;
                    const margin = {top: 20, right: 120, bottom: 20, left: 120};
                    
                    // Création du SVG
                    const svg = d3.select("#hierarchical-graph")
                        .append("svg")
                        .attr("width", width)
                        .attr("height", height)
                        .append("g")
                        .attr("transform", `translate(${margin.left},${margin.top})`);
                    
                    // Création de la hiérarchie
                    const root = d3.hierarchy(hierarchicalData);
                    
                    // Configuration de l'arbre
                    const treeLayout = d3.tree()
                        .size([height - margin.top - margin.bottom, width - margin.left - margin.right]);
                    
                    // Calcul des positions des nœuds
                    treeLayout(root);
                    
                    // Couleurs selon le type de nœud
                    const nodeColors = {
                        'entreprise': '#3b82f6', // blue-500
                        'site': '#10b981', // green-500
                        'departement': '#8b5cf6', // purple-500
                        'employee': '#6b7280' // gray-500
                    };
                    
                    // Couleurs selon le statut de l'employé
                    const statusColors = {
                        'present': '#86efac', // green-300
                        'en_pause': '#fcd34d', // yellow-300
                        'absent': '#fca5a5', // red-300
                        'unknown': '#d1d5db' // gray-300
                    };
                    
                    // Création des liens entre les nœuds
                    svg.selectAll(".link")
                        .data(root.links())
                        .join("path")
                        .attr("class", "link")
                        .attr("d", d3.linkHorizontal()
                            .x(d => d.y)
                            .y(d => d.x)
                        )
                        .attr("fill", "none")
                        .attr("stroke", "#d1d5db")
                        .attr("stroke-width", 1.5);
                    
                    // Création des nœuds
                    const nodes = svg.selectAll(".node")
                        .data(root.descendants())
                        .join("g")
                        .attr("class", "node")
                        .attr("transform", d => `translate(${d.y},${d.x})`)
                        .attr("cursor", "pointer")
                        .on("click", function(event, d) {
                            // Toggle des enfants lors du clic
                            if (d.children) {
                                d._children = d.children;
                                d.children = null;
                            } else {
                                d.children = d._children;
                                d._children = null;
                            }
                            
                            // Mise à jour du graphe
                            update(root);
                        });
                    
                    // Ajout des cercles pour les nœuds
                    nodes.append("circle")
                        .attr("r", d => {
                            if (d.data.type === 'employee') return 8;
                            return 12;
                        })
                        .attr("fill", d => {
                            if (d.data.type === 'employee') {
                                return statusColors[d.data.status] || statusColors.unknown;
                            }
                            return nodeColors[d.data.type] || "#6b7280";
                        })
                        .attr("stroke", "#fff")
                        .attr("stroke-width", 2);
                    
                    // Ajout des labels
                    nodes.append("text")
                        .attr("dy", d => d.data.type === 'employee' ? -12 : 4)
                        .attr("x", d => d.children || d._children ? -13 : 13)
                        .attr("text-anchor", d => d.children || d._children ? "end" : "start")
                        .text(d => {
                            if (d.data.type === 'departement') {
                                return `${d.data.name} (${d.data.count})`;
                            }
                            return d.data.name;
                        })
                        .attr("font-size", d => {
                            if (d.data.type === 'employee') return "10px";
                            if (d.data.type === 'departement') return "12px";
                            return "14px";
                        })
                        .attr("fill", d => {
                            if (d.data.type === 'employee') return "#4b5563";
                            return "#1f2937";
                        });
                    
                    // Ajout de l'heure d'arrivée pour les employés
                    nodes.filter(d => d.data.type === 'employee')
                        .append("text")
                        .attr("dy", 0)
                        .attr("x", 13)
                        .attr("text-anchor", "start")
                        .text(d => d.data.time || "")
                        .attr("font-size", "8px")
                        .attr("fill", "#6b7280");
                    
                    // Fonction pour mettre à jour le graphe
                    function update(source) {
                        // Recalcul des positions
                        treeLayout(root);
                        
                        // Mise à jour des liens
                        const links = svg.selectAll(".link")
                            .data(root.links());
                        
                        links.transition()
                            .duration(750)
                            .attr("d", d3.linkHorizontal()
                                .x(d => d.y)
                                .y(d => d.x)
                            );
                        
                        links.enter()
                            .append("path")
                            .attr("class", "link")
                            .attr("d", d3.linkHorizontal()
                                .x(d => d.y)
                                .y(d => d.x)
                            )
                            .attr("fill", "none")
                            .attr("stroke", "#d1d5db")
                            .attr("stroke-width", 1.5);
                        
                        links.exit().remove();
                        
                        // Mise à jour des nœuds
                        const node = svg.selectAll(".node")
                            .data(root.descendants(), d => d.id || (d.id = ++i));
                        
                        node.transition()
                            .duration(750)
                            .attr("transform", d => `translate(${d.y},${d.x})`);
                        
                        node.exit().remove();
                    }
                    
                    // Polling pour rafraîchir les données
                    setInterval(() => {
                        Livewire.dispatch('refresh-hierarchical-data');
                    }, 30000); // Rafraîchir toutes les 30 secondes
                });
            </script>
            @endif
{{ ... }}
