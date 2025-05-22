<x-filament::page>
    {{ $this->form }}

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-lg font-medium text-primary-600">Total des demandes</h3>
            <p class="text-3xl font-bold mt-2">{{ $totalConges }}</p>
            <p class="text-sm text-gray-500 mt-1">Toutes les demandes de congés</p>
        </div>
        
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-lg font-medium text-warning-600">En attente</h3>
            <p class="text-3xl font-bold mt-2">{{ $congesEnAttente }}</p>
            <p class="text-sm text-gray-500 mt-1">Demandes nécessitant validation</p>
        </div>
        
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-lg font-medium text-success-600">Approuvés</h3>
            <p class="text-3xl font-bold mt-2">{{ $congesApprouves }}</p>
            <p class="text-sm text-gray-500 mt-1">Demandes validées</p>
        </div>
        
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="text-lg font-medium text-danger-600">Rejetés</h3>
            <p class="text-3xl font-bold mt-2">{{ $congesRejetes }}</p>
            <p class="text-sm text-gray-500 mt-1">Demandes refusées</p>
        </div>
    </div>

    <x-filament::section>
        <x-slot name="heading">Indicateurs clés</x-slot>
        
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="text-lg font-medium">Taux d'approbation</h3>
                <p class="text-3xl font-bold mt-2 text-success-600">{{ $tauxApprobation }}%</p>
                <p class="text-sm text-gray-500 mt-1">Des demandes sont approuvées</p>
            </div>
            
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="text-lg font-medium">Moyenne par employé</h3>
                <p class="text-3xl font-bold mt-2 text-primary-600">{{ number_format($moyenneJoursParEmploye, 1) }} jours</p>
                <p class="text-sm text-gray-500 mt-1">De congés pris par employé</p>
            </div>
        </div>
    </x-filament::section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-filament::section>
            <x-slot name="heading">Répartition par type de congé</x-slot>
            
            <div class="h-80" id="conges-by-type-chart"></div>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">Évolution des demandes</x-slot>
            
            <div class="h-80" id="conges-trend-chart"></div>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Top 10 des employés par jours de congés</x-slot>
        
        <div class="h-80" id="top-employes-chart"></div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Dernières demandes de congés</x-slot>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Employé</th>
                        <th scope="col" class="px-6 py-3">Type de congé</th>
                        <th scope="col" class="px-6 py-3">Date début</th>
                        <th scope="col" class="px-6 py-3">Date fin</th>
                        <th scope="col" class="px-6 py-3">Durée</th>
                        <th scope="col" class="px-6 py-3">Statut</th>
                        <th scope="col" class="px-6 py-3">Validé par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dernieresDemandesConges as $conge)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">{{ $conge->employeur->nom  ?? 'N/A' }} {{ $conge->employeur->prenom ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $conge->typeConge->nom ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $conge->date_debut ? \Carbon\Carbon::parse($conge->date_debut)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $conge->date_fin ? \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $conge->duree_jours }} jours</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'en_attente' => 'bg-yellow-100 text-yellow-800',
                                        'approuve' => 'bg-green-100 text-green-800',
                                        'rejete' => 'bg-red-100 text-red-800',
                                        'annule' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusLabels = [
                                        'en_attente' => 'En attente',
                                        'approuve' => 'Approuvé',
                                        'rejete' => 'Rejeté',
                                        'annule' => 'Annulé',
                                    ];
                                    $statusColor = $statusColors[$conge->statut] ?? 'bg-gray-100 text-gray-800';
                                    $statusLabel = $statusLabels[$conge->statut] ?? 'Inconnu';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $conge->validateur->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Graphique de répartition par type de congé
                const congesByTypeData = @json($congesByType);
                
                if (congesByTypeData.length > 0) {
                    const congesByTypeOptions = {
                        series: congesByTypeData.map(item => item.value),
                        chart: {
                            type: 'donut',
                            height: 320,
                        },
                        labels: congesByTypeData.map(item => item.label),
                        colors: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        legend: {
                            position: 'bottom',
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 200
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }]
                    };

                    const congesByTypeChart = new ApexCharts(document.querySelector("#conges-by-type-chart"), congesByTypeOptions);
                    congesByTypeChart.render();
                } else {
                    document.querySelector("#conges-by-type-chart").innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Aucune donnée disponible</p></div>';
                }
                
                // Graphique d'évolution des demandes
                const congesTrendData = @json($congesTrend);
                
                if (congesTrendData.length > 0) {
                    const congesTrendOptions = {
                        series: [{
                            name: 'Demandes',
                            data: congesTrendData.map(item => item.count)
                        }],
                        chart: {
                            height: 320,
                            type: 'area',
                            toolbar: {
                                show: false,
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth'
                        },
                        xaxis: {
                            categories: congesTrendData.map(item => item.month),
                        },
                        tooltip: {
                            x: {
                                format: 'MM yyyy'
                            },
                        },
                        colors: ['#3b82f6'],
                    };

                    const congesTrendChart = new ApexCharts(document.querySelector("#conges-trend-chart"), congesTrendOptions);
                    congesTrendChart.render();
                } else {
                    document.querySelector("#conges-trend-chart").innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Aucune donnée disponible</p></div>';
                }
                
                // Graphique des top employés
                const topEmployesData = @json($topEmployes);
                
                if (topEmployesData.length > 0) {
                    const topEmployesOptions = {
                        series: [{
                            name: 'Jours de congés',
                            data: topEmployesData.map(item => item.total_jours)
                        }],
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false,
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                dataLabels: {
                                    position: 'top',
                                },
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            offsetX: -6,
                            style: {
                                fontSize: '12px',
                                colors: ['#fff']
                            }
                        },
                        stroke: {
                            show: true,
                            width: 1,
                            colors: ['#fff']
                        },
                        xaxis: {
                            categories: topEmployesData.map(item => item.employeur ? item.employeur.name : 'Inconnu'),
                        },
                        colors: ['#10b981'],
                    };

                    const topEmployesChart = new ApexCharts(document.querySelector("#top-employes-chart"), topEmployesOptions);
                    topEmployesChart.render();
                } else {
                    document.querySelector("#top-employes-chart").innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Aucune donnée disponible</p></div>';
                }
            });
        </script>
    @endpush
</x-filament::page>
