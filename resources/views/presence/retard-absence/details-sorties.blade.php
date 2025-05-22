@extends('layouts.app')

@section('title', 'Détails des sorties manquantes - ' . $user->nom . ' ' . $user->prenom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Détails des sorties manquantes - {{ $user->nom }} {{ $user->prenom }}
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
                    <form action="{{ route('retard-absence.details-sorties', $user->id) }}" method="GET" class="mb-4">
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
                                    <h5 class="mb-0">Résumé des sorties manquantes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total des sorties manquantes</span>
                                                    <span class="info-box-number">{{ count($sortiesManquantes) }} jours</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Taux d'oubli</span>
                                                    @php
                                                        $totalJoursTravail = $dateDebut->diffInDaysFiltered(function ($date) use ($user) {
                                                            return in_array($date->dayOfWeekIso, $user->joursTravail()->pluck('jour')->toArray());
                                                        }, $dateFin) + 1;
                                                        
                                                        $tauxOubli = $totalJoursTravail > 0 ? (count($sortiesManquantes) / $totalJoursTravail) * 100 : 0;
                                                    @endphp
                                                    <span class="info-box-number">{{ round($tauxOubli, 1) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <h6>Période: {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}</h6>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ min(100, $tauxOubli) }}%" 
                                                 aria-valuenow="{{ $tauxOubli }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ round($tauxOubli, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des sorties manquantes -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Détails des sorties manquantes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Dernier pointage</th>
                                            <th>Notification</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sortiesManquantes as $sortie)
                                        <tr>
                                            <td>{{ $sortie['date']->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $sortie['dernier_pointage']->type == 'entree' ? 'success' : 'info' }}">
                                                    {{ $sortie['dernier_pointage']->type == 'entree' ? 'Entrée' : 'Fin de pause' }}
                                                </span>
                                                à {{ $sortie['dernier_pointage']->date_heure->format('H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    Site: {{ $sortie['dernier_pointage']->site->nom ?? 'Non spécifié' }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($sortie['notification'])
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-bell mr-1"></i> 
                                                        Envoyée le {{ $sortie['notification']->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-bell-slash mr-1"></i> 
                                                        Non notifiée
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalPointages{{ $sortie['date']->format('Ymd') }}">
                                                    <i class="fas fa-list mr-1"></i> Pointages du jour
                                                </button>
                                                
                                                <!-- Modal des pointages -->
                                                <div class="modal fade" id="modalPointages{{ $sortie['date']->format('Ymd') }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $sortie['date']->format('Ymd') }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalLabel{{ $sortie['date']->format('Ymd') }}">
                                                                    Pointages du {{ $sortie['date']->format('d/m/Y') }}
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="timeline">
                                                                    @foreach($sortie['dernier_pointage']->where('user_id', $user->id)
                                                                                                      ->whereDate('date_heure', $sortie['date'])
                                                                                                      ->orderBy('date_heure')
                                                                                                      ->get() as $presence)
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
                                                                    <div>
                                                                        <i class="fas fa-exclamation-triangle bg-warning"></i>
                                                                        <div class="timeline-item">
                                                                            <span class="time">
                                                                                <i class="fas fa-clock"></i> Fin de journée
                                                                            </span>
                                                                            <h3 class="timeline-header">
                                                                                <span class="badge badge-warning">Sortie manquante</span>
                                                                            </h3>
                                                                            <div class="timeline-body">
                                                                                <p class="text-danger">
                                                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                                                    Aucun pointage de sortie n'a été enregistré pour cette journée.
                                                                                </p>
                                                                            </div>
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
                                                
                                                @can('manage', App\Models\Presence::class)
                                                <a href="{{ route('pointage.ajouter-sortie', ['user_id' => $user->id, 'date' => $sortie['date']->format('Y-m-d')]) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-plus-circle mr-1"></i> Ajouter sortie
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Aucune sortie manquante trouvée pour la période sélectionnée</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique des sorties manquantes par mois -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Répartition des sorties manquantes</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sortiesChart" height="100"></canvas>
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
        // Données pour le graphique
        const sorties = @json($sortiesManquantes);
        
        // Préparation des données pour le graphique par mois
        const sortiesByMonth = {};
        
        // Compter les sorties manquantes par mois
        sorties.forEach(sortie => {
            const date = new Date(sortie.date.date);
            const monthYear = date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
            sortiesByMonth[monthYear] = (sortiesByMonth[monthYear] || 0) + 1;
        });
        
        // Graphique par mois
        const ctx = document.getElementById('sortiesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(sortiesByMonth),
                datasets: [
                    {
                        label: 'Sorties manquantes par mois',
                        data: Object.values(sortiesByMonth),
                        backgroundColor: 'rgba(23, 162, 184, 0.6)',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Sorties manquantes par mois'
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
