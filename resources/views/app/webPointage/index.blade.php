@extends('layouts.app')

@section('title', 'Pointage - ' . $entreprise->nom)

@section('styles')
<style>
    .pointage-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .pointage-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .pointage-btn {
        transition: all 0.2s ease;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .pointage-btn:hover {
        transform: scale(1.05);
    }
    
    .btn-entree {
        background-color: #4CAF50;
        color: white;
    }
    
    .btn-sortie {
        background-color: #F44336;
        color: white;
    }
    
    .btn-pause {
        background-color: #FF9800;
        color: white;
    }
    
    .status-indicator {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .status-online {
        background-color: #4CAF50;
    }
    
    .status-offline {
        background-color: #F44336;
    }
    
    .status-pause {
        background-color: #FF9800;
    }
</style>
@endsection

@section('content')
<div class="container py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Entreprise Info -->
        <div class="bg-white pointage-card p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    @if($entreprise->logo)
                        <img src="{{ asset('storage/' . $entreprise->logo) }}" alt="{{ $entreprise->nom }}" class="h-16 w-16 object-contain mr-4">
                    @else
                        <div class="h-16 w-16 bg-gray-200 flex items-center justify-center rounded-full mr-4">
                            <span class="text-2xl text-gray-500">{{ substr($entreprise->nom, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $entreprise->nom }}</h1>
                        <p class="text-gray-600">Site: {{ \App\Models\Site::find($site_id)->nom ?? 'Non spécifié' }}</p>
                    </div>
                </div>
                <div>
                    <span id="current-time" class="text-xl font-semibold text-gray-700"></span>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center mb-2">
                    <span class="status-indicator" id="status-indicator"></span>
                    <span id="status-text" class="font-medium">Chargement...</span>
                </div>
                <p class="text-sm text-gray-600">Dernière activité: <span id="last-activity">Chargement...</span></p>
            </div>
        </div>
        
        <!-- Pointage Buttons -->
        <div class="bg-white pointage-card p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Enregistrer un pointage</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <button id="btn-entree" class="pointage-btn btn-entree py-4 px-6 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Entrée
                </button>
                
                <button id="btn-sortie" class="pointage-btn btn-sortie py-4 px-6 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sortie
                </button>
                
                <button id="btn-pause-debut" class="pointage-btn btn-pause py-4 px-6 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Début de pause
                </button>
                
                <button id="btn-pause-fin" class="pointage-btn btn-pause py-4 px-6 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Fin de pause
                </button>
            </div>
        </div>
        
        <!-- Informations -->
        <div class="bg-white pointage-card p-6">
            <h2 class="text-xl font-semibold mb-4">Informations</h2>
            
            <div class="space-y-3">
                <div class="flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span id="geolocation-status">Localisation en cours...</span>
                </div>
                
                <div class="flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                    </svg>
                    <span id="webauthn-status">Authentification biométrique en cours...</span>
                </div>
                
                <div class="flex items-center text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Heures de travail aujourd'hui: <span id="work-hours">Calcul en cours...</span></span>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('webPointage.historique') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    Voir l'historique des pointages
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables
        const token = "{{ $token }}";
        let latitude = null;
        let longitude = null;
        let webauthnCredential = null;
        
        // Éléments DOM
        const btnEntree = document.getElementById('btn-entree');
        const btnSortie = document.getElementById('btn-sortie');
        const btnPauseDebut = document.getElementById('btn-pause-debut');
        const btnPauseFin = document.getElementById('btn-pause-fin');
        const geolocationStatus = document.getElementById('geolocation-status');
        const webauthnStatus = document.getElementById('webauthn-status');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const lastActivity = document.getElementById('last-activity');
        const workHours = document.getElementById('work-hours');
        const currentTime = document.getElementById('current-time');
        
        // Mise à jour de l'heure actuelle
        function updateCurrentTime() {
            const now = new Date();
            currentTime.textContent = now.toLocaleTimeString();
        }
        
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
        
        // Obtenir la géolocalisation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                    geolocationStatus.textContent = "Localisation obtenue";
                    geolocationStatus.classList.add('text-green-600');
                },
                function(error) {
                    geolocationStatus.textContent = "Erreur de localisation: " + error.message;
                    geolocationStatus.classList.add('text-red-600');
                }
            );
        } else {
            geolocationStatus.textContent = "La géolocalisation n'est pas prise en charge par votre navigateur";
            geolocationStatus.classList.add('text-red-600');
        }
        
        // Vérifier la prise en charge de WebAuthn
        if (window.PublicKeyCredential) {
            // Simuler l'obtention d'informations d'identification WebAuthn
            // Dans une implémentation réelle, vous utiliseriez l'API WebAuthn
            setTimeout(() => {
                webauthnCredential = "simulated-credential-" + Math.random().toString(36).substring(2);
                webauthnStatus.textContent = "Authentification biométrique disponible";
                webauthnStatus.classList.add('text-green-600');
            }, 1500);
        } else {
            webauthnStatus.textContent = "L'authentification biométrique n'est pas prise en charge par votre navigateur";
            webauthnStatus.classList.add('text-red-600');
        }
        
        // Simuler le chargement des données utilisateur
        setTimeout(() => {
            // Simuler un statut aléatoire pour la démonstration
            const statuses = ['online', 'offline', 'pause'];
            const statusLabels = ['Présent', 'Absent', 'En pause'];
            const randomStatus = Math.floor(Math.random() * statuses.length);
            
            statusIndicator.classList.add('status-' + statuses[randomStatus]);
            statusText.textContent = statusLabels[randomStatus];
            
            lastActivity.textContent = new Date().toLocaleString();
            workHours.textContent = "6h 30min";
        }, 1000);
        
        // Fonction pour effectuer un pointage
        function effectuerPointage(type) {
            if (!latitude || !longitude) {
                alert("La géolocalisation est requise pour effectuer un pointage");
                return;
            }
            
            if (!webauthnCredential) {
                alert("L'authentification biométrique est requise pour effectuer un pointage");
                return;
            }
            
            // Afficher un indicateur de chargement
            const button = document.getElementById('btn-' + type.replace('_', '-'));
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Traitement...';
            
            // Envoyer la requête au serveur
            fetch('{{ route("webPointage.pointage") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    token: token,
                    latitude: latitude,
                    longitude: longitude,
                    webauthn_credential: webauthnCredential,
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                button.innerHTML = originalText;
                
                if (data.success) {
                    // Mettre à jour l'interface utilisateur
                    switch(type) {
                        case 'entree':
                            statusIndicator.className = 'status-indicator status-online';
                            statusText.textContent = 'Présent';
                            break;
                        case 'sortie':
                            statusIndicator.className = 'status-indicator status-offline';
                            statusText.textContent = 'Absent';
                            break;
                        case 'pause_debut':
                            statusIndicator.className = 'status-indicator status-pause';
                            statusText.textContent = 'En pause';
                            break;
                        case 'pause_fin':
                            statusIndicator.className = 'status-indicator status-online';
                            statusText.textContent = 'Présent';
                            break;
                    }
                    
                    lastActivity.textContent = new Date().toLocaleString();
                    
                    // Afficher un message de succès
                    alert("Pointage enregistré avec succès");
                } else {
                    // Afficher un message d'erreur
                    alert("Erreur: " + data.message);
                }
            })
            .catch(error => {
                button.innerHTML = originalText;
                alert("Erreur de connexion: " + error.message);
            });
        }
        
        // Ajouter des écouteurs d'événements aux boutons
        btnEntree.addEventListener('click', () => effectuerPointage('entree'));
        btnSortie.addEventListener('click', () => effectuerPointage('sortie'));
        btnPauseDebut.addEventListener('click', () => effectuerPointage('pause_debut'));
        btnPauseFin.addEventListener('click', () => effectuerPointage('pause_fin'));
    });
</script>
@endsection
