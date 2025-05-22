<x-filament-panels::page>
    <div class="space-y-8">
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 mr-2 text-primary-500" />
                    <span>Filtres</span>
                </div>
            </x-slot>
            
            <form wire:submit="submitForm">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                    {{ $this->form }}
                    
                    <div class="flex justify-end mt-6">
                        <x-filament::button type="submit" color="primary" class="px-6">
                            <x-heroicon-o-funnel class="w-4 h-4 mr-2" />
                            Appliquer les filtres
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-chart-pie class="w-5 h-5 mr-2 text-primary-500" />
                    <span>Statistiques générales</span>
                </div>
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Colonne 1: Total des visites -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                    <div class="p-4 flex flex-col items-center justify-center h-full">
                        <div class="inline-flex items-center justify-center p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full mb-2">
                            <x-heroicon-o-document-chart-bar class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Total des visites</h3>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
                
                <!-- Colonne 2: En cours -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                    <div class="p-4 flex flex-col items-center justify-center h-full">
                        <div class="inline-flex items-center justify-center p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full mb-2">
                            <x-heroicon-o-clock class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">En cours</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['en_cours'] ?? 0 }}</p>
                    </div>
                </div>
                
                <!-- Colonne 3: Terminées -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                    <div class="p-4 flex flex-col items-center justify-center h-full">
                        <div class="inline-flex items-center justify-center p-3 bg-green-100 dark:bg-green-900/50 rounded-full mb-2">
                            <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Terminées</h3>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['terminees'] ?? 0 }}</p>
                    </div>
                </div>
                
                <!-- Colonne 4: Annulées -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                    <div class="p-4 flex flex-col items-center justify-center h-full">
                        <div class="inline-flex items-center justify-center p-3 bg-red-100 dark:bg-red-900/50 rounded-full mb-2">
                            <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Annulées</h3>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['annulees'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </x-filament::section>
        
        @if($showChart)
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-primary-500" />
                    <span>Évolution des visites</span>
                </div>
            </x-slot>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="w-full h-80">
                    <canvas id="visitesChart"></canvas>
                </div>
                
                <!-- Données du graphique stockées pour être utilisées par chart-loader.js -->
                <div id="chartData" class="hidden">{{ json_encode($chartData) }}</div>
                <div id="chartTheme" class="hidden">{{ json_encode(['isDarkMode' => config('filament.dark_mode')]) }}</div>
                
                <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
                </div>
            </div>
        </x-filament::section>
        @endif
        
        @if(!empty($stats['par_site']))
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-building-office class="w-5 h-5 mr-2 text-primary-500" />
                    <span>Répartition par site</span>
                </div>
            </x-slot>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Site</th>
                                <th class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre de visites</th>
                                <th class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pourcentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($stats['par_site'] as $siteStat)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
                                            <span class="text-primary-700 dark:text-primary-400 font-medium">{{ substr($siteStat['site_nom'], 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $siteStat['site_nom'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">{{ $siteStat['total'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format(($siteStat['total'] / $stats['total']) * 100, 1) }}%</span>
                                        <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 mt-2 overflow-hidden">
                                            <div class="bg-primary-600 dark:bg-primary-500 h-2.5 rounded-full" style="width: {{ ($siteStat['total'] / $stats['total']) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
        @endif
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</x-filament-panels::page>
