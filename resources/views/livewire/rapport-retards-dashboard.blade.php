<div>
    <!-- Cartes de statistiques principales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total des retards</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['total_retards']) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total des absences</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['total_absences']) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Durée moyenne des retards</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['duree_moyenne_retard'], 1) }} min</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Retards > 30 min</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ number_format(($stats['retards_par_duree']['30_60min'] ?? 0) + ($stats['retards_par_duree']['plus_60min'] ?? 0)) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <!-- Graphique des retards par jour -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Évolution des retards</h3>
                <div class="h-80">
                    <canvas id="retardsParJourChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique des retards par durée -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Répartition par durée de retard</h3>
                <div class="h-80">
                    <canvas id="retardsParDureeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des retards par plage horaire -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg mb-6">
        <div class="p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Répartition des retards par plage horaire</h3>
            <div class="h-80">
                <canvas id="retardsParPlageChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableau des employeurs -->
    @if(count($stats['retards_par_employeur']) > 0)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Répartition des retards par employeur</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employeur</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre de retards</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pourcentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($stats['retards_par_employeur'] as $employeur)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $employeur['nom'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($employeur['count']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                {{ number_format(($employeur['count'] / $stats['total_retards']) * 100, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Scripts pour les graphiques -->
    <script>
        document.addEventListener('livewire:load', function () {
            // Graphique des retards par jour
            const retardsParJourCtx = document.getElementById('retardsParJourChart').getContext('2d');
            new Chart(retardsParJourCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['retardsParJour']['labels']),
                    datasets: [{
                        label: 'Nombre de retards',
                        data: @json($chartData['retardsParJour']['data']),
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: 'rgba(243, 244, 246, 1)',
                            bodyColor: 'rgba(243, 244, 246, 1)',
                            borderColor: 'rgba(107, 114, 128, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return 'Date: ' + tooltipItems[0].label;
                                }
                            }
                        }
                    }
                }
            });

            // Graphique des retards par durée
            const retardsParDureeCtx = document.getElementById('retardsParDureeChart').getContext('2d');
            new Chart(retardsParDureeCtx, {
                type: 'pie',
                data: {
                    labels: @json($chartData['retardsParDuree']['labels']),
                    datasets: [{
                        data: @json($chartData['retardsParDuree']['data']),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',  // < 15min
                            'rgba(245, 158, 11, 0.8)',  // 15-30min
                            'rgba(239, 68, 68, 0.8)',   // 30-60min
                            'rgba(220, 38, 38, 0.8)',   // > 60min
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(220, 38, 38, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                boxWidth: 12,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: 'rgba(243, 244, 246, 1)',
                            bodyColor: 'rgba(243, 244, 246, 1)',
                            borderColor: 'rgba(107, 114, 128, 0.5)',
                            borderWidth: 1,
                            padding: 12
                        }
                    }
                }
            });

            // Graphique des retards par plage horaire
            const retardsParPlageCtx = document.getElementById('retardsParPlageChart').getContext('2d');
            new Chart(retardsParPlageCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['retardsParPlage']['labels']),
                    datasets: [{
                        label: 'Nombre de retards',
                        data: @json($chartData['retardsParPlage']['data']),
                        backgroundColor: 'rgba(139, 92, 246, 0.6)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barThickness: 20,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: 'rgba(243, 244, 246, 1)',
                            bodyColor: 'rgba(243, 244, 246, 1)',
                            borderColor: 'rgba(107, 114, 128, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false
                        }
                    }
                }
            });
        });
    </script>
</div>
