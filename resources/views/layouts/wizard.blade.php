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
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
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
                    }
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
              --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
              --secondary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
          }
          
          body {
              font-family: 'Inter', sans-serif;
              scroll-behavior: smooth;
          }
          
          h1, h2, h3, h4, h5, h6 {
              font-family: 'Poppins', sans-serif;
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
          
          .gradient-border {
              position: relative;
              border-radius: 0.5rem;
              background: white;
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
          }
          
          .btn-primary:hover {
              transform: translateY(-2px);
              box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
          }
          
          .btn-secondary {
              background: white;
              border: 1px solid #e2e8f0;
              color: #4f46e5;
              transition: all 0.3s ease;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
          }
          
          .btn-secondary:hover {
              background: #f8fafc;
              box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
              transform: translateY(-2px);
          }
          
          .btn-success {
              background: linear-gradient(135deg, #10b981 0%, #059669 100%);
              color: white;
              transition: all 0.3s ease;
              font-weight: 500;
              padding: 0.5rem 1rem;
              border-radius: 0.375rem;
          }
          
          .btn-success:hover {
              transform: translateY(-2px);
              box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
          }
          
          .card-hover {
              transition: all 0.3s ease;
          }
          
          .card-hover:hover {
              transform: translateY(-5px);
              box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
          }
          
          /* Styles pour les étapes du wizard */
          .step-active .step-circle {
              background: var(--primary-gradient);
              color: white;
              transform: scale(1.1);
              box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
          }
          
          .step-completed .step-circle {
              background: var(--primary-gradient);
              color: white;
          }
          
          .step-inactive .step-circle {
              background: white;
              color: #6b7280;
              border: 2px solid #e5e7eb;
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
          }
          
          .step-line {
              height: 2px;
              background: #e5e7eb;
              flex: 1;
              margin: 0 0.5rem;
              transition: background-color 0.5s ease;
          }
          
          .step-completed .step-line {
              background: #4f46e5;
          }
      </style>

    
    @stack('styles')
    @livewireStyles()
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="wizard-header text-white p-6 shadow-md gradient-bg">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-10 w-25  flex items-center justify-center">
                    </a>
                </div>
  
            </div>
            <div>
                <a href="/admin/paie/bulletin-paies" class="text-white hover:text-white/80 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto py-8 px-4">
        @if (session('success'))
            <div class="bg-success-100 border-l-4 border-success-500 text-success-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-6" role="alert">
                <p class="font-bold">Des erreurs sont survenues :</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            @yield('content')
        </div>
    </div>

    <footer class="gradient-bg text-white py-6 mt-12 shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'G-WORK') }}. Tous droits réservés.</p>
                </div>
                <div>
                    <p class="text-sm opacity-70">Module de Paie - Version 1.0</p>
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
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Succès' : 'Erreur',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
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
        });
    </script>
    
    @stack('scripts')
    @livewireScripts()
</body>
</html>
