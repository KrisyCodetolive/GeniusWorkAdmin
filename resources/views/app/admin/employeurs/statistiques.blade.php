@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Statistiques des employeurs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Statistiques des employeurs</h1>
        <a href="{{ route('admin.employeurs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Filtres</h2>
        </div>
        <div class="p-4">
            <form action="{{ route('admin.employeurs.statistiques') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="entreprise_id" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                    <select id="entreprise_id" name="entreprise_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Toutes les entreprises</option>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}" {{ isset($filters['entreprise_id']) && $filters['entreprise_id'] == $entreprise->id ? 'selected' : '' }}>
                                {{ $entreprise->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                    <select id="periode" name="periode" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="annee" {{ isset($filters['periode']) && $filters['periode'] == 'annee' ? 'selected' : '' }}>Année en cours</option>
                        <option value="trimestre" {{ isset($filters['periode']) && $filters['periode'] == 'trimestre' ? 'selected' : '' }}>Trimestre en cours</option>
                        <option value="mois" {{ isset($filters['periode']) && $filters['periode'] == 'mois' ? 'selected' : '' }}>Mois en cours</option>
                        <option value="tout" {{ isset($filters['periode']) && $filters['periode'] == 'tout' ? 'selected' : '' }}>Toutes les données</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                        <i class="fas fa-filter mr-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Statistiques générales -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Statistiques générales</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Total employeurs</p>
                                <p class="text-lg font-semibold">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Employeurs actifs</p>
                                <p class="text-lg font-semibold">{{ $stats['actifs'] }} ({{ $stats['pourcentage_actifs'] }}%)</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Ancienneté moyenne</p>
                                <p class="text-lg font-semibold">{{ $stats['anciennete_moyenne'] }} ans</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-user-plus text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500">Nouveaux (< 1 an)</p>
                                <p class="text-lg font-semibold">{{ $stats['nouveaux'] }} ({{ $stats['pourcentage_nouveaux'] }}%)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Répartition par type de contrat</h3>
                    <div class="h-64">
                        <canvas id="contratChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Évolution des effectifs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Évolution des effectifs</h2>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Répartition par département -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Répartition par département</h2>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="departementChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Pyramide des âges -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Pyramide des âges</h2>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tableau des statistiques détaillées -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Statistiques détaillées par département</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Département
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre d'employeurs
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actifs
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ancienneté moyenne
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Âge moyen
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Taux de rotation
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stats['par_departement'] as $dep)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $dep['nom'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dep['total'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dep['actifs'] }} ({{ $dep['pourcentage_actifs'] }}%)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dep['anciennete_moyenne'] }} ans</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dep['age_moyen'] }} ans</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dep['taux_rotation'] }}%</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des types de contrat
        const contratCtx = document.getElementById('contratChart').getContext('2d');
        new Chart(contratCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($stats['par_contrat'])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['par_contrat'])) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
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
                    }
                }
            }
        });
        
        // Graphique d'évolution des effectifs
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['evolution']['labels']) !!},
                datasets: [{
                    label: 'Nombre d\'employeurs',
                    data: {!! json_encode($stats['evolution']['data']) !!},
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
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
        
        // Graphique de répartition par département
        const departementCtx = document.getElementById('departementChart').getContext('2d');
        new Chart(departementCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($stats['par_departement'], 'nom')) !!},
                datasets: [{
                    label: 'Nombre d\'employeurs',
                    data: {!! json_encode(array_column($stats['par_departement'], 'total')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
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
        
        // Graphique de la pyramide des âges
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($stats['par_age'])) !!},
                datasets: [{
                    label: 'Nombre d\'employeurs',
                    data: {!! json_encode(array_values($stats['par_age'])) !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
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
