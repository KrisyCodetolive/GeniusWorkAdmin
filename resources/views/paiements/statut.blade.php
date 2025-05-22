@extends('layouts.app')

@section('title', 'Statut du paiement')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Statut du paiement</h1>
                
                <div class="mb-8">
                    <div class="border border-gray-200 rounded-lg p-4 mb-6">
                        <h2 class="text-lg font-medium text-gray-700 mb-2">Détails de la transaction</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Référence: <span class="font-medium text-gray-800">{{ $paiement->reference }}</span></p>
                                <p class="text-sm text-gray-600">Date: <span class="font-medium text-gray-800">{{ $paiement->created_at->format('d/m/Y H:i') }}</span></p>
                                <p class="text-sm text-gray-600">Montant: <span class="font-medium text-gray-800">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Méthode: <span class="font-medium text-gray-800">{{ ucfirst($paiement->methode) }}</span></p>
                                <p class="text-sm text-gray-600">Passerelle: <span class="font-medium text-gray-800">{{ ucfirst($paiement->passerelle) }}</span></p>
                                <p class="text-sm text-gray-600">Facture: <span class="font-medium text-gray-800">{{ $paiement->facturation->numero_facture }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        @if($paiement->statut === 'en_attente')
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-medium text-yellow-600">Paiement en attente</h2>
                                <p class="text-sm text-gray-600">Votre paiement est en cours de traitement.</p>
                            </div>
                        @elseif($paiement->statut === 'valide')
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-medium text-green-600">Paiement validé</h2>
                                <p class="text-sm text-gray-600">Votre paiement a été validé avec succès.</p>
                            </div>
                        @elseif($paiement->statut === 'rejete')
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-medium text-red-600">Paiement rejeté</h2>
                                <p class="text-sm text-gray-600">Votre paiement a été rejeté.</p>
                            </div>
                        @elseif($paiement->statut === 'annule')
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-medium text-gray-600">Paiement annulé</h2>
                                <p class="text-sm text-gray-600">Ce paiement a été annulé.</p>
                            </div>
                        @elseif($paiement->statut === 'rembourse')
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-medium text-blue-600">Paiement remboursé</h2>
                                <p class="text-sm text-gray-600">Ce paiement a été remboursé.</p>
                            </div>
                        @endif
                    </div>
                    
                    @if($paiement->statut === 'en_attente' && $paiement->passerelle === 'manuel')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Votre paiement est en attente de validation par notre équipe. Vous recevrez une notification dès que votre paiement sera validé.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($paiement->statut === 'en_attente')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Votre paiement est en cours de traitement. Veuillez patienter quelques instants. Cette page se rafraîchira automatiquement.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-center my-8">
                            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                        </div>
                        
                        <script>
                            // Rafraîchir la page toutes les 5 secondes
                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);
                        </script>
                    @elseif($paiement->statut === 'rejete')
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        Votre paiement a été rejeté. Raison: {{ $paiement->metadata['raison_rejet'] ?? 'Non spécifiée' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($paiement->statut === 'valide')
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        Votre paiement a été validé avec succès. Un email de confirmation vous a été envoyé.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-4">
                            <a href="{{ route('workflow.success', $paiement->reference) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Voir les détails de l'abonnement
                            </a>
                            <form action="{{ route('paiements.demander-remboursement', $paiement->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm4.707 3.707a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L8.414 9H10a3 3 0 013 3v1a1 1 0 102 0v-1a5 5 0 00-5-5H8.414l1.293-1.293z" clip-rule="evenodd" />
                                    </svg>
                                    Demander un remboursement
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-between mt-8">
                    <a href="{{ route('facturations.show', $paiement->facturation->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Retour à la facture
                    </a>
                    
                    @if($paiement->statut === 'en_attente')
                        <form action="{{ route('paiements.annuler', $paiement->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Annuler le paiement
                            </button>
                        </form>
                    @elseif($paiement->statut === 'valide')
                        <form action="{{ route('paiements.demander-remboursement', $paiement->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm4.707 3.707a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L8.414 9H10a3 3 0 013 3v1a1 1 0 102 0v-1a5 5 0 00-5-5H8.414l1.293-1.293z" clip-rule="evenodd" />
                                </svg>
                                Demander un remboursement
                            </button>
                        </form>
                    @elseif($paiement->statut === 'rejete' || $paiement->statut === 'annule')
                        <a href="{{ route('paiements.choisir-methode', $paiement->facturation->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Essayer un autre paiement
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
