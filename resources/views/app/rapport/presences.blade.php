@extends('layouts.app')

@section('title', 'Rapport de Présences')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Rapport de Présences</h3>
                    <div>
                        <a href="{{ route('rapports.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('rapports.presences', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'pdf']) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Exporter en PDF
                        </a>
                        <a href="{{ route('rapports.presences', ['date_debut' => $dateDebut->format('Y-m-d'), 'date_fin' => $dateFin->format('Y-m-d'), 'departement_id' => $departementId, 'format' => 'excel']) }}" class="btn btn-success">
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
                                    <span class="info-box-text">Taux de présence</span>
                                    <span class="info-box-number">{{ $statistiques['taux_presence'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <div class="info-box-content">
                                    <span class="info-box-text">Taux de retard</span>
                                    <span class="info-box-number">{{ $statistiques['taux_retard'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <div class="info-box-content">
                                    <span class="info-box-text">Total absences</span>
                                    <span class="info-box-number">{{ $statistiques['total_absences'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    <th>Heures de présence</th>
                                    <th>Jours de présence</th>
                                    <th>Jours d'absence</th>
                                    <th>Retards</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donnees as $donnee)
                                <tr>
                                    <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                                    <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                                    <td>{{ $donnee['heures_presence']['heures_formatees'] }}</td>
                                    <td>{{ $donnee['jours_presence'] }}</td>
                                    <td>{{ $donnee['jours_absence'] }}</td>
                                    <td>{{ count($donnee['retards']['details_jours']) }}</td>
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
                    <h5 class="modal-title" id="detailsModalLabel{{ $donnee['employe']->id }}">Détails pour {{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="detailsTab{{ $donnee['employe']->id }}" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="presences-tab{{ $donnee['employe']->id }}" data-toggle="tab" href="#presences{{ $donnee['employe']->id }}" role="tab" aria-controls="presences{{ $donnee['employe']->id }}" aria-selected="true">Présences</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="retards-tab{{ $donnee['employe']->id }}" data-toggle="tab" href="#retards{{ $donnee['employe']->id }}" role="tab" aria-controls="retards{{ $donnee['employe']->id }}" aria-selected="false">Retards</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="detailsTabContent{{ $donnee['employe']->id }}">
                        <div class="tab-pane fade show active" id="presences{{ $donnee['employe']->id }}" role="tabpanel" aria-labelledby="presences-tab{{ $donnee['employe']->id }}">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Entrée</th>
                                            <th>Sortie</th>
                                            <th>Durée</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $presencesParJour = $donnee['presences']->groupBy(function ($item) {
                                                return $item->date_heure->format('Y-m-d');
                                            });
                                        @endphp
                                        
                                        @foreach($presencesParJour as $date => $presences)
                                            @php
                                                $entree = $presences->where('type', 'entree')->first();
                                                $sortie = $presences->where('type', 'sortie')->first();
                                                
                                                $duree = null;
                                                if ($entree && $sortie) {
                                                    $duree = $entree->date_heure->diff($sortie->date_heure);
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                                <td>{{ $entree ? $entree->date_heure->format('H:i') : 'N/A' }}</td>
                                                <td>{{ $sortie ? $sortie->date_heure->format('H:i') : 'N/A' }}</td>
                                                <td>{{ $duree ? sprintf('%02d:%02d', $duree->h, $duree->i) : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="retards{{ $donnee['employe']->id }}" role="tabpanel" aria-labelledby="retards-tab{{ $donnee['employe']->id }}">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Heure prévue</th>
                                            <th>Heure réelle</th>
                                            <th>Retard (minutes)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($donnee['retards']['details_jours'] as $retard)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($retard['date'])->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($retard['heure_prevue'])->format('H:i') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($retard['heure_reelle'])->format('H:i') }}</td>
                                            <td>{{ $retard['minutes_retard'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
