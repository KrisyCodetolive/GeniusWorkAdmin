@extends('layouts.app')

@section('title', 'Rapport de Congés')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Rapport de Congés</h3>
                    <div>
                        <a href="{{ route('rapports.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('rapports.conges', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'pdf']) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Exporter en PDF
                        </a>
                        <a href="{{ route('rapports.conges', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'excel']) }}" class="btn btn-success">
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
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre d'employés</span>
                                    <span class="info-box-number">{{ $statistiques['total_employes'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total congés utilisés</span>
                                    <span class="info-box-number">{{ $statistiques['total_conges_utilises'] }} jours</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total congés restants</span>
                                    <span class="info-box-number">{{ $statistiques['total_conges_restants'] }} jours</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <div class="info-box-content">
                                    <span class="info-box-text">Moyenne par employé</span>
                                    <span class="info-box-number">{{ $statistiques['moyenne_conges_utilises'] }} jours</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique de tendance -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tendance des congés</h4>
                </div>
                <div class="card-body">
                    <canvas id="congesChart" height="100"></canvas>
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
                                    <th>Congés annuels</th>
                                    <th>Jours utilisés</th>
                                    <th>Jours restants</th>
                                    <th>Pourcentage utilisé</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donnees as $donnee)
                                @php
                                    $congesAnnuels = $donnee['employe']->conges_annuels ?? 0;
                                    $pourcentageUtilise = $congesAnnuels > 0 
                                        ? round(($donnee['jours_utilises'] / $congesAnnuels) * 100, 2) 
                                        : 0;
                                @endphp
                                <tr>
                                    <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                                    <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                                    <td>{{ $congesAnnuels }}</td>
                                    <td>{{ $donnee['jours_utilises'] }}</td>
                                    <td>{{ $donnee['jours_restants'] }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ $pourcentageUtilise > 75 ? 'danger' : ($pourcentageUtilise > 50 ? 'warning' : 'success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $pourcentageUtilise }}%" 
                                                 aria-valuenow="{{ $pourcentageUtilise }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">{{ $pourcentageUtilise }}%</div>
                                        </div>
                                    </td>
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
                    <h5 class="modal-title" id="detailsModalLabel{{ $donnee['employe']->id }}">Détails des congés pour {{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Type de congé</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Durée (jours)</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donnee['conges'] as $conge)
                                @php
                                    $duree = $conge->date_debut->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                                        return $date->isWeekday();
                                    }, $conge->date_fin);
                                @endphp
                                <tr>
                                    <td>{{ $conge->type }}</td>
                                    <td>{{ $conge->date_debut->format('d/m/Y') }}</td>
                                    <td>{{ $conge->date_fin->format('d/m/Y') }}</td>
                                    <td>{{ $duree }}</td>
                                    <td>{{ $conge->motif }}</td>
                                    <td>
                                        <span class="badge badge-{{ $conge->statut === 'approuve' ? 'success' : ($conge->statut === 'refuse' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($conge->statut) }}
                                        </span>
                                    </td>
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
        // Préparer les données pour le graphique
        var congesParMois = @json(collect($donnees)->flatMap(function($donnee) {
            return $donnee['conges']->map(function($conge) {
                return [
                    'mois' => $conge->date_debut->format('Y-m'),
                    'jours' => $conge->date_debut->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                        return $date->isWeekday();
                    }, $conge->date_fin)
                ];
            });
        })->groupBy('mois')->map(function($items) {
            return $items->sum('jours');
        }));
        
        // Trier les mois
        var mois = Object.keys(congesParMois).sort();
        var joursConges = mois.map(function(m) { return congesParMois[m]; });
        
        // Formater les étiquettes des mois
        var moisFormatte = mois.map(function(m) {
            var date = new Date(m + '-01');
            return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        });
        
        // Créer le graphique
        var ctx = document.getElementById('congesChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: moisFormatte,
                datasets: [{
                    label: 'Jours de congés',
                    data: joursConges,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
                            text: 'Nombre de jours'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mois'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
