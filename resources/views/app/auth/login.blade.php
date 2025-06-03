<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - GENIUS WORK</title>
    
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
        
        .btn-primary {
            background: var(--primary-gradient);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .tab-active {
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-6">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo/logo2.png') }}" alt="Logo" class="h-10 w-25  flex items-center justify-center">
                    </a>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Connexion à votre compte</h2>
                <p class="text-gray-600">Accédez à votre espace de gestion des présences</p>
            </div>
            
            <div class="mt-8 bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-100">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Info Message (for redirects from registration) -->
                @if (session('info'))
                    <div class="mb-4 p-4 rounded-md bg-blue-50 border border-blue-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4">
                        <div class="font-medium text-red-600">
                            {{ __('Oups! Quelque chose s\'est mal passé.') }}
                        </div>

                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Login Tabs -->
                <div x-data="{ activeTab: 'email' }">
                    <div class="flex border-b border-gray-200 mb-6">
                        <button 
                            @click="activeTab = 'email'" 
                            :class="{ 'tab-active': activeTab === 'email' }"
                            class="flex-1 py-2 px-1 text-center text-sm font-medium focus:outline-none transition-colors duration-200"
                        >
                            <i data-lucide="mail" class="h-4 w-4 inline-block mr-1"></i>
                            Email
                        </button>
                        <button 
                            @click="activeTab = 'phone'" 
                            :class="{ 'tab-active': activeTab === 'phone' }"
                            class="flex-1 py-2 px-1 text-center text-sm font-medium focus:outline-none transition-colors duration-200"
                        >
                            <i data-lucide="smartphone" class="h-4 w-4 inline-block mr-1"></i>
                            Téléphone
                        </button>
                    </div>
                    
                    <!-- Email Login Form -->
                    <div x-show="activeTab === 'email'">
                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf
                            <input type="hidden" name="login_type" value="email">
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Adresse email
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="email" 
                                        name="email" 
                                        type="email" 
                                        required 
                                        autofocus 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                        placeholder="exemple@entreprise.com"
                                        value="{{ session('email') ?? old('email') }}"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Mot de passe
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="password" 
                                        name="password" 
                                        type="password" 
                                        autocomplete="current-password" 
                                        required
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="••••••••"
                                    >
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input 
                                        id="remember_me" 
                                        name="remember" 
                                        type="checkbox" 
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                    <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                        Se souvenir de moi
                                    </label>
                                </div>

                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        Mot de passe oublié?
                                    </a>
                                </div>
                            </div>

                            <div>
                                <button 
                                    type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Se connecter
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Phone Login Form -->
                    <div x-show="activeTab === 'phone'" x-cloak>
                        <div x-data="{ step: 1 }">
                            <!-- Step 1: Enter Phone Number -->
                            <div x-show="step === 1" class="space-y-6">
                                <form method="POST" action="{{ route('login.send-otp') }}" class="space-y-6">
                                    @csrf
                                    <input type="hidden" name="login_type" value="phone">
                                    
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">
                                            Numéro de téléphone
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="smartphone" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input 
                                                id="phone" 
                                                name="phone" 
                                                type="tel" 
                                                autocomplete="tel" 
                                                required 
                                                value="{{ old('phone') }}"
                                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="+237 6XX XX XX XX"
                                            >
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Entrez votre numéro avec l'indicatif du pays</p>
                                    </div>

                                    <div>
                                        <button 
                                            type="submit"
                                            @click.prevent="step = 2"
                                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Recevoir le code OTP
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Step 2: Enter OTP -->
                            <div x-show="step === 2" x-cloak class="space-y-6">
                                <div class="text-center mb-4">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-3">
                                        <i data-lucide="check" class="h-6 w-6 text-green-600"></i>
                                    </div>
                                    <p class="text-sm text-gray-600">Fonctionnalité pas encore disponible</p>
                                </div>
                                
                                <form method="POST" action="#" class="space-y-6">
                                    @csrf
                                    <input type="hidden" name="login_type" value="phone">
                                    <input type="hidden" name="phone" x-bind:value="document.getElementById('phone')?.value || ''">
                                    
                                    <div>
                                        <label for="otp" class="block text-sm font-medium text-gray-700">
                                            Code de vérification (OTP)
                                        </label>
                                        <div class="mt-1">
                                            <div class="flex justify-between space-x-2">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" oninput="this.nextElementSibling?.focus()">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" oninput="this.nextElementSibling?.focus()">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" oninput="this.nextElementSibling?.focus()">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" oninput="this.nextElementSibling?.focus()">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" oninput="this.nextElementSibling?.focus()">
                                                <input type="text" maxlength="1" class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <input type="hidden" id="otp" name="otp">
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500 text-center">
                                            Vous n'avez pas reçu de code? 
                                            <button type="button" @click="step = 1" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                Réessayer
                                            </button>
                                        </p>
                                    </div>

                                    <div>
                                        <button 
                                            type="submit" 
                                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            onclick="combineOTP()"
                                        >
                                            Vérifier et se connecter
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">
                                Ou continuer avec
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i data-lucide="github" class="h-5 w-5 text-gray-700"></i>
                                <span class="ml-2">GitHub</span>
                            </a>
                        </div>

                        <div>
                            <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i data-lucide="mail" class="h-5 w-5 text-gray-700"></i>
                                <span class="ml-2">Google</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 text-center text-sm">
                    <p class="text-gray-600">
                        Vous n'avez pas de compte?
                        <a href="{{ route('workflow.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Créer un compte
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="py-6 text-center">
        <p class="text-sm text-gray-500">&copy; {{ date('Y') }} Genius Work. Tous droits réservés.</p>
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
            });
        });
        
        // Function to combine OTP inputs into a single hidden field
        function combineOTP() {
            const otpInputs = document.querySelectorAll('input[maxlength="1"]');
            let otpValue = '';
            
            otpInputs.forEach(input => {
                otpValue += input.value;
            });
            
            document.getElementById('otp').value = otpValue;
        }
    </script>
</body>
</html>
