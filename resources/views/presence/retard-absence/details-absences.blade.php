@extends('layouts.app')

@section('title', 'Détails des absences - ' . $user->nom . ' ' . $user->prenom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-slash mr-2"></i>
                        Détails des absences - {{ $user->nom }} {{ $user->prenom }}
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
                    <form action="{{ route('retard-absence.details-absences', $user->id) }}" method="GET" class="mb-4">
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
                                    <h5 class="mb-0">Résumé des absences</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box bg-danger">
                                                <span class="info-box-icon"><i class="fas fa-calendar-times"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total des absences</span>
                                                    <span class="info-box-number">{{ count($absences) }} jours</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Taux d'absentéisme</span>
                                                    @php
                                                        $totalJoursTravail = $dateDebut->diffInDaysFiltered(function ($date) use ($user) {
                                                            return in_array($date->dayOfWeekIso, $user->joursTravail()->pluck('jour')->toArray());
                                                        }, $dateFin) + 1;
                                                        
                                                        $tauxAbsenteisme = $totalJoursTravail > 0 ? (count($absences) / $totalJoursTravail) * 100 : 0;
                                                    @endphp
                                                    <span class="info-box-number">{{ round($tauxAbsenteisme, 1) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <h6>Période: {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}</h6>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: {{ min(100, $tauxAbsenteisme) }}%" 
                                                 aria-valuenow="{{ $tauxAbsenteisme }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ round($tauxAbsenteisme, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des absences par jour -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Détails des absences</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Jour</th>
                                            <th>Notification</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($absences as $absence)
                                        <tr>
                                            <td>{{ $absence['date']->format('d/m/Y') }}</td>
                                            <td>{{ ucfirst($absence['date']->locale('fr')->dayName) }}</td>
                                            <td>
                                                @if($absence['notification'])
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-bell mr-1"></i> 
                                                        Envoyée le {{ $absence['notification']->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-bell-slash mr-1"></i> 
                                                        Non notifiée
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $justification = $user->justificationsAbsence()
                                                        ->whereDate('date_absence', $absence['date'])
                                                        ->first();
                                                @endphp
                                                
                                                @if($justification)
                                                    @if($justification->statut === 'approuve')
                                                        <span class="badge badge-success">Justifiée</span>
                                                    @elseif($justification->statut === 'refuse')
                                                        <span class="badge badge-danger">Non justifiée</span>
                                                    @else
                                                        <span class="badge badge-warning">En attente</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-danger">Non justifiée</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($justification)
                                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalJustification{{ $absence['date']->format('Ymd') }}">
                                                        <i class="fas fa-eye mr-1"></i> Voir justification
                                                    </button>
                                                    
                                                    <!-- Modal de justification -->
                                                    <div class="modal fade" id="modalJustification{{ $absence['date']->format('Ymd') }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $absence['date']->format('Ymd') }}" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalLabel{{ $absence['date']->format('Ymd') }}">
                                                                        Justification d'absence du {{ $absence['date']->format('d/m/Y') }}
                                                                    </h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p><strong>Motif:</strong> {{ $justification->motif }}</p>
                                                                    <p><strong>Description:</strong> {{ $justification->description }}</p>
                                                                    
                                                                    @if($justification->piece_jointe_url)
                                                                        <p><strong>Pièce jointe:</strong> 
                                                                            <a href="{{ $justification->piece_jointe_url }}" target="_blank">
                                                                                <i class="fas fa-file mr-1"></i> Voir le document
                                                                            </a>
                                                                        </p>
                                                                    @endif
                                                                    
                                                                    <p><strong>Statut:</strong> 
                                                                        @if($justification->statut === 'approuve')
                                                                            <span class="badge badge-success">Approuvée</span>
                                                                        @elseif($justification->statut === 'refuse')
                                                                            <span class="badge badge-danger">Refusée</span>
                                                                        @else
                                                                            <span class="badge badge-warning">En attente</span>
                                                                        @endif
                                                                    </p>
                                                                    
                                                                    @if($justification->commentaire_validation)
                                                                        <p><strong>Commentaire du validateur:</strong> {{ $justification->commentaire_validation }}</p>
                                                                    @endif
                                                                    
                                                                    @if($justification->validateur)
                                                                        <p><strong>Validé par:</strong> {{ $justification->validateur->nom }} {{ $justification->validateur->prenom }}</p>
                                                                        <p><strong>Date de validation:</strong> {{ $justification->date_validation->format('d/m/Y H:i') }}</p>
                                                                    @endif
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Aucune justification</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Aucune absence trouvée pour la période sélectionnée</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique des absences par mois -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Répartition des absences</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="absencesByMonthChart" height="200"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <canvas id="absencesByDayChart" height="200"></canvas>
                                </div>
                            </div>
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
        // Données pour les graphiques
        const absences = @json($absences);
        
        // Préparation des données pour le graphique par mois
        const absencesByMonth = {};
        const absencesByDay = {};
        
        // Initialiser les jours de la semaine
        const daysOfWeek = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        daysOfWeek.forEach(day => {
            absencesByDay[day] = 0;
        });
        
        // Compter les absences par mois et par jour
        absences.forEach(absence => {
            const date = new Date(absence.date.date);
            
            // Par mois
            const monthYear = date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
            absencesByMonth[monthYear] = (absencesByMonth[monthYear] || 0) + 1;
            
            // Par jour de la semaine
            const dayName = date.toLocaleDateString('fr-FR', { weekday: 'long' });
            const capitalizedDayName = dayName.charAt(0).toUpperCase() + dayName.slice(1);
            absencesByDay[capitalizedDayName] = (absencesByDay[capitalizedDayName] || 0) + 1;
        });
        
        // Graphique par mois
        const ctxMonth = document.getElementById('absencesByMonthChart').getContext('2d');
        new Chart(ctxMonth, {
            type: 'bar',
            data: {
                labels: Object.keys(absencesByMonth),
                datasets: [
                    {
                        label: 'Absences par mois',
                        data: Object.values(absencesByMonth),
                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        borderColor: '#dc3545',
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
                        text: 'Absences par mois'
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
        
        // Graphique par jour de la semaine
        const ctxDay = document.getElementById('absencesByDayChart').getContext('2d');
        new Chart(ctxDay, {
            type: 'pie',
            data: {
                labels: Object.keys(absencesByDay).filter(day => absencesByDay[day] > 0),
                datasets: [
                    {
                        label: 'Absences par jour',
                        data: Object.values(absencesByDay).filter(count => count > 0),
                        backgroundColor: [
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(23, 162, 184, 0.7)',
                            'rgba(0, 123, 255, 0.7)',
                            'rgba(111, 66, 193, 0.7)',
                            'rgba(248, 108, 107, 0.7)'
                        ],
                        borderColor: [
                            '#dc3545',
                            '#ffc107',
                            '#28a745',
                            '#17a2b8',
                            '#007bff',
                            '#6f42c1',
                            '#f86c6b'
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Absences par jour de la semaine'
                    }
                }
            }
        });
    });
</script>
@endsection
