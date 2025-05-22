@extends('layouts.app')

@section('title', 'Détails des retards - ' . $user->nom . ' ' . $user->prenom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Détails des retards - {{ $user->nom }} {{ $user->prenom }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('retard-absence.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Retour
                        </a>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form action="{{ route('retard-absence.details-retards', $user->id) }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_debut">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                        value="{{ $dateDebut->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                        value="{{ $dateFin->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filtrer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Informations de l'employé -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Informations de l'employé</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <img src="{{ $user->photo_url ?? asset('img/default-user.png') }}" 
                                                 alt="Photo de profil" class="img-fluid rounded-circle mb-3" 
                                                 style="max-width: 100px;">
                                        </div>
                                        <div class="col-md-8">
                                            <h5>{{ $user->nom }} {{ $user->prenom }}</h5>
                                            <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                                            <p class="mb-1"><strong>Téléphone:</strong> {{ $user->telephone ?? 'Non renseigné' }}</p>
                                            <p class="mb-1">
                                                <strong>Département:</strong> 
                                                {{ $user->departement->nom ?? 'Non assigné' }}
                                            </p>
                                            <p class="mb-1">
                                                <strong>Poste:</strong> 
                                                {{ $user->poste ?? 'Non renseigné' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Résumé des retards</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total des retards</span>
                                                    <span class="info-box-number">{{ $retardInfo['total_jours_retard'] }} jours</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-danger">
                                                <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Temps total de retard</span>
                                                    <span class="info-box-number">{{ $retardInfo['total_minutes_retard'] }} minutes</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <h6>Moyenne de retard: {{ round($retardInfo['moyenne_minutes_retard'], 1) }} minutes par jour de retard</h6>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                 style="width: {{ min(100, $retardInfo['moyenne_minutes_retard']) }}%" 
                                                 aria-valuenow="{{ $retardInfo['moyenne_minutes_retard'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ round($retardInfo['moyenne_minutes_retard'], 1) }} min
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des retards par jour -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Détails des retards par jour</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Heure prévue</th>
                                            <th>Heure de pointage</th>
                                            <th>Retard (minutes)</th>
                                            <th>Détails</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($retardInfo['details_jours'] as $date => $retard)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                            <td>{{ $retard['heure_prevue'] }}</td>
                                            <td>{{ $retard['heure_pointage'] }}</td>
                                            <td>
                                                <span class="badge badge-warning">{{ $retard['minutes'] }} minutes</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalPointages{{ str_replace('-', '', $date) }}">
                                                    <i class="fas fa-list mr-1"></i> Pointages du jour
                                                </button>
                                                
                                                <!-- Modal des pointages -->
                                                <div class="modal fade" id="modalPointages{{ str_replace('-', '', $date) }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ str_replace('-', '', $date) }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalLabel{{ str_replace('-', '', $date) }}">
                                                                    Pointages du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @if(isset($presences[$date]) && $presences[$date]->count() > 0)
                                                                    <div class="timeline">
                                                                        @foreach($presences[$date] as $presence)
                                                                            <div class="time-label">
                                                                                <span class="{{ $presence->type == 'entree' ? 'bg-green' : ($presence->type == 'sortie' ? 'bg-red' : 'bg-blue') }}">
                                                                                    {{ $presence->date_heure->format('H:i') }}
                                                                                </span>
                                                                            </div>
                                                                            <div>
                                                                                <i class="{{ $presence->type == 'entree' ? 'fas fa-sign-in-alt bg-green' : ($presence->type == 'sortie' ? 'fas fa-sign-out-alt bg-red' : 'fas fa-coffee bg-blue') }}"></i>
                                                                                <div class="timeline-item">
                                                                                    <span class="time">
                                                                                        <i class="fas fa-clock"></i> {{ $presence->date_heure->format('H:i:s') }}
                                                                                    </span>
                                                                                    <h3 class="timeline-header">
                                                                                        @if($presence->type == 'entree')
                                                                                            <span class="badge badge-success">Entrée</span>
                                                                                        @elseif($presence->type == 'sortie')
                                                                                            <span class="badge badge-danger">Sortie</span>
                                                                                        @elseif($presence->type == 'pause_debut')
                                                                                            <span class="badge badge-info">Début de pause</span>
                                                                                        @elseif($presence->type == 'pause_fin')
                                                                                            <span class="badge badge-info">Fin de pause</span>
                                                                                        @endif
                                                                                    </h3>
                                                                                    <div class="timeline-body">
                                                                                        <p><strong>Site:</strong> {{ $presence->site->nom ?? 'Non spécifié' }}</p>
                                                                                        <p><strong>Méthode:</strong> {{ $presence->methodePointage->nom ?? 'Non spécifiée' }}</p>
                                                                                        @if($presence->distance_site)
                                                                                            <p><strong>Distance du site:</strong> {{ $presence->distance_site }} mètres</p>
                                                                                        @endif
                                                                                        @if($presence->commentaire)
                                                                                            <p><strong>Commentaire:</strong> {{ $presence->commentaire }}</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <p class="text-center">Aucun pointage trouvé pour cette date.</p>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Aucun retard trouvé pour la période sélectionnée</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique d'évolution des retards -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Évolution des retards</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="retardChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function() {
        // Données pour le graphique d'évolution des retards
        const retardsData = @json($retardInfo['details_jours']);
        const dates = Object.keys(retardsData).map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        });
        
        const minutes = Object.values(retardsData).map(retard => retard.minutes);
        
        // Création du graphique
        const ctx = document.getElementById('retardChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Minutes de retard',
                        data: minutes,
                        backgroundColor: 'rgba(255, 193, 7, 0.6)',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Évolution des retards (en minutes)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
