@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-white to-gray-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="text-center mb-12 animate-fade-in-down">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                Simulateur de Coût
            </h1>
            <p class="mt-4 text-xl text-gray-500">
                Calculez le coût mensuel de notre application de pointage en fonction de vos besoins
            </p>
        </div>

        <!-- Carte principale -->
        <div class="animate-fade-in">
            <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border border-gray-100">
                <div class="p-6 sm:p-8">
                    <h2 class="text-2xl font-semibold text-gray-900">Estimation de votre tarif</h2>
                    <p class="text-gray-500 mt-2">
                        Entrez le nombre d'utilisateurs pour calculer votre tarif mensuel
                    </p>
                </div>
                
                <div class="px-6 sm:px-8 pb-6">
                    <div class="space-y-6">
                        <!-- Message d'erreur -->
                        @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <form action="{{ route('simulateur.calculer') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                <label for="nombre_utilisateurs" class="block text-sm font-medium text-gray-700">
                                    Nombre d'utilisateurs
                                </label>
                                
                                <!-- Champs de saisie -->
                                <div class="flex w-full sm:w-2/3 items-center space-x-2">
                                    <input
                                        id="nombre_utilisateurs"
                                        name="nombre_utilisateurs"
                                        type="number"
                                        min="1"
                                        placeholder="Ex: 25"
                                        value="{{ $nombreUtilisateurs ?? 1 }}"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center"
                                        required
                                    >
                                </div>
                                
                                <!-- Nombres prédéfinis -->
                                <div class="pt-2">
                                    <p class="text-sm text-gray-500 mb-2">Suggestions :</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach([10, 25, 50, 75, 100, 200] as $count)
                                        <button 
                                            type="submit" 
                                            name="nombre_utilisateurs" 
                                            value="{{ $count }}" 
                                            class="px-3 py-1 text-sm font-medium rounded-full border transition-colors {{ ($nombreUtilisateurs ?? 0) == $count ? 'bg-blue-50 border-blue-300 text-blue-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}"
                                        >
                                            {{ $count }} utilisateurs
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <button
                                type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow-sm transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Calculer le coût
                            </button>
                        </form>

                        <!-- Résultats -->
                        @if(isset($resultat))
                        <div class="mt-8 animate-fade-in">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    Résultats de votre simulation
                                </h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Forfait</span>
                                        <span class="font-semibold text-blue-600">{{ $resultat['forfait'] }}</span>
                                    </div>
                                    
                                    <hr class="border-gray-200">
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Nombre d'utilisateurs</span>
                                        <span class="font-semibold">{{ $resultat['nombreUtilisateurs'] }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Coût fixe mensuel</span>
                                        <span class="font-semibold">{{ number_format($resultat['coutFixe'], 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Coût des utilisateurs (100 FCFA/utilisateur)</span>
                                        <span class="font-semibold">{{ number_format($resultat['coutUtilisateurs'], 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    
                                    <hr class="border-gray-200">
                                    
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-base font-medium text-gray-800">Coût total mensuel</span>
                                        <span class="text-xl font-bold text-blue-700">{{ number_format($resultat['coutTotal'], 0, ',', ' ') }} FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <form action="{{ route('generer-devis') }}" method="POST">
                                @csrf
                                <input type="hidden" name="nombreUtilisateurs" value="{{ $resultat['nombreUtilisateurs'] }}">
                                <input type="hidden" name="forfait" value="{{ $resultat['forfait'] }}">
                                <input type="hidden" name="coutFixe" value="{{ $resultat['coutFixe'] }}">
                                <input type="hidden" name="coutUtilisateurs" value="{{ $resultat['coutUtilisateurs'] }}">
                                <input type="hidden" name="coutTotal" value="{{ $resultat['coutTotal'] }}">
                                
                                <button
                                    type="submit"
                                    class="mt-4 px-4 py-2 border border-blue-300 text-blue-700 font-medium rounded-md hover:bg-blue-50 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Télécharger le devis (PDF)
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Note de bas de page -->
        <div class="mt-12 text-center text-sm text-gray-500 animate-fade-in-delayed">
            <p>
                Les prix indiqués sont hors taxes et peuvent être sujets à modification.
                <br />
                Pour plus d'informations, contactez notre équipe commerciale.
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out forwards;
    }
    
    .animate-fade-in-delayed {
        opacity: 0;
        animation: fadeIn 0.5s ease-out forwards;
        animation-delay: 0.3s;
    }
</style>
@endsection
