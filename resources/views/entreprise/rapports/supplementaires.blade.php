@extends('layouts.app')

@section('title', 'Rapport d\'Heures Supplémentaires')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Rapport d'Heures Supplémentaires</h3>
                    <div>
                        <a href="{{ route('rapports.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('rapports.supplementaires', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'pdf']) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Exporter en PDF
                        </a>
                        <a href="{{ route('rapports.supplementaires', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'excel']) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exporter en Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Période :</strong> {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statistiques globales</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre d'employés</span>
                                    <span class="info-box-number">{{ $statistiques['total_employes'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total heures supplémentaires</span>
                                    <span class="info-box-number">{{ $statistiques['total_heures_formatees'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <div class="info-box-content">
                                    <span class="info-box-text">Moyenne minutes par employé</span>
                                    <span class="info-box-number">{{ $statistiques['moyenne_minutes_par_employe'] }} min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Heures supplémentaires par département</h4>
                </div>
                <div class="card-body">
                    <canvas id="departementChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 5 employés (heures supplémentaires)</h4>
                </div>
                <div class="card-body">
                    <canvas id="employesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Données détaillées -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Détails par employé</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Département</th>
                                    <th>Heures supplémentaires</th>
                                    <th>Nombre d'occurrences</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donnees as $donnee)
                                <tr>
                                    <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                                    <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                                    <td>{{ $donnee['heures_formatees'] }}</td>
                                    <td>{{ count($donnee['supplementaires']) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal{{ $donnee['employe']->id }}">
                                            <i class="fas fa-eye"></i> Détails
                                        </button>
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

    <!-- Modals pour les détails -->
    @foreach($donnees as $donnee)
    <div class="modal fade" id="detailsModal{{ $donnee['employe']->id }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel{{ $donnee['employe']->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel{{ $donnee['employe']->id }}">Détails des heures supplémentaires pour {{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Durée</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                    <th>Validé par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donnee['supplementaires'] as $supplementaire)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($supplementaire->date)->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $heures = floor($supplementaire->duree_minutes / 60);
                                            $minutes = $supplementaire->duree_minutes % 60;
                                            echo sprintf('%02d:%02d', $heures, $minutes);
                                        @endphp
                                    </td>
                                    <td>{{ $supplementaire->motif }}</td>
                                    <td>
                                        <span class="badge badge-{{ $supplementaire->statut === 'approuve' ? 'success' : ($supplementaire->statut === 'refuse' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($supplementaire->statut) }}
                                        </span>
                                    </td>
                                    <td>{{ $supplementaire->validateur ? $supplementaire->validateur->nom . ' ' . $supplementaire->validateur->prenom : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique par département
        var departementData = @json(collect($statistiques['supplementaires_par_departement'])->map(function($item) {
            return [
                'departement' => $item['departement']->nom,
                'minutes' => $item['minutes_supplementaires']
            ];
        }));
        
        var departementLabels = departementData.map(function(item) { return item.departement; });
        var departementMinutes = departementData.map(function(item) { return item.minutes; });
        
        var ctxDepartement = document.getElementById('departementChart').getContext('2d');
        var departementChart = new Chart(ctxDepartement, {
            type: 'pie',
            data: {
                labels: departementLabels,
                datasets: [{
                    data: departementMinutes,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
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
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var hours = Math.floor(value / 60);
                                var minutes = value % 60;
                                return label + ': ' + hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
                            }
                        }
                    }
                }
            }
        });
        
        // Graphique top 5 employés
        var employesData = @json(collect($donnees)->sortByDesc('minutes_supplementaires')->take(5)->map(function($item) {
            return [
                'employe' => $item['employe']->nom . ' ' . $item['employe']->prenom,
                'minutes' => $item['minutes_supplementaires']
            ];
        }));
        
        var employesLabels = employesData.map(function(item) { return item.employe; });
        var employesMinutes = employesData.map(function(item) { return item.minutes; });
        
        var ctxEmployes = document.getElementById('employesChart').getContext('2d');
        var employesChart = new Chart(ctxEmployes, {
            type: 'bar',
            data: {
                labels: employesLabels,
                datasets: [{
                    label: 'Minutes supplémentaires',
                    data: employesMinutes,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Employés'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.raw || 0;
                                var hours = Math.floor(value / 60);
                                var minutes = value % 60;
                                return label + ': ' + hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
