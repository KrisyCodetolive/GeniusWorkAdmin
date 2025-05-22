<div>
    <!-- Cartes de statistiques principales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total heures supp.</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['total_heures_supp'], 1) }} h</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Employés concernés</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['employes_avec_heures_supp']) }}</div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Moyenne par employé</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($stats['moyenne_heures_supp'], 1) }} h</div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Jour le plus chargé</dt>
                            <dd>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">
                                    @php
                                        $maxJour = collect($stats['heures_supp_par_jour_semaine'])->sortDesc()->keys()->first();
                                        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                                    @endphp
                                    {{ $jours[$maxJour] ?? 'N/A' }}
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
        <!-- Graphique des heures supplémentaires par jour -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Évolution des heures supplémentaires</h3>
                <div class="h-80">
                    <canvas id="heuresSuppParJourChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique des heures supplémentaires par jour de la semaine -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Répartition par jour de la semaine</h3>
                <div class="h-80">
                    <canvas id="heuresSuppParJourSemaineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top employés -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg mb-6">
        <div class="p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top 5 des employés avec le plus d'heures supplémentaires</h3>
            <div class="h-80">
                <canvas id="topEmployesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableau des employeurs -->
    @if(count($stats['heures_supp_par_employeur']) > 0)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Répartition par employeur</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employeur</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Heures supplémentaires</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre d'occurrences</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pourcentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($stats['heures_supp_par_employeur'] as $employeur)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $employeur['nom'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($employeur['heures'], 1) }} h</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ number_format($employeur['count']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                {{ number_format(($employeur['heures'] / $stats['total_heures_supp']) * 100, 1) }}%
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
            // Graphique des heures supplémentaires par jour
            const heuresSuppParJourCtx = document.getElementById('heuresSuppParJourChart').getContext('2d');
            new Chart(heuresSuppParJourCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['heuresSuppParJour']['labels']),
                    datasets: [{
                        label: 'Heures supplémentaires',
                        data: @json($chartData['heuresSuppParJour']['data']),
                        backgroundColor: 'rgba(99, 102, 241, 0.2)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(99, 102, 241, 1)',
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

            // Graphique des heures supplémentaires par jour de la semaine
            const heuresSuppParJourSemaineCtx = document.getElementById('heuresSuppParJourSemaineChart').getContext('2d');
            new Chart(heuresSuppParJourSemaineCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['heuresSuppParJourSemaine']['labels']),
                    datasets: [{
                        label: 'Heures supplémentaires',
                        data: @json($chartData['heuresSuppParJourSemaine']['data']),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)',  // Lundi
                            'rgba(59, 130, 246, 0.6)',  // Mardi
                            'rgba(139, 92, 246, 0.6)',  // Mercredi
                            'rgba(245, 158, 11, 0.6)',  // Jeudi
                            'rgba(239, 68, 68, 0.6)',   // Vendredi
                            'rgba(99, 102, 241, 0.6)',  // Samedi
                            'rgba(107, 114, 128, 0.6)', // Dimanche
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(139, 92, 246, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)',
                            'rgba(99, 102, 241, 1)',
                            'rgba(107, 114, 128, 1)',
                        ],
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

            // Graphique des top employés
            const topEmployesCtx = document.getElementById('topEmployesChart').getContext('2d');
            new Chart(topEmployesCtx, {
                type: 'horizontalBar',
                data: {
                    labels: @json($chartData['topEmployes']['labels']),
                    datasets: [{
                        label: 'Heures supplémentaires',
                        data: @json($chartData['topEmployes']['data']),
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barThickness: 20,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            }
                        },
                        y: {
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
                                label: function(context) {
                                    return context.parsed.x + ' heures';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
