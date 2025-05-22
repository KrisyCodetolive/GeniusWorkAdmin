@extends('layouts.app')

@section('title', 'Instructions de paiement manuel')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <!-- En-tête avec statut de paiement -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Instructions de paiement</h1>
                    <div class="bg-blue-500 bg-opacity-50 px-4 py-2 rounded-full text-sm font-medium">
                        Statut: En attente de paiement
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                <!-- Notification de méthode de paiement -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-r-md shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-md text-blue-800 font-medium">
                                Vous avez choisi de payer la facture <span class="font-bold">{{ $paiement->facturation->numero_facture }}</span> d'un montant de <span class="font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span> par <span class="font-bold text-blue-700">{{ ucfirst($paiement->methode) }}</span>.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Détails de la facture -->
                <div class="bg-white rounded-lg p-6 mb-8 shadow-md border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Détails de la facture
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">Numéro de facture:</span>
                                <span class="font-medium text-gray-900">{{ $paiement->facturation->numero_facture }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium text-gray-900">{{ $paiement->facturation->date_facturation->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">Montant HT:</span>
                                <span class="font-medium text-gray-900">{{ number_format($paiement->facturation->montant_ht, 0, ',', ' ') }} {{ $paiement->devise }}</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">TVA ({{ $paiement->facturation->taux_tva }}%):</span>
                                <span class="font-medium text-gray-900">{{ number_format($paiement->facturation->montant_tva, 0, ',', ' ') }} {{ $paiement->devise }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">Montant TTC:</span>
                                <span class="font-bold text-blue-700">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-gray-600">Référence de paiement:</span>
                                <span class="font-medium text-gray-900">{{ $paiement->reference }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions de paiement -->
                <div class="bg-white rounded-lg p-6 mb-8 shadow-md border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Instructions de paiement
                    </h2>
                    
                    @if($paiement->methode === 'virement')
                        <div class="mb-6 border border-gray-100 rounded-lg overflow-hidden">
                            <!-- En-tête de la section -->
                            <div class="bg-blue-50 p-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Coordonnées bancaires
                                </h3>
                            </div>
                            
                            <!-- Contenu de la section -->
                            <div class="p-5 bg-white">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <span class="text-gray-600">Banque:</span>
                                            <span class="font-medium text-gray-900">{{ config('paiement.manuel.banque') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <span class="text-gray-600">Titulaire du compte:</span>
                                            <span class="font-medium text-gray-900">{{ config('paiement.manuel.beneficiaire') }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <span class="text-gray-600">IBAN:</span>
                                            <span class="font-medium text-gray-900">{{ config('paiement.manuel.iban') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <span class="text-gray-600">BIC/SWIFT:</span>
                                            <span class="font-medium text-gray-900">{{ config('paiement.manuel.bic') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 bg-yellow-50 p-4 rounded-lg">
                                    <p class="flex items-center text-yellow-800">
                                        <svg class="h-5 w-5 mr-2 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span class="font-medium">Important:</span>
                                        <span class="ml-1">Indiquez la référence <span class="font-bold text-yellow-900">{{ $paiement->reference }}</span> lors de votre virement.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                          
                        <div class="p-5 bg-white">
                            <div class="text-center mb-6">
                                <p class="text-gray-700 mb-4">Payez instantanément avec votre mobile en cliquant sur le bouton ci-dessous:</p>
                                <a href="{{ config('paiement.manuel.methods.especes.wave_link') }}?amount={{ $paiement->montant }}" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                    <svg class="-ml-1 mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Payer avec Wave
                                </a>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">Vous serez redirigé vers la plateforme Wave pour finaliser votre paiement de <span class="font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span>. Votre référence de paiement <span class="font-bold">{{ $paiement->reference }}</span> sera automatiquement associée à votre transaction.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($paiement->methode === 'cheque')
                        <div class="mb-6 border border-gray-100 rounded-lg overflow-hidden">
                            <!-- En-tête de la section -->
                            <div class="bg-blue-50 p-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Instructions pour le paiement par chèque
                                </h3>
                            </div>
                            
                            <!-- Contenu de la section -->
                            <div class="p-5 bg-white">
                                <div class="mb-4">
                                    <p class="text-gray-600 mb-1">Libeller le chèque à l'ordre de:</p>
                                    <p class="font-semibold text-gray-900 text-lg">{{ config('paiement.manuel.ordre') }}</p>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-gray-600 mb-1">Adresse d'envoi:</p>
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="font-medium text-gray-900">
                                            {{ config('paiement.manuel.methods.check.adresse_ligne1') }}<br>
                                            
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 bg-yellow-50 p-4 rounded-lg">
                                    <p class="flex items-center text-yellow-800">
                                        <svg class="h-5 w-5 mr-2 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span class="font-medium">Important:</span>
                                        <span class="ml-1">Indiquez la référence <span class="font-bold text-yellow-900">{{ $paiement->reference }}</span> au dos du chèque.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                          
                        <div class="p-5 bg-white">
                            <div class="text-center mb-6">
                                <p class="text-gray-700 mb-4">Payez instantanément avec votre mobile en cliquant sur le bouton ci-dessous:</p>
                                <a href="{{ config('paiement.manuel.methods.especes.wave_link') }}?amount={{ $paiement->montant }}" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                    <svg class="-ml-1 mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Payer avec Wave
                                </a>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">Vous serez redirigé vers la plateforme Wave pour finaliser votre paiement de <span class="font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span>. Votre référence de paiement <span class="font-bold">{{ $paiement->reference }}</span> sera automatiquement associée à votre transaction.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($paiement->methode === 'especes')
                        <!-- Option 1: Paiement en espèces en personne -->
                        <div class="mb-6 border border-gray-100 rounded-lg overflow-hidden">
                            <div class="bg-blue-50 p-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Option 1: Paiement en espèces à nos bureaux
                                </h3>
                            </div>
                            
                            <div class="p-5 bg-white">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-600 mb-1">Adresse du bureau:</p>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="font-medium text-gray-900">
                                                {{ config('paiement.manuel.methods.especes.adresse_ligne1') }}<br>
                                               
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 mb-1">Horaires d'ouverture:</p>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="font-medium text-gray-900">{{ config('paiement.manuel.methods.especes.horaires') }}</p>
                                        </div>
                                        <p class="text-gray-600 mt-3 mb-1">Référence à mentionner:</p>
                                        <p class="font-bold text-blue-700">{{ $paiement->reference }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-5 bg-white">
                            <div class="text-center mb-6">
                                <p class="text-gray-700 mb-4">Payez instantanément avec votre mobile en cliquant sur le bouton ci-dessous:</p>
                                <a href="{{ config('paiement.manuel.methods.especes.wave_link') }}?amount={{ $paiement->montant }}" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                    <svg class="-ml-1 mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Payer avec Wave
                                </a>
                            </div>
                            
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">Vous serez redirigé vers la plateforme Wave pour finaliser votre paiement de <span class="font-bold">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}</span>. Votre référence de paiement <span class="font-bold">{{ $paiement->reference }}</span> sera automatiquement associée à votre transaction.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Notification importante -->
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-5 mb-6 shadow-sm border border-yellow-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-yellow-400 rounded-full p-2">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-yellow-800 mb-1">Important</h4>
                                <p class="text-yellow-700">
                                    Après avoir effectué votre paiement, veuillez conserver votre preuve de paiement. Notre équipe validera votre paiement dans les plus brefs délais et vous recevrez une confirmation par email.
                                </p>
                                <p class="mt-2 text-yellow-700 font-medium">
                                    Délai de traitement estimé: <span class="font-bold">24-48 heures ouvrées</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section d'aide et contact -->
                <div class="bg-white rounded-lg p-6 shadow-md border border-gray-100 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Besoin d'aide ?
                    </h3>
                    
                    <p class="text-gray-600 mb-4">Si vous avez des questions concernant votre paiement, n'hésitez pas à contacter notre service client:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contacts directs -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-full p-2 mr-3">
                                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">WhatsApp</p>
                                    <a href="{{ config('paiement.support.whatsapp') }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">Discuter avec un conseiller</a>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-full p-2 mr-3">
                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Email</p>
                                    <a href="mailto:{{ config('paiement.support.email') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ config('paiement.support.email') }}</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations supplémentaires -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-full p-2 mr-3">
                                    <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Téléphone</p>
                                    <a href="tel:{{ config('paiement.support.telephone') }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ config('paiement.support.telephone') }}</a>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2 mr-3">
                                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Horaires</p>
                                    <p class="text-gray-700">{{ config('paiement.support.horaires') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
