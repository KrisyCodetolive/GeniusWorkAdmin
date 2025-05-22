@extends('layouts.app')

@section('title', 'Choisir une méthode de paiement')

@php
    // Initialiser les variables avec des valeurs par défaut
    $selectedGateway = $selectedGateway ?? '';
    $selectedMethod = $selectedMethod ?? '';
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentForm', () => ({
            selectedGateway: '{{ $selectedGateway }}',
            selectedMethod: '{{ $selectedMethod }}',
            facturationId: '{{ $facturation->id ?? null }}',
            isProcessing: false,
            errorMessage: '',
            
            init() {
                // Vérifier si nous avons un ID de facturation
                if (!this.facturationId) {
                    this.errorMessage = 'Erreur: Identifiant de facturation manquant. Veuillez réessayer.';
                    console.error('Facturation ID is missing');
                }
                
                // Initialiser les conteneurs de méthodes
                this.updateMethodContainers();
                
                // Ajouter les gestionnaires d'événements pour les clics sur les cartes
                this.setupEventListeners();
            },
            
            setupEventListeners() {
                // Gestionnaires pour les cartes de passerelle
                document.querySelectorAll('.payment-card:not(.payment-method-item)').forEach(card => {
                    card.addEventListener('click', (event) => {
                        const gateway = event.currentTarget.getAttribute('data-gateway');
                        if (gateway) {
                            this.selectGateway(gateway);
                        }
                    });
                });
                
                // Gestionnaires pour les cartes de méthode
                document.querySelectorAll('.payment-method-item').forEach(card => {
                    card.addEventListener('click', (event) => {
                        const method = event.currentTarget.getAttribute('data-method');
                        if (method) {
                            this.selectMethod(method);
                        }
                    });
                });
            },
            
            formatNumber(number) {
                // Formatter les nombres pour l'affichage monétaire
                return new Intl.NumberFormat('fr-FR').format(number);
            },
            
            selectGateway(gateway) {
                this.selectedGateway = gateway;
                this.selectedMethod = ''; // Réinitialiser la méthode sélectionnée
                document.getElementById('selected_gateway').value = gateway;
                document.getElementById('selected_method').value = '';
                this.errorMessage = '';
                
                // Mettre à jour les classes pour les cartes de passerelle
                document.querySelectorAll('.payment-card').forEach(card => {
                    card.classList.remove('selected');
                });
                
                // Ajouter la classe selected à la passerelle sélectionnée
                const gatewayCards = document.querySelectorAll('.payment-card:not(.payment-method-item)');
                gatewayCards.forEach(card => {
                    if (card.getAttribute('data-gateway') === gateway) {
                        card.classList.add('selected');
                    }
                });
                
                // Mettre à jour les conteneurs de méthodes avec animation
                this.updateMethodContainers();
            },
            
            selectMethod(method) {
                this.selectedMethod = method;
                document.getElementById('selected_method').value = method;
                this.errorMessage = '';
                
                // Mettre à jour les classes pour les cartes de méthode
                const methodCards = document.querySelectorAll('.payment-method-item');
                methodCards.forEach(card => {
                    card.classList.remove('selected');
                    if (card.getAttribute('data-method') === method) {
                        card.classList.add('selected');
                    }
                });
            },
            
            updateMethodContainers() {
                // Cacher tous les conteneurs de méthodes
                document.querySelectorAll('.payment-method-container').forEach(container => {
                    container.classList.remove('active');
                });
                
                // Afficher le conteneur de méthodes pour la passerelle sélectionnée
                if (this.selectedGateway) {
                    const methodContainer = document.getElementById(this.selectedGateway + '-methods');
                    if (methodContainer) {
                        setTimeout(() => {
                            methodContainer.classList.add('active');
                        }, 100);
                    }
                }
            },
            
            isGatewaySelected(gateway) {
                return this.selectedGateway === gateway;
            },
            
            isMethodSelected(method) {
                return this.selectedMethod === method;
            },
            
            submitForm() {
                if (!this.selectedGateway) {
                    this.errorMessage = 'Veuillez sélectionner une passerelle de paiement';
                    return false;
                }
                
                if (!this.selectedMethod) {
                    this.errorMessage = 'Veuillez sélectionner une méthode de paiement';
                    return false;
                }
                
                if (!this.facturationId) {
                    this.errorMessage = 'Erreur: Identifiant de facturation manquant. Veuillez rafraîchir la page et réessayer.';
                    return false;
                }
                
                this.isProcessing = true;
                return true;
            },
            
            async processPayment() {
                if (!this.submitForm()) {
                    return;
                }
                
                try {
                    this.$refs.paymentForm.submit();
                } catch (error) {
                    console.error('Error processing payment:', error);
                    this.errorMessage = 'Une erreur est survenue lors du traitement du paiement. Veuillez réessayer.';
                    this.isProcessing = false;
                }
            },
            
            async submit() {
                await this.processPayment();
            }
        }));
    });
</script>
@endpush

@section('content')
<style>
    .payment-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .payment-card.selected {
        border-color: #4F46E5;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        transform: translateY(-2px);
    }
    
    .payment-method-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease, opacity 0.3s ease;
        opacity: 0;
    }
    
    .payment-method-container.active {
        max-height: 500px;
        opacity: 1;
    }
    
    .payment-method-item {
        transition: all 0.3s ease;
        transform: translateY(10px);
        opacity: 0;
    }
    
    .payment-method-container.active .payment-method-item {
        transform: translateY(0);
        opacity: 1;
    }
    
    .payment-method-container.active .payment-method-item:nth-child(1) {
        transition-delay: 0.1s;
    }
    
    .payment-method-container.active .payment-method-item:nth-child(2) {
        transition-delay: 0.2s;
    }
    
    .payment-method-container.active .payment-method-item:nth-child(3) {
        transition-delay: 0.3s;
    }
</style>

<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500 mb-2">
                Choisir une méthode de paiement
            </h1>
            <p class="text-lg text-gray-600">
                Sélectionnez votre méthode de paiement préférée pour finaliser votre transaction
            </p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md overflow-hidden" x-data="paymentForm">
            <form x-ref="paymentForm" method="POST" action="{{ route('paiements.initialiser', $facturation->id) }}">
                @csrf
                <input type="hidden" id="selected_gateway" name="passerelle" value="{{ $selectedGateway }}">
                <input type="hidden" id="selected_method" name="methode" value="{{ $selectedMethod }}">
                <div class="p-8">
                    <!-- Order Summary -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Récapitulatif de votre commande</h2>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Numéro de facture: <span class="font-medium text-gray-800">{{ $facturation->numero_facture }}</span></p>
                                    <p class="text-sm text-gray-600">Date: <span class="font-medium text-gray-800">{{ $facturation->date_facturation->format('d/m/Y') }}</span></p>
                                    <p class="text-sm text-gray-600">Montant HT: <span class="font-medium text-gray-800">{{ number_format($facturation->montant_ht, 0, ',', ' ') }} {{ $facturation->devise }}</span></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">TVA ({{ $facturation->taux_tva }}%): <span class="font-medium text-gray-800">{{ number_format($facturation->montant_tva, 0, ',', ' ') }} {{ $facturation->devise }}</span></p>
                                    <p class="text-sm text-gray-600">Montant TTC: <span class="font-medium text-gray-800">{{ number_format($facturation->montant_ttc, 0, ',', ' ') }} {{ $facturation->devise }}</span></p>
                                    <p class="text-sm text-gray-600">Statut: <span class="font-medium text-gray-800">{{ ucfirst($facturation->statut_paiement) }}</span></p>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-medium text-gray-900">Total à payer</span>
                                    <span class="text-xl font-bold text-indigo-600">{{ number_format($facturation->montant_ttc, 0, ',', ' ') }} {{ $facturation->devise }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Choisissez votre passerelle de paiement</h3>
                        
                        <!-- Error message -->
                        <div x-show="errorMessage" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700" x-text="errorMessage"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            @foreach($passerelles as $passerelle)
                            <!-- {{ $passerelle['id'] }} Gateway -->
                            <div class="payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedGateway === $passerelle['id'] ? 'selected' : '' }}" data-gateway="{{ $passerelle['id'] }}">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $passerelle['nom'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $passerelle['description'] }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2 border-t border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">Sécurisé</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Méthodes de paiement -->
                        <div x-show="selectedGateway" class="mt-8 transition-all duration-300 ease-in-out" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0">
                            <h3 class="text-lg font-semibold mb-4">Choisissez votre méthode de paiement</h3>
                            
                            @foreach($passerelles as $passerelle)
                            <!-- {{ $passerelle['id'] }} Methods -->
                            <div x-show="isGatewaySelected('{{ $passerelle['id'] }}')" class="payment-method-container {{ $selectedGateway === $passerelle['id'] ? 'active' : '' }}" id="{{ $passerelle['id'] }}-methods">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($passerelle['methodes'] as $methodeId => $methodeName)
                                    <!-- {{ $methodeName }} Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === $methodeId ? 'selected' : '' }}" data-method="{{ $methodeId }}">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">{{ $methodeName }}</h4>
                                                <div x-show="isMethodSelected('{{ $methodeId }}')" class="bg-indigo-500 rounded-full p-1">
                                                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">
                                                Payer avec {{ $methodeName }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="bg-gray-50 px-6 py-4 sm:px-8 border-t border-gray-200 mt-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <a 
                            href="{{ route('facturations.show', $facturation->id) }}" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out"
                        >
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Retour
                        </a>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out"
                            :disabled="!selectedGateway || !selectedMethod || isProcessing"
                            :class="{ 'opacity-50 cursor-not-allowed': !selectedGateway || !selectedMethod || isProcessing }"
                            @click.prevent="submit"
                        >
                            <span x-show="!isProcessing">Procéder au paiement</span>
                            <span x-show="isProcessing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement en cours...
                            </span>
                            <svg x-show="!isProcessing" class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Progress Steps -->
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="hidden md:flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">1</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-indigo-600">Sélection</p>
                                </div>
                            </div>
                            <div class="w-16 h-0.5 bg-gray-200"></div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-600 font-medium">2</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-gray-500">Paiement</p>
                                </div>
                            </div>
                            <div class="w-16 h-0.5 bg-gray-200"></div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-600 font-medium">3</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-gray-500">Confirmation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
