@extends('layouts.app')

@section('title', 'Carte des présences')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
<style>
    #map {
        height: 600px;
        width: 100%;
    }
    .employee-popup .employee-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }
    .employee-popup {
        display: flex;
        align-items: center;
    }
    .employee-info {
        flex: 1;
    }
    .employee-name {
        font-weight: bold;
        margin-bottom: 5px;
    }
    .employee-details {
        font-size: 0.9em;
        color: #666;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Carte des présences</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pointage.dashboard') }}">Présences</a></li>
        <li class="breadcrumb-item active">Carte</li>
    </ol>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrer les résultats
                </div>
                <div class="card-body">
                    <form action="{{ route('pointage.carte') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="site_id" class="form-label">Site</label>
                            <select class="form-select" id="site_id" name="site_id">
                                <option value="">Tous les sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ $siteId == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                                @endforeach
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
                    <i class="fas fa-map-marked-alt me-1"></i>
                    Carte des employés
                </div>
                <div class="card-body">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Liste des employés localisés
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="employesTable">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Département</th>
                                    <th>Dernier pointage</th>
                                    <th>Site</th>
                                    <th>Statut</th>
                                    <th>Coordonnées</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointagesAvecGeo as $pointage)
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
                                        <td>{{ $pointage->date_heure->format('d/m/Y H:i:s') }}</td>
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
                                        <td>{{ $pointage->latitude }}, {{ $pointage->longitude }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun employé localisé</td>
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
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la carte
        var map = L.map('map').setView([0, 0], 2);
        
        // Ajouter la couche de tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Définir les icônes pour les différents statuts
        var presentIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        var pauseIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        var absentIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        // Ajouter les sites sur la carte
        @foreach($sites as $site)
            @if($site->latitude && $site->longitude)
                var siteCircle = L.circle([{{ $site->latitude }}, {{ $site->longitude }}], {
                    color: 'blue',
                    fillColor: '#30f',
                    fillOpacity: 0.2,
                    radius: {{ $site->rayon_geofencing ?: 100 }} // Rayon en mètres
                }).addTo(map);
                
                siteCircle.bindPopup("<strong>{{ $site->nom }}</strong><br>{{ $site->adresse }}<br>{{ $site->code_postal }} {{ $site->ville }}");
            @endif
        @endforeach
        
        // Ajouter les employés sur la carte
        var markers = [];
        
        @foreach($pointagesAvecGeo as $pointage)
            var icon;
            
            @if($pointage->type == 'entree' || $pointage->type == 'pause_fin')
                icon = presentIcon;
            @elseif($pointage->type == 'pause_debut')
                icon = pauseIcon;
            @else
                icon = absentIcon;
            @endif
            
            var marker = L.marker([{{ $pointage->latitude }}, {{ $pointage->longitude }}], {icon: icon}).addTo(map);
            
            var popupContent = `
                <div class="employee-popup">
                    @if($pointage->user->photo_path)
                        <img src="{{ asset('storage/' . $pointage->user->photo_path) }}" class="employee-photo">
                    @else
                        <div class="employee-photo bg-secondary d-flex align-items-center justify-content-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    @endif
                    <div class="employee-info">
                        <div class="employee-name">{{ $pointage->user->nom }} {{ $pointage->user->prenom }}</div>
                        <div class="employee-details">
                            <div>{{ $pointage->user->departement ? $pointage->user->departement->nom : 'Aucun département' }}</div>
                            <div>{{ $pointage->date_heure->format('d/m/Y H:i:s') }}</div>
                            <div>
                                @if($pointage->type == 'entree' || $pointage->type == 'pause_fin')
                                    <span class="text-success">Présent</span>
                                @elseif($pointage->type == 'pause_debut')
                                    <span class="text-warning">En pause</span>
                                @elseif($pointage->type == 'sortie')
                                    <span class="text-danger">Absent</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
        @endforeach
        
        // Ajuster la vue de la carte pour inclure tous les marqueurs
        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        } else {
            // Si aucun marqueur, centrer sur la France
            map.setView([46.603354, 1.888334], 6);
        }
        
        // Initialiser DataTables
        $('#employesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            order: [[2, 'desc']],
            pageLength: 10
        });
    });
</script>
@endsection
