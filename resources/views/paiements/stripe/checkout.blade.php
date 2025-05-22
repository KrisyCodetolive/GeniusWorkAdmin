@extends('layouts.app')

@section('title', 'Paiement Stripe')

@section('styles')
<style>
    .StripeElement {
        box-sizing: border-box;
        height: 40px;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        background-color: white;
        transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
        border-color: #3b82f6;
    }

    .StripeElement--invalid {
        border-color: #ef4444;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Paiement par carte bancaire</h1>
                
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
                                <p class="text-sm text-gray-600">Méthode: <span class="font-medium text-gray-800">Carte bancaire</span></p>
                                <p class="text-sm text-gray-600">Facture: <span class="font-medium text-gray-800">{{ $paiement->facturation->numero_facture }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <div x-data="{ loading: false, cardError: '' }">
                        <form id="payment-form" class="space-y-6">
                            <div>
                                <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">Informations de carte bancaire</label>
                                <div id="card-element" class="StripeElement"></div>
                                <div id="card-errors" role="alert" class="mt-2 text-sm text-red-600" x-text="cardError"></div>
                            </div>
                            
                            <div class="flex justify-between">
                                <a href="{{ route('paiements.choisir-methode', $paiement->facturation->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Changer de méthode
                                </a>
                                <button 
                                    type="submit" 
                                    id="submit-button"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    :disabled="loading"
                                    :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                    x-on:click="loading = true">
                                    <span x-show="!loading">Payer {{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span>
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
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-700">Paiement sécurisé</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Toutes les transactions sont sécurisées et cryptées. Les informations de votre carte ne sont jamais stockées sur nos serveurs.
                    </p>
                    <div class="flex space-x-4">
                        <img src="https://cdn.stripe.com/v3/fingerprinted/img/visa-729c05c240c4bdb47b03ac81d9945bfe.svg" alt="Visa" class="h-8">
                        <img src="https://cdn.stripe.com/v3/fingerprinted/img/mastercard-4d8844094130711885b5e41b28c9848f.svg" alt="Mastercard" class="h-8">
                        <img src="https://cdn.stripe.com/v3/fingerprinted/img/amex-a49b82f46c5cd6a96a6e418a6ca1717c.svg" alt="American Express" class="h-8">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser Stripe
        const stripe = Stripe('{{ config('paiement.stripe.public_key') }}');
        const elements = stripe.elements();
        
        // Créer l'élément de carte
        const cardElement = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#ef4444',
                    iconColor: '#ef4444'
                }
            }
        });
        
        // Monter l'élément de carte dans le DOM
        cardElement.mount('#card-element');
        
        // Gérer les erreurs de validation en temps réel
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                Alpine.store('cardError', event.error.message);
            } else {
                Alpine.store('cardError', '');
            }
        });
        
        // Gérer la soumission du formulaire
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            document.getElementById('submit-button').disabled = true;
            
            // Créer un token de paiement
            stripe.createToken(cardElement).then(function(result) {
                if (result.error) {
                    // Informer l'utilisateur s'il y a une erreur
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    
                    // Réactiver le bouton de soumission
                    document.getElementById('submit-button').disabled = false;
                } else {
                    // Envoyer le token au serveur
                    stripeTokenHandler(result.token);
                }
            });
        });
        
        // Envoyer le token au serveur
        function stripeTokenHandler(token) {
            // Créer un formulaire caché
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('paiements.stripe.process', $paiement->id) }}';
            
            // Ajouter le token
            const hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            
            // Ajouter le CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.setAttribute('type', 'hidden');
            csrfInput.setAttribute('name', '_token');
            csrfInput.setAttribute('value', '{{ csrf_token() }}');
            form.appendChild(csrfInput);
            
            // Ajouter le formulaire au document et le soumettre
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endsection
