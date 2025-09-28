<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pointage Mobile') - {{ $app_name ?? 'GeniusWork' }}</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pointage Mobile">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/logo2.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'bounce-slow': 'bounce 3s infinite',
                        'fadeIn': 'fadeIn 0.5s ease-in-out',
                        'slideInRight': 'slideInRight 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior-y: none;
        }
        
        /* Prevent zoom on input focus */
        input[type="email"], input[type="text"], input[type="password"], select, textarea {
            font-size: 16px;
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid rgba(99, 102, 241, 0.2);
            border-top: 3px solid #6366f1;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Pulse animation */
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        
        /* Custom button styles */
        .btn-primary {
            @apply bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-4 focus:ring-primary-300 focus:outline-none;
        }
        
        .btn-secondary {
            @apply bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-sm hover:shadow border border-gray-200;
        }
        
        .btn-success {
            @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-4 focus:ring-green-300 focus:outline-none;
        }
        
        .btn-danger {
            @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:ring-4 focus:ring-red-300 focus:outline-none;
        }
        
        /* Card styles */
        .card {
            @apply bg-white rounded-2xl shadow-md p-6 mb-6 border border-gray-100;
        }
        
        /* Status indicators */
        .status-success {
            @apply bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium inline-flex items-center;
        }
        
        .status-warning {
            @apply bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium inline-flex items-center;
        }
        
        .status-error {
            @apply bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium inline-flex items-center;
        }
        
        /* Glassmorphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* App logo animation */
        .logo-pulse {
            animation: logo-pulse 3s ease-in-out infinite;
        }
        
        @keyframes logo-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-primary-700 to-primary-900 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 logo-pulse">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ $app_name ?? 'GeniusWork' }}</h1>
                        <p class="text-indigo-200 text-sm">Pointage Mobile</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('mobile.pointage.help') }}" class="text-white hover:text-indigo-200 transition-colors" title="Aide">
                        <i class="fas fa-question-circle text-xl"></i>
                    </a>
                    
                    @if(isset($showBackButton) && $showBackButton)
                    <button onclick="history.back()" class="text-white hover:text-indigo-200 transition-colors p-2 rounded-full hover:bg-primary-800">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-6 mt-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0 flex items-center">
                    <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="w-8 h-8 mr-3">
                    <span class="font-semibold">{{ $app_name ?? 'GeniusWork' }}</span>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ $app_name ?? 'GeniusWork' }}. Tous droits réservés.</p>
                    <p class="text-xs text-gray-500 mt-1">Version {{ config('app.version', '1.0') }}</p>
                </div>
            </div>
        </div>
    </footer>
    
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Scripts -->
    <!-- WebAuth Modal -->    
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" id="webAuthModal">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" id="webAuthModalTitle">Authentification biométrique</h3>
            
            <div id="webAuthRegistrationUI" class="hidden">
                <p class="mb-4">Configurez l'authentification biométrique pour vous connecter plus facilement à l'avenir.</p>
                <div class="flex justify-center mb-4">
                    <i class="fas fa-fingerprint text-5xl text-primary-600"></i>
                </div>
                <p class="text-sm text-gray-600 mb-4">Suivez les instructions de votre appareil pour enregistrer votre empreinte digitale, visage ou clé de sécurité.</p>
            </div>
            
            <div id="webAuthLoginUI" class="hidden">
                <p class="mb-4">Utilisez votre authentification biométrique pour vous connecter.</p>
                <div class="flex justify-center mb-4">
                    <i class="fas fa-fingerprint text-5xl text-primary-600 pulse"></i>
                </div>
                <p class="text-sm text-gray-600 mb-4">Suivez les instructions de votre appareil.</p>
            </div>
            
            <div id="webAuthNoCredentialsUI" class="hidden">
                <p class="mb-4">Vous n'avez pas encore configuré d'authentification biométrique.</p>
                <div class="flex justify-center mb-4">
                    <i class="fas fa-exclamation-circle text-5xl text-yellow-500"></i>
                </div>
                <p class="text-sm text-gray-600 mb-4">Souhaitez-vous configurer l'authentification biométrique maintenant?</p>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button id="webAuthCancelBtn" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
                <button id="webAuthActionBtn" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Continuer
                </button>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token setup
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // API Base URL
        window.apiBaseUrl = '{{ url("/api") }}';
        
        // Current domain for WebAuthn
        window.rpId = window.location.hostname;
        
        // Utility functions
        window.utils = {
            showLoading: function() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                } else {
                    console.warn('Loading overlay element not found');
                }
            },
            
            hideLoading: function() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                } else {
                    console.warn('Loading overlay element not found');
                }
            },
            
            showToast: function(message, type = 'info') {
                const toast = document.createElement('div');
                const bgColor = {
                    'success': 'bg-green-500',
                    'error': 'bg-red-500',
                    'warning': 'bg-yellow-500',
                    'info': 'bg-blue-500'
                }[type] || 'bg-blue-500';
                
                toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-x-full`;
                toast.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span>${message}</span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.getElementById('toastContainer').appendChild(toast);
                
                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            },
            
            formatTime: function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString('fr-FR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            },
            
            formatDate: function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR', { 
                    weekday: 'long',
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            },
            
            formatDuration: function(minutes) {
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                return `${hours}h${mins.toString().padStart(2, '0')}`;
            }
        };
        
        // API helper functions
        window.api = {
            post: async function(endpoint, data = {}) {
                try {
                    const response = await fetch(`${window.apiBaseUrl}${endpoint}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                    
                    return await response.json();
                } catch (error) {
                    console.error('API Error:', error);
                    throw error;
                }
            },
            
            get: async function(endpoint) {
                try {
                    const response = await fetch(`${window.apiBaseUrl}${endpoint}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    });
                    
                    return await response.json();
                } catch (error) {
                    console.error('API Error:', error);
                    throw error;
                }
            }
        };
        
        // Auto-fill email from browser credentials
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput) {
                // Set autofill attributes
                emailInput.setAttribute('autocomplete', 'email');
                emailInput.setAttribute('autofocus', 'true');
                
                // Try to get stored credentials if available
                if (navigator.credentials && navigator.credentials.get) {
                    navigator.credentials.get({password: true, mediation: 'silent'})
                        .then(function(credential) {
                            if (credential && credential.id) {
                                emailInput.value = credential.id;
                                console.log('Email auto-filled from credentials');
                            }
                        })
                        .catch(function(error) {
                            console.log('Credential Management API error:', error);
                        });
                }
                
                // Focus the input to trigger browser autofill
                setTimeout(function() {
                    if (!emailInput.value) {
                        emailInput.focus();
                        setTimeout(() => emailInput.blur(), 100);
                    }
                }, 500);
            }
        });
        
        // WebAuthn Registration Implementation
        window.webAuthRegistration = {
            register: async function() {
                try {
                    window.utils.showLoading();
                    
                    // Check if WebAuthn is supported
                    if (!window.PublicKeyCredential) {
                        throw new Error('WebAuthn n\'est pas supporté par ce navigateur');
                    }
                    
                    // 1. Request challenge from server
                    const response = await fetch(`${window.apiBaseUrl}/webauthn/register/options`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Impossible d\'obtenir les options d\'enregistrement');
                    }
                    
                    const options = await response.json();
                    
                    // 2. Convert base64 strings to ArrayBuffer
                    options.publicKey.challenge = this._base64UrlToArrayBuffer(options.publicKey.challenge);
                    options.publicKey.user.id = this._base64UrlToArrayBuffer(options.publicKey.user.id);
                    
                    if (options.publicKey.excludeCredentials) {
                        for (let cred of options.publicKey.excludeCredentials) {
                            cred.id = this._base64UrlToArrayBuffer(cred.id);
                        }
                    }
                    
                    // 3. Create credential
                    const credential = await navigator.credentials.create({
                        publicKey: options.publicKey
                    });
                    
                    // 4. Prepare credential for server
                    const credentialForServer = {
                        id: credential.id,
                        rawId: this._arrayBufferToBase64Url(credential.rawId),
                        type: credential.type,
                        response: {
                            clientDataJSON: this._arrayBufferToBase64Url(credential.response.clientDataJSON),
                            attestationObject: this._arrayBufferToBase64Url(credential.response.attestationObject)
                        }
                    };
                    
                    // 5. Send to server for verification
                    const verificationResponse = await fetch(`${window.apiBaseUrl}/webauthn/register/verify`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify(credentialForServer)
                    });
                    
                    if (!verificationResponse.ok) {
                        throw new Error('Impossible de vérifier l\'enregistrement');
                    }
                    
                    window.utils.showToast('Authentification biométrique activée avec succès', 'success');
                    return true;
                } catch (error) {
                    console.error('WebAuthn registration error:', error);
                    window.utils.showToast(error.message || 'Échec de l\'enregistrement biométrique', 'error');
                    return false;
                } finally {
                    window.utils.hideLoading();
                }
            },
            
            // Helper functions for encoding/decoding
            _base64UrlToArrayBuffer: function(base64Url) {
                const padding = '='.repeat((4 - (base64Url.length % 4)) % 4);
                const base64 = (base64Url + padding)
                    .replace(/-/g, '+')
                    .replace(/_/g, '/');
                const rawData = window.atob(base64);
                const buffer = new Uint8Array(rawData.length);
                
                for (let i = 0; i < rawData.length; i++) {
                    buffer[i] = rawData.charCodeAt(i);
                }
                
                return buffer.buffer;
            },
            
            _arrayBufferToBase64Url: function(arrayBuffer) {
                const bytes = new Uint8Array(arrayBuffer);
                let binary = '';
                
                for (let i = 0; i < bytes.byteLength; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                
                const base64 = window.btoa(binary);
                return base64
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=/g, '');
            }
        };
        
        // WebAuthn Authentication Implementation
        window.webAuthLogin = {
            login: async function() {
                try {
                    window.utils.showLoading();
                    
                    // Check if WebAuthn is supported
                    if (!window.PublicKeyCredential) {
                        throw new Error('WebAuthn n\'est pas supporté par ce navigateur');
                    }
                    
                    // 1. Request challenge from server
                    const response = await fetch(`${window.apiBaseUrl}/webauthn/login/options`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Impossible d\'obtenir les options d\'authentification');
                    }
                    
                    const options = await response.json();
                    
                    // Check if user has registered credentials
                    if (!options.publicKey.allowCredentials || options.publicKey.allowCredentials.length === 0) {
                        window.webAuthUI.showNoCredentials();
                        return false;
                    }
                    
                    // 2. Convert base64 strings to ArrayBuffer
                    options.publicKey.challenge = webAuthRegistration._base64UrlToArrayBuffer(options.publicKey.challenge);
                    
                    for (let cred of options.publicKey.allowCredentials) {
                        cred.id = webAuthRegistration._base64UrlToArrayBuffer(cred.id);
                    }
                    
                    // 3. Get credential
                    const credential = await navigator.credentials.get({
                        publicKey: options.publicKey
                    });
                    
                    // 4. Prepare credential for server
                    const credentialForServer = {
                        id: credential.id,
                        rawId: webAuthRegistration._arrayBufferToBase64Url(credential.rawId),
                        type: credential.type,
                        response: {
                            clientDataJSON: webAuthRegistration._arrayBufferToBase64Url(credential.response.clientDataJSON),
                            authenticatorData: webAuthRegistration._arrayBufferToBase64Url(credential.response.authenticatorData),
                            signature: webAuthRegistration._arrayBufferToBase64Url(credential.response.signature),
                            userHandle: credential.response.userHandle ? webAuthRegistration._arrayBufferToBase64Url(credential.response.userHandle) : null
                        }
                    };
                    
                    // 5. Send to server for verification
                    const verificationResponse = await fetch(`${window.apiBaseUrl}/webauthn/login/verify`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify(credentialForServer)
                    });
                    
                    if (!verificationResponse.ok) {
                        throw new Error('Impossible de vérifier l\'authentification');
                    }
                    
                    window.utils.showToast('Authentification réussie', 'success');
                    return true;
                } catch (error) {
                    console.error('WebAuthn authentication error:', error);
                    window.utils.showToast(error.message || 'Échec de l\'authentification biométrique', 'error');
                    return false;
                } finally {
                    window.utils.hideLoading();
                }
            }
        };
        
        // WebAuth UI Controller
        window.webAuthUI = {
            showRegistration: function() {
                document.getElementById('webAuthModalTitle').textContent = 'Configuration biométrique';
                document.getElementById('webAuthRegistrationUI').classList.remove('hidden');
                document.getElementById('webAuthLoginUI').classList.add('hidden');
                document.getElementById('webAuthNoCredentialsUI').classList.add('hidden');
                document.getElementById('webAuthActionBtn').textContent = 'Configurer';
                document.getElementById('webAuthModal').classList.remove('hidden');
                
                document.getElementById('webAuthActionBtn').onclick = async () => {
                    const success = await window.webAuthRegistration.register();
                    if (success) {
                        this.hideModal();
                    }
                };
                
                document.getElementById('webAuthCancelBtn').onclick = () => {
                    this.hideModal();
                };
            },
            
            showLogin: function() {
                document.getElementById('webAuthModalTitle').textContent = 'Authentification biométrique';
                document.getElementById('webAuthRegistrationUI').classList.add('hidden');
                document.getElementById('webAuthLoginUI').classList.remove('hidden');
                document.getElementById('webAuthNoCredentialsUI').classList.add('hidden');
                document.getElementById('webAuthActionBtn').textContent = 'S\'authentifier';
                document.getElementById('webAuthModal').classList.remove('hidden');
                
                document.getElementById('webAuthActionBtn').onclick = async () => {
                    const success = await window.webAuthLogin.login();
                    if (success) {
                        this.hideModal();
                        // Redirect or update UI as needed
                        window.location.reload();
                    }
                };
                
                document.getElementById('webAuthCancelBtn').onclick = () => {
                    this.hideModal();
                };
            },
            
            showNoCredentials: function() {
                document.getElementById('webAuthModalTitle').textContent = 'Authentification biométrique';
                document.getElementById('webAuthRegistrationUI').classList.add('hidden');
                document.getElementById('webAuthLoginUI').classList.add('hidden');
                document.getElementById('webAuthNoCredentialsUI').classList.remove('hidden');
                document.getElementById('webAuthActionBtn').textContent = 'Configurer';
                document.getElementById('webAuthModal').classList.remove('hidden');
                
                document.getElementById('webAuthActionBtn').onclick = () => {
                    this.hideModal();
                    this.showRegistration();
                };
                
                document.getElementById('webAuthCancelBtn').onclick = () => {
                    this.hideModal();
                };
            },
            
            hideModal: function() {
                document.getElementById('webAuthModal').classList.add('hidden');
            }
        };
        
        // Check WebAuthn Support
        window.checkWebAuthnSupport = async function() {
            // Check if WebAuthn is supported
            if (!window.PublicKeyCredential) {
                return false;
            }
            
            try {
                // Check if user has registered credentials
                const response = await fetch(`${window.apiBaseUrl}/webauthn/credentials/check`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
                
                if (!response.ok) {
                    return false;
                }
                
                const result = await response.json();
                return result.hasCredentials;
            } catch (error) {
                console.error('Error checking WebAuthn credentials:', error);
                return false;
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>
