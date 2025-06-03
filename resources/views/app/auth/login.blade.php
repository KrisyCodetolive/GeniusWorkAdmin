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
                <!-- Notifications -->
                <div x-data="{ 
                    showStatus: {{ session('status') ? 'true' : 'false' }}, 
                    showInfo: {{ session('info') ? 'true' : 'false' }}, 
                    showErrors: {{ $errors->any() ? 'true' : 'false' }},
                    autoClose(type, delay = 5000) {
                        if (this[type]) {
                            setTimeout(() => { this[type] = false }, delay);
                        }
                    }
                }" 
                x-init="
                    autoClose('showStatus', 5000);
                    autoClose('showInfo', 8000);
                    // Erreurs ne se ferment pas automatiquement
                ">
                    <!-- Success Message -->
                    <div x-show="showStatus" 
                         x-transition:enter="transform ease-out duration-300 transition" 
                         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
                         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="mb-4 p-4 rounded-md bg-green-50 border border-green-100 shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="showStatus = false" type="button" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                                        <span class="sr-only">Fermer</span>
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Message -->
                    <div x-show="showInfo"
                         x-transition:enter="transform ease-out duration-300 transition" 
                         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
                         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="mb-4 p-4 rounded-md bg-blue-50 border border-blue-100 shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="info" class="h-5 w-5 text-blue-500"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="showInfo = false" type="button" class="inline-flex rounded-md p-1.5 text-blue-500 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                        <span class="sr-only">Fermer</span>
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Validation Errors -->
                    <div x-show="showErrors"
                         x-transition:enter="transform ease-out duration-300 transition" 
                         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" 
                         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="mb-4 p-4 rounded-md bg-red-50 border border-red-100 shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="alert-triangle" class="h-5 w-5 text-red-500"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-red-800">{{ __('Oups! Quelque chose s\'est mal passé.') }}</h3>
                                <div class="mt-2">
                                    <ul class="list-disc pl-5 space-y-1 text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="showErrors = false" type="button" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                                        <span class="sr-only">Fermer</span>
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
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
                        <div x-data="{ step: {{ session('show_otp_step') ? 2 : 1 }} }">
                            <!-- Step 1: Enter Phone Number -->
                            <div x-show="step === 1" class="space-y-6">
                                <form method="POST" action="{{ route('login.send-otp') }}" class="space-y-6" id="phone-form">
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
                                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Recevoir le code OTP
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Step 2: Enter OTP -->
                            <div x-show="step === 2" x-cloak class="space-y-6"
                                 x-transition:enter="transform ease-out duration-300 transition" 
                                 x-transition:enter-start="opacity-0 translate-y-4" 
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="text-center mb-6">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4 animate-pulse">
                                        <i data-lucide="smartphone" class="h-8 w-8 text-green-600"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">Vérification OTP</h3>
                                    <p class="text-sm text-gray-600">Un code de vérification à 6 chiffres a été envoyé au numéro</p>
                                    <p class="text-base font-semibold text-indigo-600 mt-1">{{ session('phone') }}</p>
                                </div>
                                
                                <form method="POST" action="{{ route('login.verify-otp') }}" class="space-y-6" id="otp-form">
                                    @csrf
                                    <input type="hidden" name="login_type" value="phone">
                                    <input type="hidden" name="phone" value="{{ session('phone') }}">
                                    
                                    <div x-data="{ 
                                        focusNext(index, event) {
                                            // Seulement les chiffres
                                            if (!/^\d+$/.test(event.target.value)) {
                                                event.target.value = '';
                                                return;
                                            }
                                            
                                            // Avancer au champ suivant
                                            if (index < 5) {
                                                this.$refs['otp-' + (index + 1)].focus();
                                            }
                                        },
                                        handleKeyDown(index, event) {
                                            // Gérer la touche Backspace
                                            if (event.key === 'Backspace' && !event.target.value && index > 0) {
                                                this.$refs['otp-' + (index - 1)].focus();
                                            }
                                        },
                                        handlePaste(event) {
                                            event.preventDefault();
                                            const paste = (event.clipboardData || window.clipboardData).getData('text');
                                            if (!/^\d+$/.test(paste)) return; // Seulement les chiffres
                                            
                                            const inputs = Array.from(this.$el.querySelectorAll('input[type="text"]'));
                                            const digits = paste.split('').slice(0, inputs.length);
                                            
                                            inputs.forEach((input, i) => {
                                                if (digits[i]) {
                                                    input.value = digits[i];
                                                    if (i === inputs.length - 1) {
                                                        input.focus();
                                                    }
                                                }
                                            });
                                        }
                                    }">
                                        <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                                            Entrez le code à 6 chiffres
                                        </label>
                                        <div class="flex justify-between space-x-2" @paste="handlePaste($event)">
                                            <template x-for="(_, index) in [0,1,2,3,4,5]" :key="index">
                                                <div class="relative">
                                                    <input 
                                                        type="text" 
                                                        maxlength="1" 
                                                        x-ref="'otp-' + index"
                                                        x-on:input="focusNext(index, $event)"
                                                        x-on:keydown="handleKeyDown(index, $event)"
                                                        x-init="index === 0 && $nextTick(() => $el.focus())"
                                                        class="w-10 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-150 shadow-sm"
                                                        :class="{'border-indigo-500 bg-indigo-50': $el.value.length > 0}"
                                                    >
                                                    <div x-show="index < 5" class="absolute top-1/2 -right-2 transform -translate-y-1/2 text-gray-300 pointer-events-none">
                                                        <span>-</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <input type="hidden" id="otp" name="otp">
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-6">
                                        <button type="button" @click="step = 1" class="inline-flex items-center text-sm text-gray-600 hover:text-indigo-600">
                                            <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Retour
                                        </button>
                                        
                                        <button type="button" @click="step = 1" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            <i data-lucide="refresh-cw" class="h-4 w-4 mr-1"></i> Renvoyer le code
                                        </button>
                                    </div>
                                    <div>
                                        <button 
                                            type="submit" 
                                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-4"
                                            @click.prevent="
                                                const inputs = $el.closest('form').querySelectorAll('input[maxlength="1"]');
                                                let otpValue = '';
                                                inputs.forEach(input => { otpValue += input.value });
                                                document.getElementById('otp').value = otpValue;
                                                if (otpValue.length === 6) {
                                                    $el.closest('form').submit();
                                                } else {
                                                    alert('Veuillez entrer les 6 chiffres du code OTP');
                                                }
                                            "
                                        >
                                            <i data-lucide="lock" class="h-5 w-5 mr-2"></i> Vérifier et se connecter
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
