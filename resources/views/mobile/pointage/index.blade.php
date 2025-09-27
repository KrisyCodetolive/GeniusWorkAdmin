@extends('mobile.pointage.layout')

@section('title', 'Pointage - ' . $site->nom)

@section('content')
<div class="max-w-md mx-auto">
    <!-- Site Information Card -->
    <div class="card">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-building text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $site->nom }}</h2>
            <p class="text-gray-600 text-sm">{{ $site->adresse }}</p>
            @if($site->ville)
                <p class="text-gray-500 text-sm">{{ $site->ville }}</p>
            @endif
        </div>
        
        <!-- Geofencing Info -->
        @if($site->has_geofencing)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <div class="flex items-center text-blue-700">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span class="text-sm">
                    Géolocalisation requise (rayon: {{ $site->rayon_geofencing }}m)
                </span>
            </div>
        </div>
        @endif
    </div>

    <!-- Authentication Section -->
    <div class="card" id="authSection">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-user-check mr-2 text-blue-600"></i>
            Authentification
        </h3>
        
        <form id="authForm" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="votre.email@entreprise.com"
                >
            </div>
            
            <button type="submit" class="w-full btn-primary" id="authButton">
                <i class="fas fa-fingerprint mr-2"></i>
                S'authentifier avec WebAuthn
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Authentification sécurisée sans mot de passe
            </p>
        </div>
    </div>

    <!-- Location Section (Hidden initially) -->
    <div class="card hidden" id="locationSection">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
            Vérification de position
        </h3>
        
        <div id="locationStatus" class="text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-600">Vérification de votre position...</p>
        </div>
        
        <button id="retryLocationButton" class="w-full btn-secondary hidden mt-4">
            <i class="fas fa-redo mr-2"></i>
            Réessayer la géolocalisation
        </button>
    </div>

    <!-- Pointage Section (Hidden initially) -->
    <div class="card hidden" id="pointageSection">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-clock mr-2 text-purple-600"></i>
            Pointage
        </h3>
        
        <div id="employeeInfo" class="bg-gray-50 rounded-lg p-4 mb-4">
            <!-- Employee info will be populated here -->
        </div>
        
        <div id="currentStatus" class="mb-4">
            <!-- Current status will be populated here -->
        </div>
        
        <button id="pointageButton" class="w-full btn-success">
            <i class="fas fa-play mr-2"></i>
            <span id="pointageButtonText">Pointer l'entrée</span>
        </button>
    </div>

    <!-- Progress Indicator -->
    <div class="flex justify-center mt-6">
        <div class="flex space-x-2">
            <div class="w-3 h-3 rounded-full bg-blue-600" id="step1"></div>
            <div class="w-3 h-3 rounded-full bg-gray-300" id="step2"></div>
            <div class="w-3 h-3 rounded-full bg-gray-300" id="step3"></div>
        </div>
    </div>
    
    <div class="text-center mt-2">
        <p class="text-sm text-gray-500" id="stepText">Étape 1: Authentification</p>
    </div>
</div>

<!-- Hidden data -->
<input type="hidden" id="siteId" value="{{ $site->id }}">
<input type="hidden" id="token" value="{{ $token }}">
<input type="hidden" id="siteData" value="{{ json_encode($qr_info) }}">
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const siteId = document.getElementById('siteId').value;
    const token = document.getElementById('token').value;
    const siteData = JSON.parse(document.getElementById('siteData').value);
    
    let currentEmployeur = null;
    let currentLocation = null;
    let isCheckedIn = false;
    
    // Step management
    function updateStep(step) {
        // Reset all steps
        document.querySelectorAll('[id^="step"]').forEach(el => {
            el.classList.remove('bg-blue-600', 'bg-green-600');
            el.classList.add('bg-gray-300');
        });
        
        // Update current and completed steps
        for (let i = 1; i <= step; i++) {
            const stepEl = document.getElementById(`step${i}`);
            if (i === step) {
                stepEl.classList.remove('bg-gray-300');
                stepEl.classList.add('bg-blue-600');
            } else if (i < step) {
                stepEl.classList.remove('bg-gray-300');
                stepEl.classList.add('bg-green-600');
            }
        }
        
        // Update step text
        const stepTexts = {
            1: 'Étape 1: Authentification',
            2: 'Étape 2: Vérification de position',
            3: 'Étape 3: Pointage'
        };
        document.getElementById('stepText').textContent = stepTexts[step] || '';
    }
    
    // Authentication form handler
    document.getElementById('authForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const authButton = document.getElementById('authButton');
        
        if (!email) {
            utils.showToast('Veuillez saisir votre adresse email', 'error');
            return;
        }
        
        try {
            utils.showLoading();
            authButton.disabled = true;
            authButton.innerHTML = '<div class="spinner mx-auto"></div>';
            
            // Get WebAuthn authentication options
            const optionsResponse = await api.post('/mobile/pointage/webauthn/auth-options', {
                email: email
            });
            
            if (!optionsResponse.success) {
                throw new Error(optionsResponse.message || 'Erreur lors de la génération des options d\'authentification');
            }
            
            // Start WebAuthn authentication
            const credential = await navigator.credentials.get({
                publicKey: {
                    challenge: Uint8Array.from(atob(optionsResponse.data.challenge), c => c.charCodeAt(0)),
                    allowCredentials: optionsResponse.data.allowCredentials?.map(cred => ({
                        id: Uint8Array.from(atob(cred.id), c => c.charCodeAt(0)),
                        type: cred.type,
                        transports: cred.transports
                    })) || [],
                    timeout: optionsResponse.data.timeout,
                    userVerification: optionsResponse.data.userVerification
                }
            });
            
            // Verify authentication
            const verifyResponse = await api.post('/mobile/pointage/webauthn/verify-auth', {
                email: email,
                webauthn_response: {
                    id: credential.id,
                    rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                    response: {
                        authenticatorData: btoa(String.fromCharCode(...new Uint8Array(credential.response.authenticatorData))),
                        clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                        signature: btoa(String.fromCharCode(...new Uint8Array(credential.response.signature))),
                        userHandle: credential.response.userHandle ? btoa(String.fromCharCode(...new Uint8Array(credential.response.userHandle))) : null
                    },
                    type: credential.type
                }
            });
            
            if (!verifyResponse.success) {
                throw new Error(verifyResponse.message || 'Échec de l\'authentification');
            }
            
            currentEmployeur = verifyResponse.data.employeur;
            
            // Show success and move to next step
            utils.showToast('Authentification réussie!', 'success');
            document.getElementById('authSection').classList.add('hidden');
            document.getElementById('locationSection').classList.remove('hidden');
            updateStep(2);
            
            // Start location verification
            await verifyLocation();
            
        } catch (error) {
            console.error('Authentication error:', error);
            utils.showToast(error.message || 'Erreur lors de l\'authentification', 'error');
        } finally {
            utils.hideLoading();
            authButton.disabled = false;
            authButton.innerHTML = '<i class="fas fa-fingerprint mr-2"></i>S\'authentifier avec WebAuthn';
        }
    });
    
    // Location verification
    async function verifyLocation() {
        const locationStatus = document.getElementById('locationStatus');
        const retryButton = document.getElementById('retryLocationButton');
        
        try {
            locationStatus.innerHTML = `
                <div class="spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Vérification de votre position...</p>
            `;
            retryButton.classList.add('hidden');
            
            // Get current position
            const position = await getCurrentPosition();
            currentLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            // Validate location with server
            const locationResponse = await api.post('/mobile/pointage/location/validate', {
                latitude: currentLocation.latitude,
                longitude: currentLocation.longitude,
                site_id: siteId
            });
            
            if (!locationResponse.success) {
                throw new Error(locationResponse.message || 'Position non autorisée');
            }
            
            // Show success
            locationStatus.innerHTML = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-green-600 font-semibold">Position validée</p>
                    <p class="text-sm text-gray-500 mt-1">
                        Distance: ${locationResponse.data.distance}m
                    </p>
                </div>
            `;
            
            // Move to pointage section
            setTimeout(() => {
                document.getElementById('locationSection').classList.add('hidden');
                document.getElementById('pointageSection').classList.remove('hidden');
                updateStep(3);
                setupPointageSection();
            }, 1500);
            
        } catch (error) {
            console.error('Location error:', error);
            locationStatus.innerHTML = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-red-600 font-semibold">Erreur de position</p>
                    <p class="text-sm text-gray-500 mt-1">${error.message}</p>
                </div>
            `;
            retryButton.classList.remove('hidden');
        }
    }
    
    // Get current position with promise
    function getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Géolocalisation non supportée'));
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                resolve,
                (error) => {
                    let message = 'Erreur de géolocalisation';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Accès à la géolocalisation refusé';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Position non disponible';
                            break;
                        case error.TIMEOUT:
                            message = 'Délai d\'attente dépassé';
                            break;
                    }
                    reject(new Error(message));
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        });
    }
    
    // Setup pointage section
    async function setupPointageSection() {
        const employeeInfo = document.getElementById('employeeInfo');
        const currentStatus = document.getElementById('currentStatus');
        const pointageButton = document.getElementById('pointageButton');
        const pointageButtonText = document.getElementById('pointageButtonText');
        
        // Display employee info
        employeeInfo.innerHTML = `
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${currentEmployeur.nom_complet}</p>
                    <p class="text-sm text-gray-500">${currentEmployeur.matricule}</p>
                    <p class="text-sm text-gray-500">${currentEmployeur.entreprise.nom}</p>
                </div>
            </div>
        `;
        
        try {
            // Check current status
            const statusResponse = await api.post('/mobile/pointage/status', {
                employeur_id: currentEmployeur.id
            });
            
            if (statusResponse.success && statusResponse.data.is_checked_in) {
                isCheckedIn = true;
                const presence = statusResponse.data;
                
                currentStatus.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-800 font-semibold">Déjà pointé</p>
                                <p class="text-sm text-green-600">
                                    Entrée: ${utils.formatTime(presence.heure_entree)}
                                </p>
                                <p class="text-sm text-green-600">
                                    Temps écoulé: ${utils.formatDuration(presence.temps_ecoule_minutes)}
                                </p>
                            </div>
                            <div class="status-success">
                                ${presence.statut}
                            </div>
                        </div>
                    </div>
                `;
                
                pointageButton.className = 'w-full btn-danger';
                pointageButtonText.innerHTML = '<i class="fas fa-stop mr-2"></i>Pointer la sortie';
            } else {
                currentStatus.innerHTML = `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <p class="text-blue-800">Prêt pour le pointage d'entrée</p>
                        <p class="text-sm text-blue-600 mt-1">
                            ${utils.formatDate(new Date())}
                        </p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
        
        // Pointage button handler
        pointageButton.addEventListener('click', async function() {
            await performPointage();
        });
    }
    
    // Perform pointage
    async function performPointage() {
        const pointageButton = document.getElementById('pointageButton');
        
        try {
            utils.showLoading();
            pointageButton.disabled = true;
            
            const pointageData = {
                employeur_id: currentEmployeur.id,
                site_id: siteId,
                latitude: currentLocation.latitude,
                longitude: currentLocation.longitude,
                webauthn_verified: true,
                webauthn_credential_id: null // Will be set by WebAuthn service
            };
            
            const response = await api.post('/mobile/pointage/record', pointageData);
            
            if (!response.success) {
                throw new Error(response.message || 'Erreur lors du pointage');
            }
            
            // Redirect to success page
            const params = new URLSearchParams({
                type: response.data.type,
                employeur: currentEmployeur.nom_complet,
                site: siteData.site.nom,
                heures: response.data.minutes_travaillees ? utils.formatDuration(response.data.minutes_travaillees) : '0h00'
            });
            
            window.location.href = `/mobile/pointage/success?${params.toString()}`;
            
        } catch (error) {
            console.error('Pointage error:', error);
            utils.showToast(error.message || 'Erreur lors du pointage', 'error');
        } finally {
            utils.hideLoading();
            pointageButton.disabled = false;
        }
    }
    
    // Retry location button
    document.getElementById('retryLocationButton').addEventListener('click', verifyLocation);
});
</script>
@endpush
