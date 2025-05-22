@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- En-tête du rapport -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Rapport de présences</h1>
                        <p class="text-gray-600">
                            {{ $entreprise->nom }} 
                            @if($site)
                                - Site: {{ $site->nom }}
                            @else
                                - Tous les sites
                            @endif
                        </p>
                        <p class="text-gray-600">
                            Période: {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="/admin/presences" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Retour
                        </a>
                        <a href="{{ route('rapports.presences.pdf', ['dateDebut' => $dateDebut->format('Y-m-d'), 'dateFin' => $dateFin->format('Y-m-d'), 'site_id' => $site ? $site->id : null]) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                        <h3 class="text-sm font-medium text-blue-800">Employés présents</h3>
                        <div class="mt-1 flex items-baseline justify-between">
                            <p class="text-2xl font-semibold text-blue-900">{{ $stats['employes_presents'] }}</p>
                            <p class="text-sm font-medium text-blue-800">sur {{ $stats['total_employes'] }}</p>
                        </div>
                        <p class="text-sm text-blue-700 mt-1">Taux de présence: {{ $stats['taux_presence'] }}%</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-green-800">Heures travaillées</h3>
                        <p class="mt-1 text-2xl font-semibold text-green-900">{{ $stats['heures_travaillees'] }} h</p>
                        <p class="text-sm text-green-700 mt-1">Total sur la période</p>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-yellow-800">Minutes de retard</h3>
                        <p class="mt-1 text-2xl font-semibold text-yellow-900">{{ $stats['minutes_retard'] }}</p>
                        <p class="text-sm text-yellow-700 mt-1">Total sur la période</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-purple-800">Présences validées</h3>
                        <p class="mt-1 text-2xl font-semibold text-purple-900">
                            {{ isset($stats['validation']['approuve']) ? $stats['validation']['approuve'] : 0 }}
                        </p>
                        <p class="text-sm text-purple-700 mt-1">
                            {{ $presences->count() > 0 ? round((isset($stats['validation']['approuve']) ? $stats['validation']['approuve'] : 0) / $presences->count() * 100, 1) : 0 }}% du total
                        </p>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Présences par statut</h3>
                        <div class="h-64">
                            <canvas id="statutsChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Présences par site</h3>
                        <div class="h-64">
                            <canvas id="sitesChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Présences par jour</h3>
                        <div class="h-64">
                            <canvas id="joursChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Heures d'arrivée</h3>
                        <div class="h-64">
                            <canvas id="heuresChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des présences -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Liste des présences</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date entrée</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date sortie</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durée (min)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validation</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($presences as $presence)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $presence->employeur ? $presence->employeur->prenom . ' ' . $presence->employeur->nom : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $presence->site ? $presence->site->nom : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $presence->date_heure_entree ? $presence->date_heure_entree->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $presence->date_heure_sortie ? $presence->date_heure_sortie->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $presence->duree_effective ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($presence->statut == 'present') bg-green-100 text-green-800 
                                        @elseif($presence->statut == 'absent') bg-red-100 text-red-800 
                                        @elseif($presence->statut == 'retard') bg-yellow-100 text-yellow-800 
                                        @elseif($presence->statut == 'sortie') bg-blue-100 text-blue-800 
                                        @elseif($presence->statut == 'conge') bg-gray-100 text-gray-800 
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($presence->statut == 'present') Présent 
                                        @elseif($presence->statut == 'absent') Absent 
                                        @elseif($presence->statut == 'retard') En retard 
                                        @elseif($presence->statut == 'sortie') Sorti 
                                        @elseif($presence->statut == 'conge') En congé 
                                        @else {{ $presence->statut }} @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($presence->statut_validation == 'approuve') bg-green-100 text-green-800 
                                        @elseif($presence->statut_validation == 'rejete') bg-red-100 text-red-800 
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($presence->statut_validation == 'approuve') Approuvé 
                                        @elseif($presence->statut_validation == 'rejete') Rejeté 
                                        @else En attente @endif
                                    </span>
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
        // Données pour les graphiques
        const statutsData = {
            labels: [
                @foreach($stats['statuts'] as $statut => $count)
                    '{{ $statut == "present" ? "Présent" : ($statut == "absent" ? "Absent" : ($statut == "retard" ? "En retard" : ($statut == "sortie" ? "Sorti" : ($statut == "conge" ? "En congé" : $statut)))) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($stats['statuts'] as $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#10B981', // Vert pour présent
                    '#EF4444', // Rouge pour absent
                    '#F59E0B', // Jaune pour retard
                    '#3B82F6', // Bleu pour sorti
                    '#6B7280', // Gris pour congé
                    '#8B5CF6'  // Violet pour autres
                ]
            }]
        };
        
        const sitesData = {
            labels: [
                @foreach($stats['par_site'] as $site => $count)
                    '{{ $site }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($stats['par_site'] as $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6',
                    '#EC4899',
                    '#6B7280'
                ]
            }]
        };
        
        const joursData = {
            labels: [
                @foreach($stats['presences_par_jour'] as $jour => $count)
                    '{{ $jour == "Monday" ? "Lundi" : ($jour == "Tuesday" ? "Mardi" : ($jour == "Wednesday" ? "Mercredi" : ($jour == "Thursday" ? "Jeudi" : ($jour == "Friday" ? "Vendredi" : ($jour == "Saturday" ? "Samedi" : "Dimanche"))))) }}',
                @endforeach
            ],
            datasets: [{
                label: 'Nombre de présences',
                data: [
                    @foreach($stats['presences_par_jour'] as $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        };
        
        const heuresData = {
            labels: [
                @foreach($stats['heures_arrivee'] as $heure => $count)
                    '{{ $heure }}h',
                @endforeach
            ],
            datasets: [{
                label: 'Nombre d\'arrivées',
                data: [
                    @foreach($stats['heures_arrivee'] as $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            }]
        };
        
        // Créer les graphiques
        new Chart(document.getElementById('statutsChart'), {
            type: 'pie',
            data: statutsData,
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
        
        new Chart(document.getElementById('joursChart'), {
            type: 'bar',
            data: joursData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        new Chart(document.getElementById('heuresChart'), {
            type: 'bar',
            data: heuresData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
