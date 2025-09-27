@extends('mobile.pointage.layout')

@section('title', 'Aide')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Help Header -->
    <div class="card text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-question-circle text-blue-600 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Centre d'aide</h2>
        <p class="text-gray-600">Guide d'utilisation du pointage mobile</p>
    </div>

    <!-- FAQ Sections -->
    <div class="space-y-4">
        <!-- Getting Started -->
        <div class="card">
            <button onclick="toggleSection('getting-started')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-play-circle mr-2 text-green-600"></i>
                    Comment commencer ?
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="getting-started-icon"></i>
            </button>
            
            <div id="getting-started-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="font-medium text-blue-800 mb-2">Étapes pour pointer :</p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700">
                        <li>Scannez le QR code affiché sur votre site de travail</li>
                        <li>Authentifiez-vous avec votre compte Google (WebAuthn)</li>
                        <li>Autorisez l'accès à votre géolocalisation</li>
                        <li>Confirmez votre pointage d'entrée ou de sortie</li>
                    </ol>
                </div>
                
                <p><strong>Prérequis :</strong></p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Navigateur compatible WebAuthn (Chrome, Firefox, Safari récents)</li>
                    <li>Compte Google configuré pour l'authentification</li>
                    <li>Géolocalisation activée sur votre appareil</li>
                    <li>Connexion internet stable</li>
                </ul>
            </div>
        </div>

        <!-- QR Code -->
        <div class="card">
            <button onclick="toggleSection('qr-code')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-qrcode mr-2 text-purple-600"></i>
                    Problèmes avec le QR code
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="qr-code-icon"></i>
            </button>
            
            <div id="qr-code-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="space-y-3">
                    <div>
                        <p class="font-medium text-gray-800 mb-1">QR code illisible :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Nettoyez l'objectif de votre caméra</li>
                            <li>Améliorez l'éclairage</li>
                            <li>Tenez votre téléphone stable</li>
                            <li>Ajustez la distance (15-30 cm recommandé)</li>
                        </ul>
                    </div>
                    
                    <div>
                        <p class="font-medium text-gray-800 mb-1">QR code expiré :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Demandez un nouveau QR code à votre responsable</li>
                            <li>Les QR codes expirent après 24h par sécurité</li>
                            <li>Utilisez le scanner intégré pour réessayer</li>
                        </ul>
                    </div>
                    
                    <div>
                        <p class="font-medium text-gray-800 mb-1">Pas de caméra :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Utilisez la saisie manuelle de l'URL</li>
                            <li>Copiez le lien depuis un autre appareil</li>
                            <li>Contactez votre administrateur</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication -->
        <div class="card">
            <button onclick="toggleSection('auth')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-fingerprint mr-2 text-blue-600"></i>
                    Authentification WebAuthn
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="auth-icon"></i>
            </button>
            
            <div id="auth-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="font-medium text-green-800 mb-2">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Authentification sécurisée sans mot de passe
                    </p>
                    <p class="text-green-700 text-sm">
                        WebAuthn utilise la biométrie ou le PIN de votre appareil pour une sécurité maximale.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <p class="font-medium text-gray-800 mb-1">Première utilisation :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Configurez votre compte Google avec WebAuthn</li>
                            <li>Activez la biométrie sur votre appareil</li>
                            <li>Suivez les instructions de votre navigateur</li>
                        </ul>
                    </div>
                    
                    <div>
                        <p class="font-medium text-gray-800 mb-1">Problèmes d'authentification :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Vérifiez que votre email est correct</li>
                            <li>Assurez-vous d'être enregistré dans le système</li>
                            <li>Utilisez un navigateur compatible</li>
                            <li>Contactez votre administrateur si nécessaire</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="card">
            <button onclick="toggleSection('location')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>
                    Géolocalisation
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="location-icon"></i>
            </button>
            
            <div id="location-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="font-medium text-yellow-800 mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Pourquoi la géolocalisation ?
                    </p>
                    <p class="text-yellow-700 text-sm">
                        Elle garantit que vous pointez bien depuis le site de travail autorisé.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <p class="font-medium text-gray-800 mb-1">Autoriser la géolocalisation :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Cliquez sur "Autoriser" dans votre navigateur</li>
                            <li>Vérifiez les paramètres de votre appareil</li>
                            <li>Assurez-vous d'être connecté à internet</li>
                        </ul>
                    </div>
                    
                    <div>
                        <p class="font-medium text-gray-800 mb-1">Position non autorisée :</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Rapprochez-vous du site de travail</li>
                            <li>Vérifiez que le GPS est activé</li>
                            <li>Attendez quelques secondes pour une meilleure précision</li>
                            <li>Contactez votre responsable si le problème persiste</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="card">
            <button onclick="toggleSection('troubleshooting')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-tools mr-2 text-orange-600"></i>
                    Dépannage
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="troubleshooting-icon"></i>
            </button>
            
            <div id="troubleshooting-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="space-y-4">
                    <div>
                        <p class="font-medium text-gray-800 mb-2">Problèmes courants :</p>
                        
                        <div class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="font-medium text-gray-700 mb-1">Page qui ne charge pas :</p>
                                <ul class="list-disc list-inside space-y-1 ml-4 text-sm">
                                    <li>Vérifiez votre connexion internet</li>
                                    <li>Actualisez la page</li>
                                    <li>Videz le cache de votre navigateur</li>
                                </ul>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="font-medium text-gray-700 mb-1">Erreur lors du pointage :</p>
                                <ul class="list-disc list-inside space-y-1 ml-4 text-sm">
                                    <li>Réessayez après quelques minutes</li>
                                    <li>Vérifiez que vous êtes bien sur le bon site</li>
                                    <li>Contactez votre administrateur</li>
                                </ul>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="font-medium text-gray-700 mb-1">Navigateur non compatible :</p>
                                <ul class="list-disc list-inside space-y-1 ml-4 text-sm">
                                    <li>Utilisez Chrome, Firefox ou Safari récent</li>
                                    <li>Mettez à jour votre navigateur</li>
                                    <li>Activez JavaScript</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="card">
            <button onclick="toggleSection('contact')" class="w-full text-left flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-headset mr-2 text-indigo-600"></i>
                    Besoin d'aide ?
                </h3>
                <i class="fas fa-chevron-down text-gray-400" id="contact-icon"></i>
            </button>
            
            <div id="contact-content" class="hidden mt-4 space-y-3 text-sm text-gray-600">
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <p class="font-medium text-indigo-800 mb-3">Contactez le support :</p>
                    
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-indigo-600 w-5"></i>
                            <span class="ml-2">support@geniuswork.com</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-indigo-600 w-5"></i>
                            <span class="ml-2">+33 1 23 45 67 89</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-indigo-600 w-5"></i>
                            <span class="ml-2">Lun-Ven 9h-18h</span>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-gray-500 text-xs mb-3">
                        Avant de contacter le support, essayez les solutions ci-dessus.
                    </p>
                    
                    <button onclick="generateSupportInfo()" class="btn-secondary text-sm">
                        <i class="fas fa-clipboard mr-2"></i>
                        Générer infos de diagnostic
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions rapides</h3>
        
        <div class="grid grid-cols-2 gap-3">
            <a href="/mobile/pointage/scanner" class="btn-primary text-center text-sm py-2">
                <i class="fas fa-qrcode mb-1 block"></i>
                Scanner QR
            </a>
            
            <button onclick="testCamera()" class="btn-secondary text-sm py-2">
                <i class="fas fa-camera mb-1 block"></i>
                Test caméra
            </button>
            
            <button onclick="testLocation()" class="btn-secondary text-sm py-2">
                <i class="fas fa-map-marker-alt mb-1 block"></i>
                Test GPS
            </button>
            
            <button onclick="clearCache()" class="btn-secondary text-sm py-2">
                <i class="fas fa-trash mb-1 block"></i>
                Vider cache
            </button>
        </div>
    </div>
</div>

<!-- Support Info Modal -->
<div id="supportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full max-h-96 overflow-y-auto">
        <div class="text-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Informations de diagnostic</h3>
        </div>
        
        <div id="supportInfo" class="text-sm text-gray-600 space-y-2 mb-4">
            <!-- Support info will be populated here -->
        </div>
        
        <div class="flex space-x-3">
            <button onclick="copySupportInfo()" class="flex-1 btn-primary text-sm">
                <i class="fas fa-copy mr-2"></i>
                Copier
            </button>
            <button onclick="closeModal('supportModal')" class="flex-1 btn-secondary text-sm">
                Fermer
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle section function
    window.toggleSection = function(sectionId) {
        const content = document.getElementById(`${sectionId}-content`);
        const icon = document.getElementById(`${sectionId}-icon`);
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    };
    
    // Test camera function
    window.testCamera = async function() {
        try {
            utils.showLoading();
            
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            stream.getTracks().forEach(track => track.stop());
            
            utils.showToast('Caméra fonctionnelle !', 'success');
        } catch (error) {
            utils.showToast('Erreur caméra: ' + error.message, 'error');
        } finally {
            utils.hideLoading();
        }
    };
    
    // Test location function
    window.testLocation = function() {
        utils.showLoading();
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                utils.hideLoading();
                utils.showToast(`GPS OK: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}`, 'success');
            },
            (error) => {
                utils.hideLoading();
                let message = 'Erreur GPS';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Accès GPS refusé';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Position non disponible';
                        break;
                    case error.TIMEOUT:
                        message = 'Délai GPS dépassé';
                        break;
                }
                utils.showToast(message, 'error');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };
    
    // Clear cache function
    window.clearCache = function() {
        try {
            localStorage.clear();
            sessionStorage.clear();
            
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => caches.delete(name));
                });
            }
            
            utils.showToast('Cache vidé avec succès', 'success');
        } catch (error) {
            utils.showToast('Erreur lors du vidage du cache', 'error');
        }
    };
    
    // Generate support info
    window.generateSupportInfo = function() {
        const info = {
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            screen: `${screen.width}x${screen.height}`,
            viewport: `${window.innerWidth}x${window.innerHeight}`,
            language: navigator.language,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            webauthn: 'credentials' in navigator,
            geolocation: 'geolocation' in navigator,
            camera: 'mediaDevices' in navigator,
            localStorage: typeof(Storage) !== "undefined"
        };
        
        const infoHtml = Object.entries(info).map(([key, value]) => 
            `<div><strong>${key}:</strong> ${value}</div>`
        ).join('');
        
        document.getElementById('supportInfo').innerHTML = infoHtml;
        document.getElementById('supportModal').classList.remove('hidden');
    };
    
    // Copy support info
    window.copySupportInfo = function() {
        const info = document.getElementById('supportInfo').innerText;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(info).then(() => {
                utils.showToast('Informations copiées', 'success');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = info;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            utils.showToast('Informations copiées', 'success');
        }
    };
    
    // Close modal function
    window.closeModal = function(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    };
});
</script>
@endpush
