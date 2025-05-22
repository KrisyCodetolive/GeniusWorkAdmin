@extends('layouts.app')

@section('title', 'Tableau de bord des présences')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tableau de bord des présences</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Présences</li>
    </ol>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrer les résultats
                </div>
                <div class="card-body">
                    <form action="{{ route('pointage.dashboard') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $date->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="site_id" class="form-label">Site</label>
                            <select class="form-select" id="site_id" name="site_id">
                                <option value="">Tous les sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="departement_id" class="form-label">Département</label>
                            <select class="form-select" id="departement_id" name="departement_id">
                                <option value="">Tous les départements</option>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" {{ $departementId == $departement->id ? 'selected' : '' }}>{{ $departement->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total des employés</div>
                            <div class="display-6">{{ $totalEmployes }}</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Voir les détails</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Employés présents</div>
                            <div class="display-6">{{ $presents }}</div>
                        </div>
                        <div>
                            <i class="fas fa-user-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        {{ $totalEmployes > 0 ? round(($presents / $totalEmployes) * 100, 1) : 0 }}% de présence
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Employés en pause</div>
                            <div class="display-6">{{ $enPause }}</div>
                        </div>
                        <div>
                            <i class="fas fa-coffee fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        {{ $totalEmployes > 0 ? round(($enPause / $totalEmployes) * 100, 1) : 0 }}% en pause
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Employés absents</div>
                            <div class="display-6">{{ $absents }}</div>
                        </div>
                        <div>
                            <i class="fas fa-user-times fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        {{ $totalEmployes > 0 ? round(($absents / $totalEmployes) * 100, 1) : 0 }}% d'absence
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($statsDepartement)
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Statistiques du département
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Taux de présence</h5>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $statsDepartement['taux_presence'] }}%;" aria-valuenow="{{ $statsDepartement['taux_presence'] }}" aria-valuemin="0" aria-valuemax="100">{{ $statsDepartement['taux_presence'] }}%</div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Présents: {{ $statsDepartement['presents'] }}</span>
                                            <span>En pause: {{ $statsDepartement['en_pause'] }}</span>
                                            <span>Absents: {{ $statsDepartement['absents'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Taux de retard</h5>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $statsDepartement['taux_retard'] }}%;" aria-valuenow="{{ $statsDepartement['taux_retard'] }}" aria-valuemin="0" aria-valuemax="100">{{ $statsDepartement['taux_retard'] }}%</div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Employés en retard: {{ $statsDepartement['retards'] }}</span>
                                            <span>Total employés: {{ $statsDepartement['total_employes'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-table me-1"></i>
                        Statut des employés
                    </div>
                    <div>
                        <a href="{{ route('pointage.carte') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-map-marked-alt me-1"></i> Voir la carte
                        </a>
                        <a href="{{ route('pointage.validation') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-check-circle me-1"></i> Validation
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employesTable">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Département</th>
                                    <th>Dernier pointage</th>
                                    <th>Heure</th>
                                    <th>Site</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPointages as $pointage)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($pointage->user->photo_path)
                                                    <img src="{{ asset('storage/' . $pointage->user->photo_path) }}" class="rounded-circle me-2" width="40" height="40">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $pointage->user->nom }} {{ $pointage->user->prenom }}</div>
                                                    <div class="small text-muted">{{ $pointage->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $pointage->user->departement ? $pointage->user->departement->nom : 'N/A' }}</td>
                                        <td>{{ $pointage->date_heure->format('d/m/Y') }}</td>
                                        <td>{{ $pointage->date_heure->format('H:i:s') }}</td>
                                        <td>{{ $pointage->site ? $pointage->site->nom : 'N/A' }}</td>
                                        <td>
                                            @if($pointage->type == 'entree' || $pointage->type == 'pause_fin')
                                                <span class="badge bg-success">Présent</span>
                                            @elseif($pointage->type == 'pause_debut')
                                                <span class="badge bg-warning">En pause</span>
                                            @elseif($pointage->type == 'sortie')
                                                <span class="badge bg-danger">Absent</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('pointage.show', $pointage) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun pointage trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser DataTables
        $('#employesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            order: [[2, 'desc'], [3, 'desc']],
            pageLength: 15
        });
    });
</script>
@endsection
