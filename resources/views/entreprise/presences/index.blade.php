@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Suivi des pointages')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Suivi des pointages</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('entreprise.dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Suivi des pointages</li>
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

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Liste des pointages
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="presencesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Site</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presences as $presence)
                            <tr>
                                <td>{{ $presence->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($presence->user)
                                        {{ $presence->user->nom }} {{ $presence->user->prenom }}
                                    @else
                                        Utilisateur inconnu
                                    @endif
                                </td>
                                <td>
                                    @if($presence->type == 'entree')
                                        <span class="badge bg-success">Entrée</span>
                                    @elseif($presence->type == 'sortie')
                                        <span class="badge bg-danger">Sortie</span>
                                    @elseif($presence->type == 'pause_debut')
                                        <span class="badge bg-warning">Début de pause</span>
                                    @elseif($presence->type == 'pause_fin')
                                        <span class="badge bg-info">Fin de pause</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $presence->type }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($presence->site)
                                        {{ $presence->site->nom }}
                                    @else
                                        Non spécifié
                                    @endif
                                </td>
                                <td>
                                    @if($presence->methodePointage)
                                        {{ $presence->methodePointage->nom }}
                                    @else
                                        Non spécifié
                                    @endif
                                </td>
                                <td>
                                    @if($presence->statut == 'validee')
                                        <span class="badge bg-success">Validé</span>
                                    @elseif($presence->statut == 'rejetee')
                                        <span class="badge bg-danger">Rejeté</span>
                                    @elseif($presence->statut == 'en_attente')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($presence->statut == 'annule')
                                        <span class="badge bg-secondary">Annulé</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $presence->statut }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $presence->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Modal de détails -->
                            <div class="modal fade" id="detailsModal{{ $presence->id }}" tabindex="-1" aria-labelledby="detailsModalLabel{{ $presence->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="detailsModalLabel{{ $presence->id }}">Détails du pointage</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Date et heure:</strong> {{ $presence->created_at->format('d/m/Y H:i:s') }}</p>
                                                    <p><strong>Employé:</strong> 
                                                        @if($presence->user)
                                                            {{ $presence->user->nom }} {{ $presence->user->prenom }}
                                                        @else
                                                            Utilisateur inconnu
                                                        @endif
                                                    </p>
                                                    <p><strong>Type:</strong> 
                                                        @if($presence->type == 'entree')
                                                            Entrée
                                                        @elseif($presence->type == 'sortie')
                                                            Sortie
                                                        @elseif($presence->type == 'pause_debut')
                                                            Début de pause
                                                        @elseif($presence->type == 'pause_fin')
                                                            Fin de pause
                                                        @else
                                                            {{ $presence->type }}
                                                        @endif
                                                    </p>
                                                    <p><strong>Site:</strong> 
                                                        @if($presence->site)
                                                            {{ $presence->site->nom }}
                                                        @else
                                                            Non spécifié
                                                        @endif
                                                    </p>
                                                    <p><strong>Méthode de pointage:</strong> 
                                                        @if($presence->methodePointage)
                                                            {{ $presence->methodePointage->nom }}
                                                        @else
                                                            Non spécifié
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Statut:</strong> 
                                                        @if($presence->statut == 'validee')
                                                            <span class="badge bg-success">Validé</span>
                                                        @elseif($presence->statut == 'rejetee')
                                                            <span class="badge bg-danger">Rejeté</span>
                                                        @elseif($presence->statut == 'en_attente')
                                                            <span class="badge bg-warning">En attente</span>
                                                        @elseif($presence->statut == 'annule')
                                                            <span class="badge bg-secondary">Annulé</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $presence->statut }}</span>
                                                        @endif
                                                    </p>
                                                    @if($presence->validateur)
                                                        <p><strong>Validé par:</strong> {{ $presence->validateur->nom }} {{ $presence->validateur->prenom }}</p>
                                                        <p><strong>Date de validation:</strong> {{ $presence->date_validation->format('d/m/Y H:i:s') }}</p>
                                                    @endif
                                                    @if($presence->commentaire)
                                                        <p><strong>Commentaire:</strong> {{ $presence->commentaire }}</p>
                                                    @endif
                                                    @if($presence->latitude && $presence->longitude)
                                                        <p><strong>Coordonnées:</strong> {{ $presence->latitude }}, {{ $presence->longitude }}</p>
                                                        @if($presence->distance_site)
                                                            <p><strong>Distance du site:</strong> {{ round($presence->distance_site, 2) }} m</p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    @if($presence->photo_url)
                                                        <div class="mb-3">
                                                            <strong>Photo:</strong>
                                                            <div class="mt-2">
                                                                <img src="{{ $presence->photo_url }}" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    @if($presence->signature_url)
                                                        <div class="mb-3">
                                                            <strong>Signature:</strong>
                                                            <div class="mt-2">
                                                                <img src="{{ $presence->signature_url }}" class="img-fluid img-thumbnail" style="max-height: 100px;">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucun pointage trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $presences->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation du tableau avec DataTables
        $('#presencesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            responsive: true,
            paging: false,
            info: false,
            searching: true,
            order: [[0, 'desc']]
        });
    });
</script>
@endsection
