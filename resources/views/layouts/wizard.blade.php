<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'G-WORK') }} - {{ $title ?? 'Wizard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f7ff',
                            100: '#e0eefe',
                            200: '#bae0fd',
                            300: '#78cafc',
                            400: '#36b3f9',
                            500: '#0c98eb',
                            600: '#0284d8',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        warning: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                        'soft-lg': '0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                }
            }
        }
    </script>
      <!-- Styles -->
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    
      <!-- Lucide Icons (via CDN) -->
      <script src="https://unpkg.com/lucide@latest"></script>
      
      <style>
          :root {
              --primary-gradient: linear-gradient(135deg, #0284d8 0%, #0ea5e9 100%);
              --secondary-gradient: linear-gradient(135deg, #0369a1 0%, #38bdf8 100%);
              --accent-gradient: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
              --success-gradient: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
              --warning-gradient: linear-gradient(135deg, #ea580c 0%, #fb923c 100%);
              --danger-gradient: linear-gradient(135deg, #dc2626 0%, #f87171 100%);
          }
          
          body {
              font-family: 'Inter', sans-serif;
              scroll-behavior: smooth;
              color: #1e293b;
              background-color: #f8fafc;
          }
          
          h1, h2, h3, h4, h5, h6 {
              font-family: 'Poppins', sans-serif;
              font-weight: 600;
              color: #0f172a;
          }
          
          .gradient-text {
              background: var(--primary-gradient);
              -webkit-background-clip: text;
              background-clip: text;
              color: transparent;
          }
          
          .gradient-bg {
              background: var(--primary-gradient);
          }
          
          .accent-gradient-bg {
              background: var(--accent-gradient);
          }
          
          .gradient-border {
              position: relative;
              border-radius: 0.5rem;
              background: white;
              overflow: hidden;
          }
          
          .gradient-border::before {
              content: "";
              position: absolute;
              inset: -2px;
              border-radius: 0.6rem;
              background: var(--primary-gradient);
              z-index: -1;
              transition: opacity 0.3s ease;
              opacity: 0;
          }
          
          .gradient-border:hover::before {
              opacity: 1;
          }
          
          .nav-link {
              position: relative;
              transition: all 0.3s ease;
          }
          
          .nav-link::after {
              content: '';
              position: absolute;
              width: 0;
              height: 2px;
              bottom: -2px;
              left: 0;
              background: var(--primary-gradient);
              transition: width 0.3s ease;
          }
          
          .nav-link:hover::after {
              width: 100%;
          }
          
          .btn-primary {
              background: var(--primary-gradient);
              transition: all 0.3s ease;
              color: white;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
              box-shadow: 0 4px 6px -1px rgba(2, 132, 216, 0.1), 0 2px 4px -1px rgba(2, 132, 216, 0.06);
          }
          
          .btn-primary:hover {
              transform: translateY(-2px);
              box-shadow: 0 10px 15px -3px rgba(2, 132, 216, 0.2), 0 4px 6px -2px rgba(2, 132, 216, 0.1);
          }
          
          .btn-secondary {
              background: white;
              border: 1px solid #e2e8f0;
              color: #0284d8;
              transition: all 0.3s ease;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
              box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
          }
          
          .btn-secondary:hover {
              background: #f8fafc;
              box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
              transform: translateY(-2px);
          }
          
          .btn-success {
              background: var(--success-gradient);
              color: white;
              transition: all 0.3s ease;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
              box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.1), 0 2px 4px -1px rgba(22, 163, 74, 0.06);
          }
          
          .btn-success:hover {
              transform: translateY(-2px);
              box-shadow: 0 10px 15px -3px rgba(22, 163, 74, 0.2), 0 4px 6px -2px rgba(22, 163, 74, 0.1);
          }
          
          .btn-warning {
              background: var(--warning-gradient);
              color: white;
              transition: all 0.3s ease;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
              box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.1), 0 2px 4px -1px rgba(234, 88, 12, 0.06);
          }
          
          .btn-warning:hover {
              transform: translateY(-2px);
              box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.2), 0 4px 6px -2px rgba(234, 88, 12, 0.1);
          }
          
          .card-hover {
              transition: all 0.3s ease;
              border: 1px solid rgba(226, 232, 240, 0.7);
              box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
          }
          
          .card-hover:hover {
              transform: translateY(-5px);
              box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
              border-color: rgba(226, 232, 240, 1);
          }
          
          /* Styles pour les étapes du wizard */
          .step-active .step-circle {
              background: var(--primary-gradient);
              color: white;
              transform: scale(1.1);
              box-shadow: 0 10px 15px -3px rgba(2, 132, 216, 0.2), 0 4px 6px -2px rgba(2, 132, 216, 0.1);
          }
          
          .step-completed .step-circle {
              background: var(--success-gradient);
              color: white;
          }
          
          .step-inactive .step-circle {
              background: white;
              color: #64748b;
              border: 2px solid #e2e8f0;
          }
          
          .step-circle {
              width: 3rem;
              height: 3rem;
              border-radius: 9999px;
              display: flex;
              align-items: center;
              justify-content: center;
              margin: 0 auto;
              transition: all 0.3s ease;
              font-weight: 600;
          }
          
          .step-line {
              height: 3px;
              background: #e2e8f0;
              flex: 1;
              margin: 0 0.5rem;
              transition: background-color 0.5s ease;
              border-radius: 9999px;
          }
          
          .step-completed .step-line {
              background: #0ea5e9;
          }
          
          /* Animations */
          @keyframes fadeIn {
              from { opacity: 0; transform: translateY(-10px); }
              to { opacity: 1; transform: translateY(0); }
          }
          
          @keyframes slideIn {
              from { transform: translateX(-20px); opacity: 0; }
              to { transform: translateX(0); opacity: 1; }
          }
          
          @keyframes pulse {
              0%, 100% { transform: scale(1); }
              50% { transform: scale(1.05); }
          }
          
          .animate-fadeIn {
              animation: fadeIn 0.5s ease-out forwards;
          }
          
          .animate-slideIn {
              animation: slideIn 0.5s ease-out forwards;
          }
          
          .animate-pulse {
              animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
          }
          
          /* Shadows */
          .shadow-soft {
              box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
          }
          
          .shadow-soft-lg {
              box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
          }
      </style>

    
    @stack('styles')
    @livewireStyles()
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="wizard-header text-white p-6 shadow-soft-lg gradient-bg animate-fadeIn">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}" class="transition-transform duration-300 hover:scale-105">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-10 w-25 flex items-center justify-center">
                    </a>
                </div>
            </div>
            <div>
                <a href="/admin/paie/bulletin-paies" class="text-white hover:text-white/90 flex items-center gap-2 py-2 px-4 rounded-lg hover:bg-white/10 transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:transform group-hover:-translate-x-1 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <span>Retour à la liste</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto py-8 px-4 animate-fadeIn" style="animation-delay: 0.1s;">
        @if (session('success'))
            <div class="bg-success-50 border-l-4 border-success-500 text-success-700 p-4 mb-6 rounded-r-lg shadow-soft animate-slideIn" role="alert">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-success-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-danger-50 border-l-4 border-danger-500 text-danger-700 p-4 mb-6 rounded-r-lg shadow-soft animate-slideIn" role="alert">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-danger-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-danger-50 border-l-4 border-danger-500 text-danger-700 p-4 mb-6 rounded-r-lg shadow-soft animate-slideIn" role="alert">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-danger-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-bold">Des erreurs sont survenues :</p>
                </div>
                <ul class="list-disc list-inside ml-6 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-soft p-6 transition-all duration-300 hover:shadow-soft-lg">
            @yield('content')
        </div>
    </div>

    <footer class="gradient-bg text-white py-6 mt-12 shadow-soft-lg">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'G-WORK') }}. Tous droits réservés.</p>
                </div>
                <div>
                    <p class="text-sm opacity-80 bg-white/10 py-1 px-3 rounded-full">Module de Paie - Version 1.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Fonction pour afficher les notifications
        function showNotification(type, message) {
            const iconColor = type === 'success' ? '#16a34a' : 
                            type === 'warning' ? '#ea580c' : 
                            type === 'info' ? '#0284d8' : '#dc2626';
            
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Succès' : 
                      type === 'warning' ? 'Attention' : 
                      type === 'info' ? 'Information' : 'Erreur',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                iconColor: iconColor,
                customClass: {
                    popup: 'swal2-modern-toast',
                    title: 'swal2-modern-title',
                    content: 'swal2-modern-content'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInRight animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutRight animate__faster'
                }
            });
        }
        
        // Configuration AJAX globale
        document.addEventListener('DOMContentLoaded', function() {
            // Ajouter le token CSRF à toutes les requêtes AJAX
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Intercepter les requêtes fetch pour ajouter le token CSRF
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (!options.headers) {
                    options.headers = {};
                }
                
                if (!(options.headers instanceof Headers)) {
                    options.headers = new Headers(options.headers);
                }
                
                options.headers.set('X-CSRF-TOKEN', csrfToken);
                
                return originalFetch(url, options);
            };
            
            // Ajouter des styles personnalisés pour SweetAlert2
            const style = document.createElement('style');
            style.textContent = `
                .swal2-modern-toast {
                    border-radius: 0.5rem !important;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                    padding: 1rem !important;
                    border-left: 4px solid currentColor !important;
                }
                .swal2-modern-title {
                    font-family: 'Poppins', sans-serif !important;
                    font-weight: 600 !important;
                    font-size: 1rem !important;
                }
                .swal2-modern-content {
                    font-family: 'Inter', sans-serif !important;
                    font-size: 0.875rem !important;
                }
            `;
            document.head.appendChild(style);
            
            // Ajouter Animate.css pour les animations
            const animateCss = document.createElement('link');
            animateCss.rel = 'stylesheet';
            animateCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';
            document.head.appendChild(animateCss);
            
            // Ajouter des effets de hover sur les éléments interactifs
            document.querySelectorAll('.btn-primary, .btn-secondary, .btn-success, .btn-warning').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
    
    @stack('scripts')
    @livewireScripts()
</body>
</html>
