<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GENIUS WORK - Gestion de Pointage & RH')</title>
    
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
            --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
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
        
        .success-gradient-bg {
            background: var(--success-gradient);
        }
        
        .warning-gradient-bg {
            background: var(--warning-gradient);
        }
        
        .danger-gradient-bg {
            background: var(--danger-gradient);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
        
        /* Sidebar styles */
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .sidebar-link {
            transition: all 0.2s ease;
            border-radius: 0.5rem;
        }
        
        .sidebar-link:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }
        
        .sidebar-link.active {
            background-color: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            font-weight: 500;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    
    <div x-data="{ sidebarOpen: true }" class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <aside 
            class="sidebar bg-white shadow-sm w-64 fixed inset-y-0 left-0 z-20 transform md:translate-x-0 transition-all duration-300"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
        >
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between px-4 py-5 border-b">
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-10 w-25  flex items-center justify-center">
                        </a>
                    </div>
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="md:hidden text-gray-500 hover:text-indigo-600 focus:outline-none"
                    >
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 py-4 px-3 overflow-y-auto">
                    <div class="space-y-1">
                        @yield('sidebar')
                    </div>
                </nav>
                
                <!-- User Profile -->
                <div class="border-t p-4">
                    <div class="flex items-center space-x-3">
                    
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i data-lucide="user" class="h-6 w-6 text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? 'Utilisateur' }}</p>
                                <p class="text-sm text-gray-500">{{ auth()->user()->email ?? 'email@example.com' }}</p>
                                <p class="text-xs text-indigo-600 mt-1">
                                    @if(auth()->user()->hasRole('admin'))
                                        Administrateur
                                    @elseif(auth()->user()->hasRole('manager'))
                                        Manager
                                    @elseif(auth()->user()->hasRole('employe'))
                                        Employé
                                    @elseif(auth()->user()->hasRole('superadmin'))
                                        Super Administrateur
                                    @else
                                        Utilisateur
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="dropdown" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="more-vertical" class="h-5 w-5"></i>
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 bottom-full mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                            >
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Paramètres</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Déconnexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto md:ml-64 transition-all duration-300">
            <!-- Top Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center">
                        <button 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="text-gray-500 hover:text-indigo-600 focus:outline-none mr-4"
                        >
                            <i data-lucide="menu" class="h-6 w-6"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                class="text-gray-500 hover:text-indigo-600 focus:outline-none relative"
                            >
                                <i data-lucide="bell" class="h-6 w-6"></i>
                                <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-30"
                            >
                                <div class="p-3 border-b">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <i data-lucide="user-plus" class="h-4 w-4 text-indigo-600"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">Nouvel employé ajouté</p>
                                                <p class="text-xs text-gray-500">Il y a 10 minutes</p>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 border-b">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <i data-lucide="check-circle" class="h-4 w-4 text-green-600"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">Demande de congé approuvée</p>
                                                <p class="text-xs text-gray-500">Il y a 1 heure</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2 border-t">
                                    <a href="#" class="block text-center text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                        Voir toutes les notifications
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Help -->
                        <button class="text-gray-500 hover:text-indigo-600 focus:outline-none">
                            <i data-lucide="help-circle" class="h-6 w-6"></i>
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-4 md:p-6">
                @yield('content')
            </div>
        </main>
        
        <!-- Include Dynamic Navsidebar Component -->
        @include('app.components.dynamic-navsidebar')
    </div>
    
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
    
    @stack('scripts')
</body>
</html>
