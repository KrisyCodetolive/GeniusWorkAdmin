@extends('layouts.app')

@section('title', 'Gestion des retards et absences')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Gestion des retards et absences
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form action="{{ route('retard-absence.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_debut">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                        value="{{ $dateDebut->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                        value="{{ $dateFin->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="departement_id">Département</label>
                                    <select class="form-control" id="departement_id" name="departement_id">
                                        <option value="">Tous les départements</option>
                                        @foreach($departements as $departement)
                                            <option value="{{ $departement->id }}" {{ $departementId == $departement->id ? 'selected' : '' }}>
                                                {{ $departement->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id">Employé</label>
                                    <select class="form-control select2" id="user_id" name="user_id">
                                        <option value="">Tous les employés</option>
                                        @foreach($employes as $employe)
                                            <option value="{{ $employe->id }}" {{ $userId == $employe->id ? 'selected' : '' }}>
                                                {{ $employe->nom }} {{ $employe->prenom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select class="form-control" id="type" name="type">
                                        <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Tous les types</option>
                                        <option value="retard" {{ $type == 'retard' ? 'selected' : '' }}>Retards</option>
                                        <option value="absence" {{ $type == 'absence' ? 'selected' : '' }}>Absences</option>
                                        <option value="sortie_manquante" {{ $type == 'sortie_manquante' ? 'selected' : '' }}>Sorties manquantes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filtrer
                                    </button>
                                    <a href="{{ route('retard-absence.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt mr-1"></i> Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Vérification manuelle -->
                    @can('manage', App\Models\Presence::class)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Vérification manuelle</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('retard-absence.verifier') }}" method="POST" class="form-inline">
                                @csrf
                                <div class="form-group mr-2">
                                    <label for="date" class="mr-2">Date :</label>
                                    <input type="date" class="form-control" id="date" name="date" value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="type_verif" class="mr-2">Type :</label>
                                    <select class="form-control" id="type_verif" name="type">
                                        <option value="all">Toutes les vérifications</option>
                                        <option value="retards">Retards</option>
                                        <option value="absences">Absences</option>
                                        <option value="sorties">Sorties manquantes</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-sync-alt mr-1"></i> Exécuter la vérification
                                </button>
                            </form>
                        </div>
                    </div>
                    @endcan

                    <!-- Statistiques globales -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Retards</span>
                                    <span class="info-box-number">{{ $stats['retards']['count'] }}</span>
                                    <span class="info-box-text">{{ $stats['retards']['employes_distincts'] }} employés concernés</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-user-slash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Absences</span>
                                    <span class="info-box-number">{{ $stats['absences']['count'] }}</span>
                                    <span class="info-box-text">{{ $stats['absences']['employes_distincts'] }} employés concernés</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-sign-out-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sorties manquantes</span>
                                    <span class="info-box-number">{{ $stats['sorties_manquantes']['count'] }}</span>
                                    <span class="info-box-text">{{ $stats['sorties_manquantes']['employes_distincts'] }} employés concernés</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total employés</span>
                                    <span class="info-box-number">{{ $stats['total_employes'] }}</span>
                                    <span class="info-box-text">Période: {{ $stats['periode']['debut']->format('d/m/Y') }} - {{ $stats['periode']['fin']->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques par département -->
                    @if(count($stats['par_departement']) > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Statistiques par département</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Département</th>
                                            <th>Employés</th>
                                            <th>Retards</th>
                                            <th>Absences</th>
                                            <th>Sorties manquantes</th>
                                            <th>Total incidents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['par_departement'] as $depId => $depStats)
                                        <tr>
                                            <td>{{ $depStats['nom'] }}</td>
                                            <td>{{ $depStats['total_employes'] }}</td>
                                            <td>{{ $depStats['retards']['count'] }}</td>
                                            <td>{{ $depStats['absences']['count'] }}</td>
                                            <td>{{ $depStats['sorties_manquantes']['count'] }}</td>
                                            <td>{{ $depStats['retards']['count'] + $depStats['absences']['count'] + $depStats['sorties_manquantes']['count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Graphique d'évolution -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Évolution sur la période</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="evolutionChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Liste des notifications -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Historique des notifications</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Employé</th>
                                            <th>Type</th>
                                            <th>Détails</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($notifications as $notification)
                                        <tr>
                                            <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($notification->user)
                                                    {{ $notification->user->nom }} {{ $notification->user->prenom }}
                                                @else
                                                    Employé supprimé
                                                @endif
                                            </td>
                                            <td>
                                                @if($notification->type_notification == 'retard')
                                                    <span class="badge badge-warning">Retard</span>
                                                @elseif($notification->type_notification == 'absence')
                                                    <span class="badge badge-danger">Absence</span>
                                                @elseif($notification->type_notification == 'sortie_manquante')
                                                    <span class="badge badge-info">Sortie manquante</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $data = json_decode($notification->data, true);
                                                @endphp
                                                
                                                @if($notification->type_notification == 'retard')
                                                    Retard de {{ $data['minutes_retard'] ?? '?' }} minutes
                                                    ({{ $data['heure_prevue'] ?? '?' }} → {{ $data['heure_pointage'] ?? '?' }})
                                                @elseif($notification->type_notification == 'absence')
                                                    Absence le {{ $data['date'] ?? $notification->created_at->format('d/m/Y') }}
                                                @elseif($notification->type_notification == 'sortie_manquante')
                                                    Entrée à {{ $data['heure_entree'] ?? '?' }}, 
                                                    sortie prévue à {{ $data['heure_fin_prevue'] ?? '?' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($notification->user)
                                                    @if($notification->type_notification == 'retard')
                                                        <a href="{{ route('retard-absence.details-retards', $notification->user->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Détails
                                                        </a>
                                                    @elseif($notification->type_notification == 'absence')
                                                        <a href="{{ route('retard-absence.details-absences', $notification->user->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Détails
                                                        </a>
                                                    @elseif($notification->type_notification == 'sortie_manquante')
                                                        <a href="{{ route('retard-absence.details-sorties', $notification->user->id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Détails
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Aucune notification trouvée</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                {{ $notifications->appends(request()->except('page'))->links() }}
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
        $('.select2').select2();
        
        // Données pour le graphique d'évolution
        const statsParJour = @json($stats['par_jour']);
        const dates = Object.keys(statsParJour).map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        });
        
        const retards = Object.values(statsParJour).map(jour => jour.retards.count);
        const absences = Object.values(statsParJour).map(jour => jour.absences.count);
        const sorties = Object.values(statsParJour).map(jour => jour.sorties_manquantes.count);
        
        // Création du graphique
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Retards',
                        data: retards,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: 'Absences',
                        data: absences,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: 'Sorties manquantes',
                        data: sorties,
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.1,
                        fill: true
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
                        text: 'Évolution des incidents de présence'
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
