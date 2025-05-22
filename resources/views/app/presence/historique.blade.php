@extends('layouts.app')

@section('title', 'Historique des pointages')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Historique des pointages</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pointage.index') }}">Pointage</a></li>
        <li class="breadcrumb-item active">Historique</li>
    </ol>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrer les résultats
                </div>
                <div class="card-body">
                    <form action="{{ route('pointage.historique') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ $dateDebut->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-5">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ $dateFin->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
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
        <div class="col-xl-4 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Résumé de la période
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75">Temps de présence</div>
                                            <div class="text-lg fw-bold">{{ $heuresPresence['total_heures_formatees'] }}</div>
                                        </div>
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75">Temps de retard</div>
                                            <div class="text-lg fw-bold">{{ $retards['total_heures_formatees'] }}</div>
                                        </div>
                                        <i class="fas fa-hourglass-half fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mt-3">Détails par jour</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Présence</th>
                                            <th>Retard</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $joursCombines = array_unique(array_merge(
                                                array_keys($heuresPresence['details_jours']),
                                                array_keys($retards['details_jours'])
                                            ));
                                            sort($joursCombines);
                                        @endphp
                                        
                                        @foreach($joursCombines as $jour)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if(isset($heuresPresence['details_jours'][$jour]))
                                                        {{ $heuresPresence['details_jours'][$jour]['heures_formatees'] }}
                                                    @else
                                                        00:00
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($retards['details_jours'][$jour]))
                                                        {{ $retards['details_jours'][$jour]['heures_formatees'] }}
                                                    @else
                                                        00:00
                                                    @endif
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
        </div>

        <div class="col-xl-8 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Liste des pointages
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="pointagesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Type</th>
                                    <th>Site</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointages as $pointage)
                                    <tr>
                                        <td>{{ $pointage->date_heure->format('d/m/Y') }}</td>
                                        <td>{{ $pointage->date_heure->format('H:i:s') }}</td>
                                        <td>
                                            @if($pointage->type == 'entree')
                                                <span class="badge bg-success">Entrée</span>
                                            @elseif($pointage->type == 'sortie')
                                                <span class="badge bg-danger">Sortie</span>
                                            @elseif($pointage->type == 'pause_debut')
                                                <span class="badge bg-warning">Début de pause</span>
                                            @elseif($pointage->type == 'pause_fin')
                                                <span class="badge bg-info">Fin de pause</span>
                                            @endif
                                        </td>
                                        <td>{{ $pointage->site ? $pointage->site->nom : 'N/A' }}</td>
                                        <td>{{ $pointage->methodePointage->nom }}</td>
                                        <td>
                                            @if($pointage->statut == 'enregistre')
                                                <span class="badge bg-secondary">En attente</span>
                                            @elseif($pointage->statut == 'valide')
                                                <span class="badge bg-success">Validé</span>
                                            @elseif($pointage->statut == 'annule')
                                                <span class="badge bg-danger">Annulé</span>
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
                                        <td colspan="7" class="text-center">Aucun pointage trouvé pour cette période</td>
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
        $('#pointagesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            order: [[0, 'desc'], [1, 'desc']],
            pageLength: 15
        });
    });
</script>
@endsection
