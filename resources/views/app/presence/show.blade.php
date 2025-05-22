@extends('layouts.app')

@section('title', 'Détails du pointage')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Détails du pointage</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pointage.index') }}">Pointage</a></li>
        <li class="breadcrumb-item active">Détails</li>
    </ol>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-1"></i>
                        Informations du pointage
                    </div>
                    <div>
                        @if($pointage->statut == 'enregistre' && auth()->user()->can('validate', $pointage))
                            <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#validerModal">
                                <i class="fas fa-check me-1"></i> Valider
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#annulerModal">
                                <i class="fas fa-times me-1"></i> Annuler
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Informations générales</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">ID</th>
                                    <td>{{ $pointage->id }}</td>
                                </tr>
                                <tr>
                                    <th>Date et heure</th>
                                    <td>{{ $pointage->date_heure->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
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
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        @if($pointage->statut == 'enregistre')
                                            <span class="badge bg-secondary">En attente</span>
                                        @elseif($pointage->statut == 'valide')
                                            <span class="badge bg-success">Validé</span>
                                        @elseif($pointage->statut == 'annule')
                                            <span class="badge bg-danger">Annulé</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Méthode</th>
                                    <td>{{ $pointage->methodePointage->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Site</th>
                                    <td>{{ $pointage->site ? $pointage->site->nom : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Informations de l'employé</h5>
                            <div class="d-flex align-items-center mb-3">
                                @if($pointage->user->photo_path)
                                    <img src="{{ asset('storage/' . $pointage->user->photo_path) }}" class="rounded-circle me-3" width="60" height="60">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h5 class="mb-0">{{ $pointage->user->nom }} {{ $pointage->user->prenom }}</h5>
                                    <p class="text-muted mb-0">{{ $pointage->user->email }}</p>
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%">Département</th>
                                    <td>{{ $pointage->user->departement ? $pointage->user->departement->nom : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Poste</th>
                                    <td>{{ $pointage->user->poste ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $pointage->user->telephone ?: 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Informations techniques</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%">Adresse IP</th>
                                            <td>{{ $pointage->adresse_ip ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Appareil</th>
                                            <td>{{ $pointage->appareil ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Source</th>
                                            <td>{{ $pointage->source ?: 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%">Latitude</th>
                                            <td>{{ $pointage->latitude ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Longitude</th>
                                            <td>{{ $pointage->longitude ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Créé le</th>
                                            <td>{{ $pointage->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($pointage->commentaire)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Commentaire</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $pointage->commentaire }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($pointage->validateur)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Validation</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Validé par</th>
                                    <td>{{ $pointage->validateur->nom }} {{ $pointage->validateur->prenom }}</td>
                                </tr>
                                <tr>
                                    <th>Date de validation</th>
                                    <td>{{ $pointage->date_validation ? $pointage->date_validation->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="row">
                @if($pointage->latitude && $pointage->longitude)
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Localisation
                        </div>
                        <div class="card-body">
                            <div id="map" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($pointage->photo_path)
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-camera me-1"></i>
                            Photo
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset('storage/' . $pointage->photo_path) }}" class="img-fluid img-thumbnail" style="max-height: 300px;">
                        </div>
                    </div>
                </div>
                @endif
                
                @if($pointage->signature_path)
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-signature me-1"></i>
                            Signature
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset('storage/' . $pointage->signature_path) }}" class="img-fluid img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Valider -->
@if($pointage->statut == 'enregistre' && auth()->user()->can('validate', $pointage))
<div class="modal fade" id="validerModal" tabindex="-1" aria-labelledby="validerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validerModalLabel">Valider le pointage</h5>
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
<div class="modal fade" id="annulerModal" tabindex="-1" aria-labelledby="annulerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="annulerModalLabel">Annuler le pointage</h5>
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
@endsection

@section('styles')
@if($pointage->latitude && $pointage->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
@endif
@endsection

@section('scripts')
@if($pointage->latitude && $pointage->longitude)
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la carte
        var map = L.map('map').setView([{{ $pointage->latitude }}, {{ $pointage->longitude }}], 15);
        
        // Ajouter la couche de tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Ajouter un marqueur à la position du pointage
        var marker = L.marker([{{ $pointage->latitude }}, {{ $pointage->longitude }}]).addTo(map);
        
        // Si le pointage est associé à un site avec geofencing, afficher le rayon
        @if($pointage->site && $pointage->site->has_geofencing)
            var siteCircle = L.circle([{{ $pointage->site->latitude }}, {{ $pointage->site->longitude }}], {
                color: 'blue',
                fillColor: '#30f',
                fillOpacity: 0.2,
                radius: {{ $pointage->site->rayon_geofencing ?: 100 }} // Rayon en mètres
            }).addTo(map);
            
            siteCircle.bindPopup("<strong>{{ $pointage->site->nom }}</strong><br>{{ $pointage->site->adresse }}<br>{{ $pointage->site->code_postal }} {{ $pointage->site->ville }}");
            
            // Ajuster la vue pour inclure à la fois le marqueur et le cercle du site
            var group = new L.featureGroup([marker, siteCircle]);
            map.fitBounds(group.getBounds().pad(0.1));
        @endif
    });
</script>
@endif
@endsection
