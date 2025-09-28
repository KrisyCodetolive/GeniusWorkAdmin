@extends('mobile.pointage.layout')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <img class="mx-auto h-16 w-auto" src="{{ asset('images/logo/logo2.png') }}" alt="GeniusWork Logo">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Connexion à votre compte
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Pointage mobile - {{ $app_name ?? 'GeniusWork' }}
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
            <form class="mb-0 space-y-6" action="{{ route('mobile.pointage.login') }}" method="POST" id="loginForm">
                @csrf
                
                @if(session('error'))
                <div class="rounded-md bg-red-50 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Se souvenir de moi
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-primary-600 hover:text-primary-500">
                            Mot de passe oublié ?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span>Se connecter</span>
                    </button>
                </div>
                
                <!-- WebAuthn Authentication Button -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Ou</span>
                    </div>
                </div>
                
                <div>
                    <button type="button" id="webAuthButton" class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-fingerprint text-primary-600 mr-2"></i>
                        <span>Connexion biométrique</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    
    // WebAuthn button handler
    document.getElementById('webAuthButton').addEventListener('click', async function() {
        // First check if WebAuthn is supported
        if (!window.PublicKeyCredential) {
            window.utils.showToast('Votre navigateur ne supporte pas l\'authentification biométrique', 'error');
            return;
        }
        
        // Check if user has credentials
        try {
            const hasCredentials = await window.checkWebAuthnSupport();
            
            if (hasCredentials) {
                // User has credentials, show login UI
                window.webAuthUI.showLogin();
            } else {
                // User doesn't have credentials, show no credentials UI
                window.webAuthUI.showNoCredentials();
            }
        } catch (error) {
            console.error('Error checking WebAuthn support:', error);
            window.utils.showToast('Erreur lors de la vérification de l\'authentification biométrique', 'error');
        }
    });
</script>
@endpush