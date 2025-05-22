@extends('layouts.admin')

@section('title', 'Détails du site')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $site->nom }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.sites.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.sites.edit', $site->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-edit mr-2"></i> Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Informations générales -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Informations générales
                </h2>
            </div>
            <div class="p-4">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-building text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $site->nom }}</h3>
                        <p class="text-sm text-gray-600">ID: {{ $site->id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Entreprise</p>
                        <p class="text-base text-gray-800">{{ $site->entreprise->nom }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Statut</p>
                        @if($site->statut === 'actif')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Actif
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactif
                            </span>
                        @endif
                    </div>
                </div>

                @if($site->description)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="text-base text-gray-800 mt-1">{{ $site->description }}</p>
                    </div>
                @endif

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500">Date de création</p>
                    <p class="text-base text-gray-800">{{ $site->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500">Dernière modification</p>
                    <p class="text-base text-gray-800">{{ $site->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Adresse -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-map-marker-alt mr-2 text-red-500"></i> Adresse
                </h2>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <p class="text-base text-gray-800">{{ $site->adresse }}</p>
                    <p class="text-base text-gray-800">{{ $site->code_postal }}, {{ $site->ville }}</p>
                    <p class="text-base text-gray-800">{{ $site->pays }}</p>
                </div>

                @if($site->latitude && $site->longitude)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Coordonnées GPS</p>
                        <p class="text-base text-gray-800">
                            <i class="fas fa-map-pin mr-1 text-red-500"></i>
                            {{ $site->latitude }}, {{ $site->longitude }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-external-link-alt mr-1"></i> Voir sur Google Maps
                        </a>
                    </div>
                @endif

                @if($site->has_geofencing)
                    <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                        <p class="text-sm font-medium text-purple-700">
                            <i class="fas fa-map-marked-alt mr-1"></i> Geofencing activé
                        </p>
                        <p class="text-sm text-purple-600">
                            Rayon: {{ $site->rayon_geofencing }} mètres
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contact -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-address-card mr-2 text-green-500"></i> Contact
                </h2>
            </div>
            <div class="p-4">
                @if($site->contact_nom || $site->contact_email || $site->contact_telephone)
                    @if($site->contact_nom)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500">Nom du contact</p>
                            <p class="text-base text-gray-800">{{ $site->contact_nom }}</p>
                        </div>
                    @endif

                    @if($site->contact_email)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-base text-gray-800">
                                <a href="mailto:{{ $site->contact_email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $site->contact_email }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if($site->contact_telephone)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500">Téléphone</p>
                            <p class="text-base text-gray-800">
                                <a href="tel:{{ $site->contact_telephone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $site->contact_telephone }}
                                </a>
                            </p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 italic">Aucune information de contact disponible</p>
                @endif
            </div>
        </div>

        <!-- Horaires -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-clock mr-2 text-yellow-500"></i> Horaires d'ouverture
                </h2>
            </div>
            <div class="p-4">
                @if($site->horaires)
                    <div class="space-y-2">
                        @php
                            $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                        @endphp

                        @foreach($jours as $jour)
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-700 capitalize">{{ $jour }}</span>
                                <span class="text-gray-800">
                                    @if(isset($site->horaires[$jour]) && isset($site->horaires[$jour]['ouverture']) && isset($site->horaires[$jour]['fermeture']) && $site->horaires[$jour]['ouverture'] && $site->horaires[$jour]['fermeture'])
                                        {{ $site->horaires[$jour]['ouverture'] }} - {{ $site->horaires[$jour]['fermeture'] }}
                                    @else
                                        <span class="text-gray-400">Fermé</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Aucun horaire défini</p>
                @endif
            </div>
        </div>

        <!-- Carte -->
        @if($site->latitude && $site->longitude)
            <div class="bg-white rounded-lg shadow overflow-hidden md:col-span-2">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-map mr-2 text-indigo-500"></i> Carte
                    </h2>
                </div>
                <div class="p-4">
                    <div id="map" class="h-64 w-full rounded-lg border border-gray-300"></div>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i> Cette carte nécessite l'intégration d'une bibliothèque de cartographie comme Leaflet ou Google Maps.
                    </p>
                </div>
            </div>
        @endif

        <!-- Employés associés -->
        <div class="bg-white rounded-lg shadow overflow-hidden md:col-span-3">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-users mr-2 text-blue-500"></i> Employés associés ({{ $site->employes->count() }})
                </h2>
            </div>
            <div class="p-4">
                @if($site->employes->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($site->employes as $employe)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-800">{{ $employe->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $employe->email }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Aucun employé associé à ce site</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex justify-between">
        <div>
            <a href="{{ route('admin.sites.edit', $site->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                <i class="fas fa-edit mr-2"></i> Modifier
            </a>
        </div>
        <div>
            <button type="button" onclick="confirmDelete()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-trash mr-2"></i> Supprimer
            </button>
            <form id="delete-form" action="{{ route('admin.sites.destroy', $site->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce site ?')) {
            document.getElementById('delete-form').submit();
        }
    }
    
    // Placeholder for map initialization
    // This would be replaced with actual map initialization code using a library like Leaflet or Google Maps
    document.addEventListener('DOMContentLoaded', function() {
        const mapElement = document.getElementById('map');
        if (mapElement) {
            // Map initialization would go here
        }
    });
</script>
@endpush
@endsection
