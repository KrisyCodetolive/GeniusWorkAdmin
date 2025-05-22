@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('subscriptionForm', () => ({
            companySize: {{ $companySize ?? 1 }},
            forfait: '{{ $forfait }}',
            coutFixe: {{ $coutFixe }},
            coutUtilisateurs: {{ $coutUtilisateurs }},
            coutTotal: {{ $coutTotal }},
            isLoading: false,
            
            formatNumber(number) {
                return new Intl.NumberFormat('fr-FR').format(number);
            },
            
            submitForm() {
                this.isLoading = true;
                document.getElementById('subscription-form').submit();
            }
        }));
    });
</script>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500 mb-2">
                Sélection de l'abonnement
            </h1>
            <p class="text-lg text-gray-600">
                Choisissez l'abonnement qui correspond à vos besoins
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
                    <div class="ml-2 font-medium text-green-600">Compte utilisateur</div>
                    <div class="flex-1 h-1 mx-4 bg-green-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-green-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-2 font-medium text-green-600">Compte entreprise</div>
                    <div class="flex-1 h-1 mx-4 bg-indigo-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        3
                    </div>
                    <div class="ml-2 font-medium text-indigo-600">Abonnement</div>
                    <div class="flex-1 h-1 mx-4 bg-gray-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center text-gray-500 font-bold">
                        4
                    </div>
                    <div class="ml-2 font-medium text-gray-500">Paiement</div>
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

        <!-- Subscription Selection -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden" x-data="subscriptionForm">
            <div class="p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Votre forfait recommandé</h2>
                    <p class="text-gray-600">Basé sur votre taille d'entreprise de <span class="font-semibold" x-text="companySize"></span> employés</p>
                </div>
                
                <!-- Plan Details -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6 mb-8 border border-indigo-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <div class="flex items-center mb-4">
                                <span class="text-xl font-bold text-indigo-600 mr-3" x-text="forfait"></span>
                                <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full font-medium">Recommandé</span>
                            </div>
                            <p class="text-gray-700 mb-4">Idéal pour les entreprises de <span x-text="forfait === 'Starter' ? '1 à 50' : forfait === 'Side Business' ? '51 à 100' : '100+'"></span> employés</p>
                            <div class="flex items-baseline mb-1">
                                <span class="text-3xl font-bold text-gray-900" x-text="formatNumber(coutTotal)"></span>
                                <span class="text-gray-600 ml-1">FCFA / mois</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Soit <span x-text="formatNumber(Math.round(coutTotal/companySize))"></span> FCFA par utilisateur/mois</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <img x-show="forfait === 'Starter'" src="https://cdn-icons-png.flaticon.com/512/8088/8088179.png" alt="Starter" class="h-24 w-24 mx-auto md:mx-0">
                            <img x-show="forfait === 'Side Business'" src="https://cdn-icons-png.flaticon.com/512/8090/8090156.png" alt="Side Business" class="h-24 w-24 mx-auto md:mx-0">
                            <img x-show="forfait === 'Enterprise'" src="https://cdn-icons-png.flaticon.com/512/8088/8088522.png" alt="Enterprise" class="h-24 w-24 mx-auto md:mx-0">
                        </div>
                    </div>
                </div>
                
                <!-- Cost Breakdown -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails de votre abonnement</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Coût fixe</span>
                            <span class="font-medium text-gray-800" x-text="formatNumber(coutFixe) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">
                                Coût par utilisateur (<span x-text="companySize"></span> x 100 FCFA)
                            </span>
                            <span class="font-medium text-gray-800" x-text="formatNumber(coutUtilisateurs) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 font-bold">
                            <span class="text-gray-800">Total mensuel</span>
                            <span class="text-indigo-600" x-text="formatNumber(coutTotal) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 text-sm text-gray-500 italic">
                            <span>Total annuel (12 mois)</span>
                            <span x-text="formatNumber(coutTotal * 12) + ' FCFA'"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Included Features -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Fonctionnalités incluses</h3>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Gestion des présences</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Gestion des congés</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Rapports de présence</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Application mobile</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Support technique</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Mises à jour gratuites</span>
                        </div>
                        <div x-show="forfait !== 'Starter'" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Rapports avancés</span>
                        </div>
                        <div x-show="forfait !== 'Starter'" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Intégration système externe</span>
                        </div>
                        <div x-show="forfait === 'Enterprise'" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Support dédié</span>
                        </div>
                        <div x-show="forfait === 'Enterprise'" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">API complète</span>
                        </div>
                    </div>
                </div>
                
                <!-- Form for Subscription Selection -->
                <form id="subscription-form" action="{{ route('workflow.subscription.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subscription_plan" :value="forfait">
                    <input type="hidden" name="base_cost" :value="coutFixe">
                    <input type="hidden" name="user_cost" :value="coutUtilisateurs">
                    <input type="hidden" name="total_cost" :value="coutTotal">
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <a 
                            href="{{ route('workflow.company') }}" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Retour
                        </a>
                        <button 
                            type="button" 
                            @click="submitForm()"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :disabled="isLoading"
                        >
                            <span x-show="!isLoading">Choisir cet abonnement</span>
                            <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="isLoading">Chargement...</span>
                            <svg x-show="!isLoading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
