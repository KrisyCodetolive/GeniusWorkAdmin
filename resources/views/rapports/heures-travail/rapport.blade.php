@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête du rapport -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Rapport d'heures de travail</h1>
                        <p class="text-gray-600">
                            {{ $entreprise->nom }} 
                            @if($site)
                                - Site: {{ $site->nom }}
                            @else
                                - Tous les sites
                            @endif
                        </p>
                        <p class="text-gray-600">
                            Période: {{ $date_debut->format('d/m/Y') }} au {{ $date_fin->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="/admin/presences" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Retour
                        </a>
                        <a href="{{ route('rapports.heures-travail.pdf', ['date_debut' => $date_debut->format('Y-m-d'), 'date_fin' => $date_fin->format('Y-m-d'), 'site_id' => $site ? $site->id : null]) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                            </svg>
                            Exporter en PDF
                        </a>
                    </div>
                </div>
                
                <!-- Résumé des statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-blue-800">Heures travaillées</h3>
                        <p class="mt-1 text-2xl font-semibold text-blue-900">{{ $statistiques['temps_total_formate'] }}</p>
                        <p class="text-sm text-blue-700 mt-1">{{ $statistiques['total_employes'] }} employés</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-green-800">Moyenne par employé</h3>
                        <p class="mt-1 text-2xl font-semibold text-green-900">{{ $statistiques['temps_moyen_par_employe_formate'] }}</p>
                        <p class="text-sm text-green-700 mt-1">Sur la période</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-yellow-800">Heures supplémentaires</h3>
                        <p class="mt-1 text-2xl font-semibold text-yellow-900">{{ $statistiques['heures_supplementaires_total_formate'] }}</p>
                        <p class="text-sm text-yellow-700 mt-1">Total sur la période</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-purple-800">Jours travaillés</h3>
                        <p class="mt-1 text-2xl font-semibold text-purple-900">{{ $statistiques['jours_travailles_total'] }}</p>
                        <p class="text-sm text-purple-700 mt-1">
                            Moyenne: {{ $statistiques['jours_travailles_moyen_par_employe'] }} jours/employé
                        </p>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Heures par jour de la semaine</h3>
                        <div class="h-64">
                            <canvas id="joursChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Heures par site</h3>
                        <div class="h-64">
                            <canvas id="sitesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des heures de travail par employé -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Heures de travail par employé</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heures travaillées</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours travaillés</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moyenne par jour</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heures supp.</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retards</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($statistiques['employes'] as $donnees)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $donnees['employe']->prenom }} {{ $donnees['employe']->nom }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $donnees['temps_total_formate'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $donnees['jours_travailles'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $donnees['temps_moyen_par_jour_formate'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $donnees['heures_supplementaires_formate'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $donnees['retard_total_formate'] }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Données pour le graphique des jours de la semaine
        const joursData = {
            labels: [
                @foreach($statistiques['statistiques_par_jour_semaine'] as $jour => $stat)
                    '{{ $stat['jour_traduit'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Heures travaillées',
                data: [
                    @foreach($statistiques['statistiques_par_jour_semaine'] as $jour => $stat)
                        {{ $stat['total_minutes'] / 60 }}, // Convertir en heures
                    @endforeach
                ],
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        };
        
        // Données pour le graphique des sites
        const sitesData = {
            labels: [
                @foreach($statistiques['statistiques_par_site'] as $site)
                    '{{ $site['nom'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Heures travaillées',
                data: [
                    @foreach($statistiques['statistiques_par_site'] as $site)
                        {{ $site['total_minutes'] / 60 }}, // Convertir en heures
                    @endforeach
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(236, 72, 153, 0.7)',
                    'rgba(107, 114, 128, 0.7)'
                ]
            }]
        };
        
        // Créer les graphiques
        new Chart(document.getElementById('joursChart'), {
            type: 'bar',
            data: joursData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    }
                }
            }
        });
        
        new Chart(document.getElementById('sitesChart'), {
            type: 'pie',
            data: sitesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
