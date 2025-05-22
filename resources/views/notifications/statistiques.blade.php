@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Statistiques des notifications</h4>
                    <div>
                        <a href="{{ route('notifications.historique-entreprise') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-history"></i> Historique entreprise
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-bell"></i> Mes notifications
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres de date -->
                    <form action="{{ route('notifications.statistiques') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_debut">Date de début</label>
                                    <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ $dateDebut }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ $dateFin }}">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="{{ route('notifications.statistiques') }}" class="btn btn-outline-secondary ml-2">Réinitialiser</a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistiques globales -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total des notifications</h5>
                                    <h2 class="card-text">{{ $stats['total'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Notifications lues</h5>
                                    <h2 class="card-text">{{ $stats['lues'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Notifications non lues</h5>
                                    <h2 class="card-text">{{ $stats['non_lues'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques par type -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Notifications par type</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartParType" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Notifications par statut</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartParStatut" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau détaillé -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Détails par type de notification</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Nombre</th>
                                            <th>Pourcentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stats['par_type'] as $type => $count)
                                            <tr>
                                                <td>{{ $type ?: 'Non défini' }}</td>
                                                <td>{{ $count }}</td>
                                                <td>{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 2) : 0 }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique par type
        const ctxType = document.getElementById('chartParType').getContext('2d');
        const chartParType = new Chart(ctxType, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_map(function($type) { return $type ?: 'Non défini'; }, array_keys($stats['par_type']))) !!},
                datasets: [{
                    label: 'Notifications par type',
                    data: {!! json_encode(array_values($stats['par_type'])) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Répartition par type'
                    }
                }
            }
        });

        // Graphique par statut
        const ctxStatut = document.getElementById('chartParStatut').getContext('2d');
        const chartParStatut = new Chart(ctxStatut, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($stats['par_statut'])) !!},
                datasets: [{
                    label: 'Notifications par statut',
                    data: {!! json_encode(array_values($stats['par_statut'])) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Répartition par statut'
                    }
                }
            }
        });
    });
</script>
@endpush
