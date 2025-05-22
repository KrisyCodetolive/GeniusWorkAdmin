@extends('layouts.app')

@section('title', 'Paiement Paystack')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Paiement via Paystack</h1>
                
                <div class="mb-8">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Vous êtes sur le point de payer la facture <strong>{{ $paiement->facturation->numero_facture }}</strong> d'un montant de <strong>{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                    
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
                                <p class="text-sm text-gray-600">Facture: <span class="font-medium text-gray-800">{{ $paiement->facturation->numero_facture }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8" x-data="{ loading: false }">
                    @if($paiement->methode === 'card')
                        <div class="mb-6">
                            <h2 class="text-lg font-medium text-gray-700 mb-4">Paiement par carte bancaire</h2>
                            <form id="paymentForm" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <div class="mt-1">
                                            <input type="email" id="email-address" name="email" value="{{ auth()->user()->email }}" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700">Montant</label>
                                        <div class="mt-1">
                                            <input type="text" id="amount" name="amount" value="{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}" disabled class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md bg-gray-50">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between">
                                    <a href="{{ route('paiements.choisir-methode', $paiement->facturation->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Changer de méthode
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="payWithPaystack()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :disabled="loading"
                                        :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                        x-on:click="loading = true">
                                        <span x-show="!loading">Payer maintenant</span>
                                        <span x-show="loading" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Traitement en cours...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @elseif($paiement->methode === 'mobile_money')
                        <div class="mb-6">
                            <h2 class="text-lg font-medium text-gray-700 mb-4">Paiement par Mobile Money</h2>
                            <form id="mobileMoneyForm" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <div class="mt-1">
                                            <input type="email" id="mm-email-address" name="email" value="{{ auth()->user()->email }}" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Numéro de téléphone</label>
                                        <div class="mt-1">
                                            <input type="tel" id="phone" name="phone" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ex: 22507000000">
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="provider" class="block text-sm font-medium text-gray-700">Opérateur</label>
                                    <div class="mt-1">
                                        <select id="provider" name="provider" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Sélectionnez un opérateur</option>
                                            <option value="mtn">MTN Mobile Money</option>
                                            <option value="orange">Orange Money</option>
                                            <option value="moov">Moov Money</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="mm-amount" class="block text-sm font-medium text-gray-700">Montant</label>
                                    <div class="mt-1">
                                        <input type="text" id="mm-amount" name="amount" value="{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}" disabled class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md bg-gray-50">
                                    </div>
                                </div>
                                
                                <div class="flex justify-between">
                                    <a href="{{ route('paiements.choisir-methode', $paiement->facturation->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Changer de méthode
                                    </a>
                                    <button 
                                        type="button" 
                                        onclick="payWithMobileMoney()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :disabled="loading"
                                        :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                        x-on:click="loading = true">
                                        <span x-show="!loading">Payer maintenant</span>
                                        <span x-show="loading" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Traitement en cours...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-700">Paiement sécurisé</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Toutes les transactions sont sécurisées et cryptées. Vos informations de paiement ne sont jamais stockées sur nos serveurs.
                    </p>
                    <div class="flex space-x-4">
                        <img src="https://assets.paystack.com/assets/img/logos/cards/visa.svg" alt="Visa" class="h-8">
                        <img src="https://assets.paystack.com/assets/img/logos/cards/mastercard.svg" alt="Mastercard" class="h-8">
                        <img src="https://assets.paystack.com/assets/img/logos/cards/verve.svg" alt="Verve" class="h-8">
                        @if($paiement->methode === 'mobile_money')
                            <img src="https://assets.paystack.com/assets/img/logos/channels/mtn.svg" alt="MTN" class="h-8">
                            <img src="https://assets.paystack.com/assets/img/logos/channels/orange-money.svg" alt="Orange Money" class="h-8">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payWithPaystack() {
        let handler = PaystackPop.setup({
            key: '{{ config('paiement.paystack.public_key') }}',
            email: document.getElementById('email-address').value,
            amount: {{ $paiement->montant * 100 }}, // Montant en centimes
            currency: '{{ $paiement->devise }}',
            ref: '{{ $paiement->reference }}',
            callback: function(response) {
                // Rediriger vers la page de vérification du paiement
                window.location.href = '{{ route('paiements.paystack.verify', $paiement->id) }}?reference=' + response.reference;
            },
            onClose: function() {
                // Réactiver le bouton de paiement
                document.querySelector('button[onclick="payWithPaystack()"]').disabled = false;
                Alpine.store('loading', false);
            }
        });
        handler.openIframe();
    }
    
    function payWithMobileMoney() {
        // Vérifier que tous les champs sont remplis
        const email = document.getElementById('mm-email-address').value;
        const phone = document.getElementById('phone').value;
        const provider = document.getElementById('provider').value;
        
        if (!email || !phone || !provider) {
            alert('Veuillez remplir tous les champs.');
            Alpine.store('loading', false);
            return;
        }
        
        // Envoyer les données au serveur
        fetch('{{ route('paiements.paystack.mobile-money', $paiement->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                email: email,
                phone: phone,
                provider: provider
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Rediriger vers la page de vérification du paiement
                window.location.href = '{{ route('paiements.statut', $paiement->id) }}';
            } else {
                alert(data.message || 'Une erreur est survenue. Veuillez réessayer.');
                Alpine.store('loading', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
            Alpine.store('loading', false);
        });
    }
</script>
@endsection
