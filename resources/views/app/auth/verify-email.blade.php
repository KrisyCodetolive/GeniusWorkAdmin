<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vérification d'email - GENIUS WORK</title>
    
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
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Vérification de l'adresse email</h2>
                <p class="text-gray-600">Veuillez vérifier votre adresse email pour continuer</p>
            </div>
            
            <div class="mt-8 bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-100">
                <!-- Session Status -->
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 p-4 bg-green-50 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ __('Un nouveau lien de vérification a été envoyé à l\'adresse email que vous avez fournie lors de votre inscription.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('Merci de vous être inscrit! Avant de commencer, pourriez-vous vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer? Si vous n\'avez pas reçu l\'email, nous vous en enverrons volontiers un autre.') }}
                </div>
                
                <div class="mt-6 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button 
                            type="submit" 
                            class="flex items-center px-4 py-2 bg-indigo-50 border border-transparent rounded-md font-medium text-sm text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <i data-lucide="refresh-cw" class="h-4 w-4 mr-2"></i>
                            {{ __('Renvoyer l\'email de vérification') }}
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button 
                            type="submit" 
                            class="text-sm text-gray-600 hover:text-gray-900 underline"
                        >
                            {{ __('Se déconnecter') }}
                        </button>
                    </form>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2">
                            <i data-lucide="info" class="h-6 w-6 text-indigo-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">Besoin d'aide?</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Si vous rencontrez des problèmes avec la vérification de votre email, veuillez 
                                <a href="#" class="text-indigo-600 hover:text-indigo-500">contacter notre support</a>.
                            </p>
                        </div>
                    </div>
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
    </script>
</body>
</html>
