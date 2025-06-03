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
            coutMensuelParEmploye: {{ $coutMensuelParEmploye ?? 'Math.round(coutTotal/companySize)' }},
            coutAnnuel: {{ $coutAnnuel ?? 'coutTotal * 12' }},
            isLoading: false,
            
            formatNumber(number) {
                return new Intl.NumberFormat('fr-FR').format(number);
            },
            
            getPlanIcon() {
                if (this.forfait.includes('Starter')) {
                    return 'https://cdn-icons-png.flaticon.com/512/8088/8088179.png';
                } else if (this.forfait.includes('Business')) {
                    return 'https://cdn-icons-png.flaticon.com/512/8088/8088117.png';
                } else if (this.forfait.includes('Enterprise')) {
                    return 'https://cdn-icons-png.flaticon.com/512/8088/8088522.png';
                }
                return 'https://cdn-icons-png.flaticon.com/512/8088/8088117.png';
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
                                <h3 class="text-2xl font-bold text-indigo-700" x-text="forfait"></h3>
                                <span class="ml-3 px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">Recommandé</span>
                            </div>
                            <div class="text-3xl font-bold text-gray-800 mb-2">
                                <span x-text="formatNumber(coutTotal)"></span> FCFA<span class="text-gray-500 text-lg font-normal">/mois</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Soit <span x-text="formatNumber(coutMensuelParEmploye)"></span> FCFA par utilisateur/mois</p>
                            <p class="text-gray-600 text-sm mb-4">Adapté pour <span class="font-semibold">{{ $forfait }}</span> (<span x-text="companySize"></span> employés)</p>
                        </div>
                        
                        <div class="mt-4 md:mt-0">
                            <img :src="getPlanIcon()" :alt="forfait" class="h-24 w-24 mx-auto md:mx-0">
                        </div>
                    </div>
                </div>
                
                <!-- Cost Breakdown -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails de votre abonnement</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Coût fixe ({{ $forfait }})</span>
                            <span x-text="formatNumber(coutFixe) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Coût par utilisateur</span>
                            <span>{{ $planDetails['cout_par_employe'] ?? 100 }} FCFA × <span x-text="companySize"></span> utilisateurs = <span x-text="formatNumber(coutUtilisateurs) + ' FCFA'"></span></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 font-semibold">
                            <span>Total mensuel</span>
                            <span x-text="formatNumber(coutTotal) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 text-gray-600">
                            <span>Coût mensuel par employé</span>
                            <span x-text="formatNumber(coutMensuelParEmploye) + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between py-2 text-gray-600">
                            <span>Total annuel (12 mois)</span>
                            <span x-text="formatNumber(coutAnnuel) + ' FCFA'"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Included Features -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Fonctionnalités incluses</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Fonctionnalités de base pour tous les plans -->
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
                            <span class="text-gray-700">Rapports basiques</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Mises à jour gratuites</span>
                        </div>
                        
                        <!-- Fonctionnalités pour Business et Enterprise -->
                        <div x-show="forfait.includes('Business') || forfait.includes('Enterprise')" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Rapports avancés</span>
                        </div>
                        <div x-show="forfait.includes('Business') || forfait.includes('Enterprise')" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Intégration système externe</span>
                        </div>
                        <div x-show="forfait.includes('Business') || forfait.includes('Enterprise')" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Personnalisation workflow</span>
                        </div>
                        
                        <!-- Fonctionnalités exclusives Enterprise -->
                        <div x-show="forfait.includes('Enterprise')" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Support dédié</span>
                        </div>
                        <div x-show="forfait.includes('Enterprise')" class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">API complète</span>
                        </div>
                    </div>
                </div>
                
                <!-- Form for Subscription Selection -->
                <form id="subscription-form" action="{{ route('workflow.subscription.store') }}" method="POST" x-on:submit="isLoading = true">
                    @csrf
                    <input type="hidden" name="subscription_plan" :value="forfait">
                    <input type="hidden" name="base_cost" :value="coutFixe">
                    <input type="hidden" name="user_cost" :value="coutUtilisateurs">
                    <input type="hidden" name="total_cost" :value="coutTotal">
                    <input type="hidden" name="company_size" :value="companySize">
                    <input type="hidden" name="monthly_cost_per_employee" :value="coutMensuelParEmploye">
                    <input type="hidden" name="annual_cost" :value="coutAnnuel">
                    <input type="hidden" name="plan_level" value="{{ strpos($forfait, 'Enterprise') !== false ? 3 : (strpos($forfait, 'Business') !== false ? 2 : 1) }}">
                    <input type="hidden" name="plan_features" value="{{ json_encode($planDetails['fonctionnalites'] ?? []) }}">
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm text-gray-700 mb-2">Vous avez sélectionné le plan <span class="font-semibold" x-text="forfait"></span> pour <span x-text="companySize"></span> employés.</p>
                        <p class="text-sm text-gray-700">Cliquez sur le bouton ci-dessous pour continuer vers le paiement.</p>
                    </div>
                    
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('workflow.company') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 inline-flex items-center text-sm font-medium">
                            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Retour
                        </a>
                        
                        <button type="submit" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 inline-flex items-center text-sm font-medium" :disabled="isLoading">
                            <span x-show="!isLoading">Continuer vers le paiement</span>
                            <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-show="isLoading">Chargement...</span>
                            <svg x-show="!isLoading" class="ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
