@extends('layouts.app')

@section('title', 'Pointage')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Pointage</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Pointage</li>
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
        <div class="col-xl-4 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-clock me-1"></i>
                    Statut actuel
                </div>
                <div class="card-body text-center">
                    @if($estPresent)
                        <div class="mb-3">
                            <span class="badge bg-success p-3 fs-5">
                                <i class="fas fa-check-circle me-2"></i> Présent
                            </span>
                        </div>
                    @elseif($estEnPause)
                        <div class="mb-3">
                            <span class="badge bg-warning p-3 fs-5">
                                <i class="fas fa-pause-circle me-2"></i> En pause
                            </span>
                        </div>
                    @else
                        <div class="mb-3">
                            <span class="badge bg-danger p-3 fs-5">
                                <i class="fas fa-times-circle me-2"></i> Absent
                            </span>
                        </div>
                    @endif

                    @if($dernierPointage)
                        <p class="mb-1">Dernier pointage: <strong>{{ $dernierPointage->date_heure->format('d/m/Y H:i:s') }}</strong></p>
                        <p class="mb-1">Type: <strong>
                            @if($dernierPointage->type == 'entree')
                                <span class="text-success">Entrée</span>
                            @elseif($dernierPointage->type == 'sortie')
                                <span class="text-danger">Sortie</span>
                            @elseif($dernierPointage->type == 'pause_debut')
                                <span class="text-warning">Début de pause</span>
                            @elseif($dernierPointage->type == 'pause_fin')
                                <span class="text-info">Fin de pause</span>
                            @endif
                        </strong></p>
                        @if($dernierPointage->site)
                            <p class="mb-1">Site: <strong>{{ $dernierPointage->site->nom }}</strong></p>
                        @endif
                    @else
                        <p>Aucun pointage enregistré</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clock me-1"></i>
                    Enregistrer un pointage
                </div>
                <div class="card-body">
                    <form action="{{ route('pointage.store') }}" method="POST" enctype="multipart/form-data" id="pointageForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type" class="form-label">Type de pointage</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        @if(!$estPresent && !$estEnPause)
                                            <option value="entree">Entrée</option>
                                        @elseif($estPresent && !$estEnPause)
                                            <option value="sortie">Sortie</option>
                                            <option value="pause_debut">Début de pause</option>
                                        @elseif($estEnPause)
                                            <option value="pause_fin">Fin de pause</option>
                                        @endif
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="site_id" class="form-label">Site</label>
                                    <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                                        <option value="">Sélectionner un site</option>
                                        @foreach($sites as $site)
                                            <option value="{{ $site->id }}" data-geofencing="{{ $site->has_geofencing ? '1' : '0' }}">{{ $site->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('site_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="methode_pointage_id" class="form-label">Méthode de pointage</label>
                                    <select class="form-select @error('methode_pointage_id') is-invalid @enderror" id="methode_pointage_id" name="methode_pointage_id" required>
                                        @foreach($methodesPointage as $methode)
                                            <option value="{{ $methode->id }}" data-geolocation="{{ $methode->requiert_geolocation ? '1' : '0' }}" data-photo="{{ $methode->requiert_photo ? '1' : '0' }}" data-signature="{{ $methode->requiert_signature ? '1' : '0' }}">{{ $methode->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('methode_pointage_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="commentaire" class="form-label">Commentaire</label>
                                    <input type="text" class="form-control @error('commentaire') is-invalid @enderror" id="commentaire" name="commentaire" value="{{ old('commentaire') }}">
                                    @error('commentaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3" id="geolocationSection" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label">Géolocalisation</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="getLocationBtn">
                                            <i class="fas fa-map-marker-alt me-1"></i> Obtenir ma position
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" placeholder="Latitude" readonly value="{{ old('latitude') }}">
                                            @error('latitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" placeholder="Longitude" readonly value="{{ old('longitude') }}">
                                            @error('longitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div id="locationStatus" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3" id="photoSection" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="photo" class="form-label">Photo</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" capture="user">
                                        <button class="btn btn-outline-secondary" type="button" id="capturePhotoBtn">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="photoPreview" class="mt-2 text-center" style="display: none;">
                                        <img id="previewImage" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3" id="signatureSection" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="signature" class="form-label">Signature</label>
                                    <div class="border rounded p-3 mb-2">
                                        <canvas id="signatureCanvas" width="100%" height="200" style="border: 1px solid #ddd; width: 100%; height: 200px;"></canvas>
                                    </div>
                                    <input type="hidden" id="signature" name="signature">
                                    @error('signature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-secondary" id="clearSignatureBtn">
                                            <i class="fas fa-eraser me-1"></i> Effacer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pointage.historique') }}" class="btn btn-secondary">
                                <i class="fas fa-history me-1"></i> Voir l'historique
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Enregistrer le pointage
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const methodeSelect = document.getElementById('methode_pointage_id');
        const siteSelect = document.getElementById('site_id');
        const geolocationSection = document.getElementById('geolocationSection');
        const photoSection = document.getElementById('photoSection');
        const signatureSection = document.getElementById('signatureSection');
        const getLocationBtn = document.getElementById('getLocationBtn');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const locationStatus = document.getElementById('locationStatus');
        const photoInput = document.getElementById('photo');
        const capturePhotoBtn = document.getElementById('capturePhotoBtn');
        const photoPreview = document.getElementById('photoPreview');
        const previewImage = document.getElementById('previewImage');
        const signatureCanvas = document.getElementById('signatureCanvas');
        const signatureInput = document.getElementById('signature');
        const clearSignatureBtn = document.getElementById('clearSignatureBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        // Initialiser le pad de signature
        const canvas = document.getElementById('signatureCanvas');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        // Redimensionner le canvas
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();
        
        // Effacer la signature
        clearSignatureBtn.addEventListener('click', function() {
            signaturePad.clear();
            signatureInput.value = '';
        });
        
        // Vérifier les exigences de la méthode de pointage
        function checkMethodRequirements() {
            const selectedMethod = methodeSelect.options[methodeSelect.selectedIndex];
            const requiresGeolocation = selectedMethod.dataset.geolocation === '1';
            const requiresPhoto = selectedMethod.dataset.photo === '1';
            const requiresSignature = selectedMethod.dataset.signature === '1';
            
            // Vérifier si le site sélectionné a le geofencing activé
            const selectedSite = siteSelect.options[siteSelect.selectedIndex];
            const siteHasGeofencing = selectedSite && selectedSite.dataset.geofencing === '1';
            
            // Afficher/masquer les sections en fonction des exigences
            geolocationSection.style.display = (requiresGeolocation || siteHasGeofencing) ? 'block' : 'none';
            photoSection.style.display = requiresPhoto ? 'block' : 'none';
            signatureSection.style.display = requiresSignature ? 'block' : 'none';
        }
        
        methodeSelect.addEventListener('change', checkMethodRequirements);
        siteSelect.addEventListener('change', checkMethodRequirements);
        
        // Initialiser l'affichage en fonction des sélections initiales
        checkMethodRequirements();
        
        // Obtenir la géolocalisation
        getLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                locationStatus.innerHTML = '<div class="alert alert-info">Récupération de votre position en cours...</div>';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latitudeInput.value = position.coords.latitude;
                        longitudeInput.value = position.coords.longitude;
                        locationStatus.innerHTML = '<div class="alert alert-success">Position récupérée avec succès!</div>';
                    },
                    function(error) {
                        let errorMessage = 'Erreur lors de la récupération de la position.';
                        
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Vous avez refusé l\'accès à votre position.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Votre position n\'est pas disponible.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'La demande de position a expiré.';
                                break;
                        }
                        
                        locationStatus.innerHTML = '<div class="alert alert-danger">' + errorMessage + '</div>';
                    }
                );
            } else {
                locationStatus.innerHTML = '<div class="alert alert-danger">La géolocalisation n\'est pas prise en charge par votre navigateur.</div>';
            }
        });
        
        // Prévisualiser la photo
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Capturer une photo (pour les appareils mobiles)
        capturePhotoBtn.addEventListener('click', function() {
            photoInput.click();
        });
        
        // Soumettre le formulaire
        document.getElementById('pointageForm').addEventListener('submit', function(e) {
            // Vérifier si la signature est requise
            const selectedMethod = methodeSelect.options[methodeSelect.selectedIndex];
            const requiresSignature = selectedMethod.dataset.signature === '1';
            
            if (requiresSignature && signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Veuillez signer avant de soumettre le formulaire.');
                return;
            }
            
            // Enregistrer la signature si elle existe
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL();
            }
            
            // Vérifier la géolocalisation si nécessaire
            const requiresGeolocation = selectedMethod.dataset.geolocation === '1';
            const selectedSite = siteSelect.options[siteSelect.selectedIndex];
            const siteHasGeofencing = selectedSite && selectedSite.dataset.geofencing === '1';
            
            if ((requiresGeolocation || siteHasGeofencing) && (!latitudeInput.value || !longitudeInput.value)) {
                e.preventDefault();
                alert('Veuillez récupérer votre position avant de soumettre le formulaire.');
                return;
            }
        });
    });
</script>
@endsection
