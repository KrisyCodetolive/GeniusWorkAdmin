@extends('layouts.app')

@section('title', 'Inscription réussie')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <!-- Toast de notification -->
    @if(session('success'))
    <div id="toast-success" class="fixed top-4 right-4 z-50 flex items-center w-full max-w-md p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close" onclick="this.parentElement.remove();">
            <span class="sr-only">Fermer</span>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    <script>
        // Faire disparaître le toast après 5 secondes
        setTimeout(function() {
            const toast = document.getElementById('toast-success');
            if (toast) {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }, 5000);
    </script>
    @endif
    
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="text-center">
                <div class="mt-2 mb-8">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Félicitations !
                </h1>
                <p class="mt-4 text-xl text-gray-600">
                    Votre inscription à GENIUS WORK a été complétée avec succès.
                </p>
            </div>

            <div class="mt-8 border-t border-gray-200 pt-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Détails de votre abonnement</h2>
                
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Entreprise</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $entreprise->nom }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Plan d'abonnement</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->planAbonnement->nom }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date de début</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->date_debut->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date de fin</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->date_fin->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Montant</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                            <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Actif
                            </span>
                        </div>
                    </div>
                </div>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">Détails du paiement</h2>
                
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Numéro de facture</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facturation->numero_facture }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Date de facturation</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facturation->date_facturation->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Méthode de paiement</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                @switch($paiement->methode)
                                    @case('carte')
                                        Carte bancaire
                                        @break
                                    @case('mobile_money')
                                        Mobile Money
                                        @break
                                    @case('virement')
                                        Virement bancaire
                                        @break
                                    @case('cheque')
                                        Chèque
                                        @break
                                    @case('especes')
                                        Espèces
                                        @break
                                    @default
                                        {{ ucfirst($paiement->methode) }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Référence de paiement</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $paiement->reference }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Montant payé</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                            <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Payé
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-col items-center justify-center space-y-4">
                <a href="{{ route('facturations.telecharger', $facturation->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Télécharger la facture
                </a>
                
                <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Accéder au tableau de bord
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
