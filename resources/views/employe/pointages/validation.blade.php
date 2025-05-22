@extends('layouts.app')

@section('title', 'Validation des pointages')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Validation des pointages</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pointage.dashboard') }}">Présences</a></li>
        <li class="breadcrumb-item active">Validation</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrer les pointages
                </div>
                <div class="card-body">
                    <form action="{{ route('pointage.validation') }}" method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ $dateDebut->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ $dateFin->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="site_id" class="form-label">Site</label>
                            <select class="form-select" id="site_id" name="site_id">
                                <option value="">Tous les sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="user_id" class="form-label">Employé</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Tous les employés</option>
                                @foreach($employes as $employe)
                                    <option value="{{ $employe->id }}" {{ $userId == $employe->id ? 'selected' : '' }}>{{ $employe->nom }} {{ $employe->prenom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="enregistre" {{ $statut == 'enregistre' ? 'selected' : '' }}>En attente</option>
                                <option value="valide" {{ $statut == 'valide' ? 'selected' : '' }}>Validé</option>
                                <option value="annule" {{ $statut == 'annule' ? 'selected' : '' }}>Annulé</option>
                            </select>
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
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Liste des pointages à valider
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="pointagesTable">
                            <thead>
                                <tr>
                                    <th>Employé</th>
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
                                            <div class="d-flex">
                                                <a href="{{ route('pointage.show', $pointage) }}" class="btn btn-sm btn-info me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($pointage->statut == 'enregistre')
                                                    <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#validerModal{{ $pointage->id }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#annulerModal{{ $pointage->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    
                                                    <!-- Modal Valider -->
                                                    <div class="modal fade" id="validerModal{{ $pointage->id }}" tabindex="-1" aria-labelledby="validerModalLabel{{ $pointage->id }}" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="validerModalLabel{{ $pointage->id }}">Valider le pointage</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('pointage.valider', $pointage) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p>Êtes-vous sûr de vouloir valider ce pointage ?</p>
                                                                        <div class="mb-3">
                                                                            <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                                                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                        <button type="submit" class="btn btn-success">Valider</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Modal Annuler -->
                                                    <div class="modal fade" id="annulerModal{{ $pointage->id }}" tabindex="-1" aria-labelledby="annulerModalLabel{{ $pointage->id }}" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="annulerModalLabel{{ $pointage->id }}">Annuler le pointage</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('pointage.annuler', $pointage) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p>Êtes-vous sûr de vouloir annuler ce pointage ?</p>
                                                                        <div class="mb-3">
                                                                            <label for="commentaire" class="form-label">Motif d'annulation (obligatoire)</label>
                                                                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                                        <button type="submit" class="btn btn-danger">Annuler le pointage</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun pointage trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pointages->links() }}
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
        // Initialiser Select2 pour les listes déroulantes
        $('#site_id, #user_id').select2({
            theme: 'bootstrap-5'
        });
    });
</script>
@endsection
