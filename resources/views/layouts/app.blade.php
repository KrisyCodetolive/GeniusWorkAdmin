<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GENIUS WORK - La Solution Innovante pour la Gestion des Présences</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    
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
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>
    @livewireStyles()
</head>
<body class="min-h-screen bg-gradient-to-b from-indigo-50/50 to-blue-50/50 font-sans text-gray-800 overflow-x-hidden">


    

  
    <!-- Header/Navigation -->
    <header class="bg-white/90 backdrop-blur-sm shadow-sm sticky top-0 z-50 transition-all duration-300" x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="container mx-auto px-4 py-3" :class="{ 'py-2': scrolled, 'py-3': !scrolled }">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-10 w-25  flex items-center justify-center">
                    </a>
                </div>
                
                <!-- Start button -->

                <div class="flex items-center space-x-4">
                    @if (Auth::user())
                        <div class="hidden md:block">
                            <a href="/admin" class="btn-primary px-5 py-2 rounded-lg text-white font-medium hover:shadow-lg transition-all">
                                Dashbord
                            </a>
                        
                        </div>
                        <div class="hidden md:block">
                            <a href="{{ route('logout') }}" class="btn-secondary px-5 py-2 rounded-lg text-blue-500 font-medium hover:shadow-lg transition-all">
                                Se déconnecter
                            </a>
                        </div>
                    @else
                        <div class="hidden md:block">
                            <a href="{{ route('workflow.index') }}" class="btn-primary px-5 py-2 rounded-lg text-white font-medium hover:shadow-lg transition-all">
                                Commencer
                            </a>
                        </div>
                        <div class="hidden md:block">
                            <a href="{{ route('login') }}" class="btn-secondary px-5 py-2 rounded-lg text-blue-500 font-medium hover:shadow-lg transition-all">
                                Se connecter
                            </a>
                        </div>
                    @endif
        
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-gray-500 hover:text-indigo-600 focus:outline-none"
                    >
                        <i data-lucide="menu" class="h-6 w-6" x-show="!mobileMenuOpen"></i>
                        <i data-lucide="x" class="h-6 w-6" x-show="mobileMenuOpen"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <nav 
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="md:hidden pt-4 pb-2 space-y-3"
            >
                <div class="pt-2">
                    <a href="{{ route('workflow.index') }}" class="block w-full text-center btn-primary px-5 py-2 rounded-lg text-white font-medium hover:shadow-lg transition-all">
                        Commencer
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('images/logo/logo.png') }}" alt="Logo" class="h-10 w-25  flex items-center justify-center">
                        </a>
                    </div>
                    <p class="text-gray-400">La solution innovante pour la gestion des présences et des ressources humaines adaptée à votre entreprise.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4"></h3>
                   
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center space-x-2">
                            <i data-lucide="map-pin" class="h-5 w-5 text-indigo-400"></i>
                            <span class="text-gray-400">Abidjan, Côte d'Ivoire</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i data-lucide="mail" class="h-5 w-5 text-indigo-400"></i>
                            <span class="text-gray-400">work@genius.ci</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i data-lucide="phone" class="h-5 w-5 text-indigo-400"></i>
                            <span class="text-gray-400">+225 07 04 750 465</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 mb-4 md:mb-0">&copy; {{ date('Y') }} Genius Work. Tous droits réservés.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="facebook" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="twitter" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="instagram" class="h-5 w-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="linkedin" class="h-5 w-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            lucide.createIcons();
            
            // Initialize AOS animations
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
            });
        });
    </script>
    
    <!-- Additional Scripts -->
    @stack('scripts')
    @livewireScripts()
</body>
</html>