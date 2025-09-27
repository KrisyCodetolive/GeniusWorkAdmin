<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pointage Mobile') - {{ $app_name ?? 'GeniusWork' }}</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Pointage Mobile">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* Prevent zoom on input focus */
        input[type="email"], input[type="text"], input[type="password"], select, textarea {
            font-size: 16px;
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
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
            @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200;
        }
        
        .btn-secondary {
            @apply bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200;
        }
        
        .btn-success {
            @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200;
        }
        
        .btn-danger {
            @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200;
        }
        
        /* Card styles */
        .card {
            @apply bg-white rounded-lg shadow-md p-6 mb-4;
        }
        
        /* Status indicators */
        .status-success {
            @apply bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium;
        }
        
        .status-warning {
            @apply bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium;
        }
        
        .status-error {
            @apply bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-clock text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">{{ $app_name ?? 'GeniusWork' }}</h1>
                        <p class="text-blue-200 text-sm">Pointage Mobile</p>
                    </div>
                </div>
                
                @if(isset($showBackButton) && $showBackButton)
                <button onclick="history.back()" class="text-white hover:text-blue-200 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-300 text-sm">
                © {{ date('Y') }} {{ $app_name ?? 'GeniusWork' }}. Tous droits réservés.
            </p>
            <div class="mt-2 space-x-4">
                <a href="/mobile/pointage/help" class="text-gray-400 hover:text-white text-sm transition-colors">
                    <i class="fas fa-question-circle mr-1"></i>Aide
                </a>
                <a href="/mobile/pointage/scanner" class="text-gray-400 hover:text-white text-sm transition-colors">
                    <i class="fas fa-qrcode mr-1"></i>Scanner QR
                </a>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-700">Chargement...</p>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Scripts -->
    <script>
        // CSRF Token setup
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // API Base URL
        window.apiBaseUrl = '{{ url("/api") }}';
        
        // Utility functions
        window.utils = {
            showLoading: function() {
                document.getElementById('loadingOverlay').classList.remove('hidden');
            },
            
            hideLoading: function() {
                document.getElementById('loadingOverlay').classList.add('hidden');
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
    </script>
    
    @stack('scripts')
</body>
</html>
