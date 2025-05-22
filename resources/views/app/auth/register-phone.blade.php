<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription par téléphone - GENIUS WORK</title>
    
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
        
        .otp-input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-6">
                    <div class="h-12 w-12 rounded-lg gradient-bg flex items-center justify-center">
                        <i data-lucide="clock" class="h-7 w-7 text-white"></i>
                    </div>
                    <span class="text-3xl font-bold gradient-text">GENIUS WORK</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Inscription par téléphone</h2>
                <p class="text-gray-600">Créez votre compte avec votre numéro de téléphone</p>
            </div>
            
            <div class="mt-8 bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-100">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('status') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('Oups! Quelque chose s\'est mal passé.') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div x-data="{ step: {{ session('registration_phone') ? 2 : 1 }} }">
                    <!-- Step 1: Enter Phone Number -->
                    <div x-show="step === 1" class="space-y-6">
                        <form method="POST" action="{{ route('register.send-otp') }}" class="space-y-6">
                            @csrf
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Nom complet
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="name" 
                                        name="name" 
                                        type="text" 
                                        autocomplete="name" 
                                        required 
                                        value="{{ old('name') }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Jean Dupont"
                                    >
                                </div>
                            </div>
                            
                            <!-- Phone Number -->
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
                                        placeholder="+33612345678"
                                    >
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Format international requis (ex: +33612345678)</p>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Recevoir un code de vérification
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Step 2: Verify OTP and Complete Registration -->
                    <div x-show="step === 2" x-cloak class="space-y-6">
                        <div class="text-center mb-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-3">
                                <i data-lucide="check" class="h-6 w-6 text-green-600"></i>
                            </div>
                            <p class="text-sm text-gray-600">
                                Un code de vérification a été envoyé au numéro <span class="font-medium">{{ session('registration_phone') }}</span>
                            </p>
                        </div>
                        
                        <form method="POST" action="{{ route('register.verify-otp') }}" class="space-y-6">
                            @csrf
                            
                            <!-- OTP Input -->
                            <div>
                                <label for="otp" class="block text-sm font-medium text-gray-700 text-center mb-3">
                                    Entrez le code de vérification
                                </label>
                                <div class="flex justify-center space-x-2" x-data="otpInput()">
                                    <template x-for="(digit, index) in 6" :key="index">
                                        <input 
                                            type="text" 
                                            maxlength="1"
                                            class="otp-input border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                            :id="'otp-' + index"
                                            @input="handleInput($event, index)"
                                            @keydown.backspace="handleBackspace($event, index)"
                                            @focus="$event.target.select()"
                                        >
                                    </template>
                                    <input type="hidden" name="otp" id="otp-value">
                                </div>
                            </div>
                            
                            <!-- Password -->
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
                                        autocomplete="new-password" 
                                        required
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="••••••••"
                                    >
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Le mot de passe doit contenir au moins 8 caractères</p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmer le mot de passe
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                                    </div>
                                    <input 
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        type="password" 
                                        autocomplete="new-password" 
                                        required
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="••••••••"
                                    >
                                </div>
                            </div>
                            
                            <!-- Terms -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input 
                                        id="terms" 
                                        name="terms" 
                                        type="checkbox" 
                                        required
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="terms" class="font-medium text-gray-700">
                                        J'accepte les <a href="#" class="text-indigo-600 hover:text-indigo-500">conditions d'utilisation</a> et la <a href="#" class="text-indigo-600 hover:text-indigo-500">politique de confidentialité</a>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Créer mon compte
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <button type="button" @click="step = 1" class="text-sm text-indigo-600 hover:text-indigo-500">
                                    <i data-lucide="arrow-left" class="h-4 w-4 inline-block"></i>
                                    Retour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">
                                Ou
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3">
                        <a href="{{ route('register') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i data-lucide="mail" class="h-5 w-5 mr-2 text-gray-500"></i>
                            S'inscrire avec un email
                        </a>
                    </div>
                </div>
                
                <div class="mt-6 text-center text-sm">
                    <p class="text-gray-600">
                        Vous avez déjà un compte?
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Connectez-vous
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
    
    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        
        // Initialize AOS animations
        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
            });
        });
        
        // OTP Input Handling
        function otpInput() {
            return {
                handleInput(event, index) {
                    const input = event.target;
                    const value = input.value;
                    
                    // Only allow numbers
                    if (!/^\d*$/.test(value)) {
                        input.value = '';
                        return;
                    }
                    
                    // Auto-focus next input
                    if (value.length === 1 && index < 5) {
                        document.getElementById(`otp-${index + 1}`).focus();
                    }
                    
                    this.updateOtpValue();
                },
                
                handleBackspace(event, index) {
                    const input = event.target;
                    
                    // If empty and backspace is pressed, focus previous input
                    if (event.key === 'Backspace' && input.value === '' && index > 0) {
                        document.getElementById(`otp-${index - 1}`).focus();
                    }
                },
                
                updateOtpValue() {
                    let otp = '';
                    for (let i = 0; i < 6; i++) {
                        otp += document.getElementById(`otp-${i}`).value;
                    }
                    document.getElementById('otp-value').value = otp;
                }
            }
        }
    </script>
</body>
</html>
