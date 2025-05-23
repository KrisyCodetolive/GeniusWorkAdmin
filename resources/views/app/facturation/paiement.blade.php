<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GENIUS WORK - La Solution Innovante pour la Gestion des Présences</title>
    
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
            scroll-behavior: smooth;
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
        
        .gradient-border {
            position: relative;
            border-radius: 0.5rem;
            background: white;
        }
        
        .gradient-border::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 0.6rem;
            background: var(--primary-gradient);
            z-index: -1;
            transition: opacity 0.3s ease;
            opacity: 0;
        }
        
        .gradient-border:hover::before {
            opacity: 1;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: var(--primary-gradient);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
        }
    </style>
    @livewireStyles()
</head>
<body class="min-h-screen bg-gradient-to-b from-indigo-50/50 to-blue-50/50 font-sans text-gray-800 overflow-x-hidden">

    <!-- Message d'erreur -->
    @if(session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Message de succès -->
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

@php
    // Initialiser les variables avec des valeurs par défaut
    $selectedGateway = $selectedGateway ?? '';
    $selectedMethod = $selectedMethod ?? '';
    
    // Récupérer les factures impayées de l'entreprise de l'utilisateur connecté
    $entrepriseId = auth()->user()->entreprise_id;
    $facturesImpayees = isset($facturesImpayees) ? $facturesImpayees : \App\Models\Facturation::where('entreprise_id', $entrepriseId)
        ->where('statut_paiement', 'en_attente')
        ->where('date_facturation', '<', now())
        ->orderBy('date_facturation', 'asc')
        ->get();
    
    // Sélectionner la première facture impayée pour l'affichage principal
    $facture = $facture ?? ($facturesImpayees->first() ?? null);
    
    // Calculer le montant total des factures impayées
    $montantTotalImpaye = $montantTotalImpaye ?? $facturesImpayees->sum('montant_ttc');
    
    // Récupérer l'ID de facturation
    $facturationId = $facturationId ?? ($facture ? $facture->id : null);
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('paymentForm', () => ({
            selectedGateway: '{{ $selectedGateway }}',
            selectedMethod: '{{ $selectedMethod }}',
            facturationId: '{{ $facturationId ?? null }}',
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
                this.selectedMethod = null; // Réinitialiser la méthode sélectionnée
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
                    // Soumettre le formulaire directement au lieu d'utiliser fetch
                    // Cela permettra au navigateur de suivre les redirections naturellement
                    // et d'éviter les problèmes CORS avec les passerelles de paiement externes
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
    
    .payment-method-item:nth-child(1) { transition-delay: 0.1s; }
    .payment-method-item:nth-child(2) { transition-delay: 0.2s; }
    .payment-method-item:nth-child(3) { transition-delay: 0.3s; }
    
    /* Animation du loader */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loader {
        border-radius: 50%;
        width: 1.5em;
        height: 1.5em;
        border: 0.25em solid rgba(255, 255, 255, 0.2);
        border-top-color: white;
        animation: spin 1s infinite linear;
    }
    
    /* Animation pour le bouton */
    .btn-payment {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .btn-payment:active {
        transform: translateY(1px);
    }
    
    /* Responsive styles for buttons */
    @media (max-width: 640px) {
        .btn-payment, a[href="{{ route('workflow.subscription') }}"] {
            width: 100%;
            margin-bottom: 0.5rem;
            justify-content: center;
        }
    }
    
    /* Progress indicator animation */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }
    
    .bg-indigo-600.rounded-full {
        animation: pulse 2s infinite;
    }
</style>
<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500 mb-2">
                Votre Abonnement à Expiré
            </h1>
            <p class="text-lg text-gray-600">
                Veuillez effectuer le paiement de votre abonnement pour continuer à utiliser le service.
            </p>
        </div>

        <!-- Steps indicator -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div class="flex-1 flex items-center">
                    <div class="bg-green-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-2 font-medium text-green-600">Abonnement</div>
                    <div class="flex-1 h-1 mx-4 bg-indigo-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        4
                    </div>
                    <div class="ml-2 font-medium text-indigo-600">Paiement</div>
                    <div class="flex-1 h-1 mx-4 bg-gray-200"></div>
                </div>
                <div class="flex items-center">
                    <div class="bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center text-gray-500 font-bold">
                        5
                    </div>
                    <div class="ml-2 font-medium text-gray-500">Tableau de Bord</div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden" x-data="paymentForm">
            <form x-ref="paymentForm" method="POST" action="{{ route('paiement.initialiser', ['facturationId' => $facturationId]) }}">
                @csrf
                <input type="hidden" id="selected_gateway" name="passerelle" value="{{ $selectedGateway }}">
                <input type="hidden" id="selected_method" name="methode" value="{{ $selectedMethod }}">
                <input type="hidden" name="facturation_id" value="{{ $facturationId }}">
                <div class="p-8">
                    <!-- Facture impayée -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Détails de la facture</h2>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <!-- Alerte facture impayée -->
                            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 flex items-start">
                                <div class="mr-3 text-amber-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $facturesImpayees->count() > 1 ? $facturesImpayees->count().' factures en attente de paiement' : 'Facture en attente de paiement' }}</p>
                                    <p class="text-sm">Veuillez procéder au règlement pour continuer à bénéficier de tous les services.</p>
                                </div>
                            </div>
                            
                            @if(session('warning'))
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 flex items-start">
                                <div class="mr-3 text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">Attention</p>
                                    <p class="text-sm">{{ session('warning') }}</p>
                                </div>
                            </div>
                            @endif
                            
                            <div class="space-y-3">
                                @if($facture)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Numéro de facture</span>
                                    <span class="font-medium text-gray-800">{{ $facture->numero_facture }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date d'émission</span>
                                    <span class="font-medium text-gray-800">{{ $facture->date_facturation->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date d'échéance</span>
                                    <span class="font-medium text-gray-800 {{ $facture->date_echeance < now() ? 'text-red-600' : '' }}">
                                        {{ $facture->date_echeance->format('d/m/Y') }}
                                        @if($facture->date_echeance < now())
                                            <span class="text-xs ml-2 text-red-600">(Dépassée)</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Statut</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $facture->date_echeance < now() ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $facture->date_echeance < now() ? 'En retard' : 'En attente de paiement' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Description</span>
                                    <span class="font-medium text-gray-800">{{ $facture->description ?? 'Abonnement GENIUS WORK' }}</span>
                                </div>
                                @endif
                                
                                @if($facturesImpayees->count() > 1)
                                <div class="border-t border-gray-200 my-2"></div>
                                <div class="bg-gray-100 p-3 rounded-lg">
                                    <h3 class="font-medium text-gray-800 mb-2">Autres factures impayées</h3>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @foreach($facturesImpayees->skip(1) as $autreFacture)
                                        <div class="flex justify-between items-center text-sm">
                                            <span>{{ $autreFacture->numero_facture }}</span>
                                            <div class="flex items-center">
                                                <span class="mr-3">{{ number_format($autreFacture->montant_ttc, 0, ',', ' ') }} FCFA</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $autreFacture->date_echeance < now() ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $autreFacture->date_echeance->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="border-t border-gray-200 my-2"></div>
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-800">{{ $facturesImpayees->count() > 1 ? 'Total à payer (toutes factures)' : 'Total à payer' }}</span>
                                    <span class="text-indigo-600">{{ number_format($montantTotalImpaye, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Gateways -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Choisissez votre passerelle de paiement</h3>
                        
                        <!-- Error message -->
                        <div x-show="errorMessage" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700" x-text="errorMessage"></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Passerelles de paiement -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <!-- Paystack Gateway -->
                            <div class="payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedGateway === 'paystack' ? 'selected' : '' }}" data-gateway="paystack" @click="selectGateway('paystack')">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Paystack</h3>
                                        <p class="text-sm text-gray-500">Paiement sécurisé par carte ou mobile money</p>
                                    </div>
                                    <div class="ml-auto">
                                        <svg x-show="isGatewaySelected('paystack')" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stripe Gateway
                            <div class="payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedGateway === 'stripe' ? 'selected' : '' }}" data-gateway="stripe" @click="selectGateway('stripe')">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Stripe</h3>
                                        <p class="text-sm text-gray-500">Paiement international par carte bancaire</p>
                                    </div>
                                    <div class="ml-auto">
                                        <svg x-show="isGatewaySelected('stripe')" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div> -->
                            
                            <!-- Manuel Gateway 
                            <div class="payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedGateway === 'manuel' ? 'selected' : '' }}" data-gateway="manuel" @click="selectGateway('manuel')">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Manuel</h3>
                                        <p class="text-sm text-gray-500">Paiement par virement, chèque ou espèces</p>
                                    </div>
                                    <div class="ml-auto">
                                        <svg x-show="isGatewaySelected('manuel')" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        
                        <!-- Méthodes de paiement -->
                        <div x-show="selectedGateway" class="mt-8 transition-all duration-300 ease-in-out" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0">
                            <h3 class="text-lg font-semibold mb-4">Choisissez votre méthode de paiement</h3>
                            
                            <!-- Paystack Methods -->
                            <div x-show="isGatewaySelected('paystack')" class="payment-method-container {{ $selectedGateway === 'paystack' ? 'active' : '' }}" id="paystack-methods">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <!-- Card Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'card' ? 'selected' : '' }}" data-method="card" @click="selectMethod('card')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Carte bancaire</h4>
                                                <div x-show="isMethodSelected('card')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4 mb-3">
                                                <!-- Visa Card -->
                                                <div class="h-8 w-12 bg-gradient-to-r from-blue-700 to-blue-600 rounded shadow-sm flex items-center justify-center relative overflow-hidden">
                                                    <span class="text-white font-bold tracking-wider text-xs">VISA</span>
                                                    <div class="absolute top-0 right-0 w-4 h-4 bg-yellow-400 rounded-full opacity-50 transform translate-x-2 -translate-y-2"></div>
                                                </div>
                                                
                                                <!-- Mastercard -->
                                                <div class="h-8 w-12 bg-gray-100 rounded shadow-sm flex items-center justify-center relative overflow-hidden">
                                                    <div class="absolute left-1 w-5 h-5 bg-red-500 rounded-full"></div>
                                                    <div class="absolute right-1 w-5 h-5 bg-yellow-500 rounded-full"></div>
                                                    <div class="absolute w-3 h-5 bg-orange-500 rounded-full"></div>
                                                </div>
                                                
                                                <!-- Apple Pay -->
                                                <div class="h-8 w-8 bg-black rounded-full shadow-sm flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M14.94 5.19A4.38 4.38 0 0 0 16 2a4.44 4.44 0 0 0-3 1.52 4.17 4.17 0 0 0-1 3.09 3.69 3.69 0 0 0 2.94-1.42z" fill="currentColor"/>
                                                        <path d="M20 16.41c-.78 1.28-1.51 2.56-2.72 2.59-1.18.03-1.57-.7-2.93-.7-1.35 0-1.78.68-2.91.72-1.17.05-2.06-1.39-2.85-2.67-1.55-2.37-2.73-6.7-.68-9.62.82-1.16 2.27-1.91 3.85-1.93 1.2-.02 2.34.81 3.07.81.74 0 2.12-1 3.58-.85.61.03 2.31.25 3.41 1.84-2.05 1.17-1.84 4.07.18 5.07-.44 1.24-1.04 2.49-2 3.74z" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement sécurisé par carte de crédit ou débit</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Mobile Money Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'mobile_money' ? 'selected' : '' }}" data-method="mobile_money" @click="selectMethod('mobile_money')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Mobile Money</h4>
                                                <div x-show="isMethodSelected('mobile_money')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement via votre compte mobile money</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stripe Methods -->
                            <div x-show="isGatewaySelected('stripe')" class="payment-method-container {{ $selectedGateway === 'stripe' ? 'active' : '' }}" id="stripe-methods">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <!-- Card Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'card' ? 'selected' : '' }}" data-method="card" @click="selectMethod('card')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Carte bancaire</h4>
                                                <div x-show="isMethodSelected('card')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4 mb-3">
                                                <!-- Visa Card -->
                                                <div class="h-8 w-12 bg-gradient-to-r from-blue-700 to-blue-600 rounded shadow-sm flex items-center justify-center relative overflow-hidden">
                                                    <span class="text-white font-bold tracking-wider text-xs">VISA</span>
                                                    <div class="absolute top-0 right-0 w-4 h-4 bg-yellow-400 rounded-full opacity-50 transform translate-x-2 -translate-y-2"></div>
                                                </div>
                                                
                                                <!-- Mastercard -->
                                                <div class="h-8 w-12 bg-gray-100 rounded shadow-sm flex items-center justify-center relative overflow-hidden">
                                                    <div class="absolute left-1 w-5 h-5 bg-red-500 rounded-full"></div>
                                                    <div class="absolute right-1 w-5 h-5 bg-yellow-500 rounded-full"></div>
                                                    <div class="absolute w-3 h-5 bg-orange-500 rounded-full"></div>
                                                </div>
                                                
                                                <!-- Apple Pay -->
                                                <div class="h-8 w-8 bg-black rounded-full shadow-sm flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M14.94 5.19A4.38 4.38 0 0 0 16 2a4.44 4.44 0 0 0-3 1.52 4.17 4.17 0 0 0-1 3.09 3.69 3.69 0 0 0 2.94-1.42z" fill="currentColor"/>
                                                        <path d="M20 16.41c-.78 1.28-1.51 2.56-2.72 2.59-1.18.03-1.57-.7-2.93-.7-1.35 0-1.78.68-2.91.72-1.17.05-2.06-1.39-2.85-2.67-1.55-2.37-2.73-6.7-.68-9.62.82-1.16 2.27-1.91 3.85-1.93 1.2-.02 2.34.81 3.07.81.74 0 2.12-1 3.58-.85.61.03 2.31.25 3.41 1.84-2.05 1.17-1.84 4.07.18 5.07-.44 1.24-1.04 2.49-2 3.74z" fill="currentColor"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement sécurisé international par carte</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Manuel Methods -->
                            <div x-show="isGatewaySelected('manuel')" class="payment-method-container {{ $selectedGateway === 'manuel' ? 'active' : '' }}" id="manuel-methods">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <!-- Virement Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'virement' ? 'selected' : '' }}" data-method="virement" @click="selectMethod('virement')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Virement bancaire</h4>
                                                <div x-show="isMethodSelected('virement')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement par virement bancaire</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Cheque Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'cheque' ? 'selected' : '' }}" data-method="cheque" @click="selectMethod('cheque')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Chèque</h4>
                                                <div x-show="isMethodSelected('cheque')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement par chèque bancaire</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Especes Method -->
                                    <div class="payment-method-item payment-card rounded-lg bg-white p-4 border shadow-sm hover:shadow-md cursor-pointer {{ $selectedMethod === 'especes' ? 'selected' : '' }}" data-method="especes" @click="selectMethod('especes')">
                                        <div class="p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Espèces</h4>
                                                <div x-show="isMethodSelected('especes')" class="bg-blue-500 rounded-full p-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Paiement en espèces</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="bg-gray-50 px-6 py-4 sm:px-8 border-t border-gray-200 mt-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <a 
                            href="{{ url('/admin') }}" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Tableau de bord
                        </a>
                        
                        <button 
                            type="button"
                            @click="processPayment"
                            :disabled="isProcessing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out"
                            x-bind:class="{'opacity-75 cursor-not-allowed': isProcessing}"
                        >
                            <span x-show="!isProcessing">
                                Procéder au paiement
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                            <span x-show="isProcessing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement en cours...
                            </span>
                        </button>
                    </div>
                    
                    <!-- Payment Progress Indicator -->
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="hidden md:flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">1</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-gray-900">Abonnement</p>
                                </div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="h-1 w-full bg-indigo-600 rounded"></div>
                            </div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">2</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-gray-900">Paiement</p>
                                </div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="h-1 w-full bg-gray-200 rounded"></div>
                            </div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-500 font-medium">3</span>
                                </div>
                                <div class="ml-2">
                                    <p class="text-xs font-medium text-gray-500">Confirmation</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Progress Indicator -->
                        <div class="md:hidden flex justify-center items-center mt-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">1</span>
                                </div>
                                <div class="w-8 h-1 bg-indigo-600"></div>
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">2</span>
                                </div>
                                <div class="w-8 h-1 bg-gray-200"></div>
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-500 font-medium">3</span>
                                </div>
                            </div>
                        </div>
                        <div class="md:hidden flex justify-center text-xs text-gray-500 mt-2">
                            <span class="px-2 font-medium text-indigo-600">Paiement</span>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>



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
            offset: 50,
        });
    });
</script>

<!-- Additional Scripts -->
@stack('scripts')
@livewireScripts()
</body>
</html>
