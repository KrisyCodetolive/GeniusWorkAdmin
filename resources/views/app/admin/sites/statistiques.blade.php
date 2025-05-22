@extends('layouts.admin')

@section('title', 'Statistiques des sites')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Statistiques des sites</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.sites.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Filtrer les statistiques</h2>
        </div>
        <div class="p-4">
            <form action="{{ route('admin.sites.statistiques') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-auto">
                    <label for="entreprise_id" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                    <select id="entreprise_id" name="entreprise_id" class="w-full md:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Toutes les entreprises</option>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}" {{ $entrepriseId == $entreprise->id ? 'selected' : '' }}>
                                {{ $entreprise->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-filter mr-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Carte Total des sites -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-blue-50 border-b border-blue-100">
                <h3 class="text-lg font-semibold text-blue-800">Total des sites</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">{{ $statistiques['total'] }}</div>
                <div class="text-sm text-gray-500">sites enregistrés</div>
            </div>
        </div>

        <!-- Carte Sites actifs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-green-50 border-b border-green-100">
                <h3 class="text-lg font-semibold text-green-800">Sites actifs</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center">
                <div class="text-4xl font-bold text-green-600 mb-2">{{ $statistiques['actifs'] }}</div>
                <div class="text-sm text-gray-500">
                    {{ $statistiques['pourcentage_actifs'] }}% du total
                </div>
            </div>
        </div>

        <!-- Carte Sites inactifs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-red-50 border-b border-red-100">
                <h3 class="text-lg font-semibold text-red-800">Sites inactifs</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center">
                <div class="text-4xl font-bold text-red-600 mb-2">{{ $statistiques['inactifs'] }}</div>
                <div class="text-sm text-gray-500">
                    {{ 100 - $statistiques['pourcentage_actifs'] }}% du total
                </div>
            </div>
        </div>

        <!-- Carte Sites avec geofencing -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-purple-50 border-b border-purple-100">
                <h3 class="text-lg font-semibold text-purple-800">Sites avec geofencing</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center">
                <div class="text-4xl font-bold text-purple-600 mb-2">{{ $statistiques['avec_geofencing'] }}</div>
                <div class="text-sm text-gray-500">
                    {{ $statistiques['pourcentage_geofencing'] }}% du total
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Graphique répartition des statuts -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Répartition des statuts</h2>
            </div>
            <div class="p-4">
                <canvas id="statusChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Graphique utilisation du geofencing -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Utilisation du geofencing</h2>
            </div>
            <div class="p-4">
                <canvas id="geofencingChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    @if($sites->count() > 0)
    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Répartition des sites par ville</h2>
        </div>
        <div class="p-4">
            <canvas id="cityChart" class="w-full h-80"></canvas>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique de répartition des statuts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Sites actifs', 'Sites inactifs'],
                datasets: [{
                    data: [{{ $statistiques['actifs'] }}, {{ $statistiques['inactifs'] }}],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(239, 68, 68, 0.7)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Graphique d'utilisation du geofencing
        const geofencingCtx = document.getElementById('geofencingChart').getContext('2d');
        new Chart(geofencingCtx, {
            type: 'pie',
            data: {
                labels: ['Avec geofencing', 'Sans geofencing'],
                datasets: [{
                    data: [
                        {{ $statistiques['avec_geofencing'] }}, 
                        {{ $statistiques['total'] - $statistiques['avec_geofencing'] }}
                    ],
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.7)',
                        'rgba(209, 213, 219, 0.7)'
                    ],
                    borderColor: [
                        'rgba(124, 58, 237, 1)',
                        'rgba(209, 213, 219, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        @if($sites->count() > 0)
        // Préparation des données pour le graphique par ville
        const cityCounts = {};
        @foreach($sites as $site)
            if (!cityCounts['{{ $site->ville }}']) {
                cityCounts['{{ $site->ville }}'] = 0;
            }
            cityCounts['{{ $site->ville }}']++;
        @endforeach

        // Trier les villes par nombre de sites (décroissant)
        const sortedCities = Object.keys(cityCounts).sort((a, b) => cityCounts[b] - cityCounts[a]);
        
        // Limiter à 10 villes maximum pour la lisibilité
        const topCities = sortedCities.slice(0, 10);
        const topCityCounts = topCities.map(city => cityCounts[city]);

        // Graphique de répartition par ville
        const cityCtx = document.getElementById('cityChart').getContext('2d');
        new Chart(cityCtx, {
            type: 'bar',
            data: {
                labels: topCities,
                datasets: [{
                    label: 'Nombre de sites',
                    data: topCityCounts,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre de sites'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Villes'
                        }
                    }
                }
            }
        });
        @endif
    });
</script>
@endpush
@endsection
