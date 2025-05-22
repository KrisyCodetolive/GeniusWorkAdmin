@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('companyForm', () => ({
            companyName: '',
            industry: '',
            address: '',
            companySize: {{ session('user_number') ?? 1 }},
            contactPhone: '',
            contactEmail: '',
            isLoading: false,
            errors: {
                companyName: false,
                industry: false,
                address: false,
                companySize: false,
                contactPhone: false,
                contactEmail: false
            },
            errorMessages: {
                companyName: '',
                industry: '',
                address: '',
                companySize: '',
                contactPhone: '',
                contactEmail: ''
            },
            
            validateCompanyName() {
                if (!this.companyName.trim()) {
                    this.errors.companyName = true;
                    this.errorMessages.companyName = 'Veuillez entrer le nom de votre entreprise';
                    return false;
                }
                this.errors.companyName = false;
                this.errorMessages.companyName = '';
                return true;
            },
            
            validateIndustry() {
                if (!this.industry.trim()) {
                    this.errors.industry = true;
                    this.errorMessages.industry = 'Veuillez sélectionner un secteur d\'activité';
                    return false;
                }
                this.errors.industry = false;
                this.errorMessages.industry = '';
                return true;
            },
            
            validateAddress() {
                if (!this.address.trim()) {
                    this.errors.address = true;
                    this.errorMessages.address = 'Veuillez entrer l\'adresse de l\'entreprise';
                    return false;
                }
                this.errors.address = false;
                this.errorMessages.address = '';
                return true;
            },
            
            validateCompanySize() {
                const size = parseInt(this.companySize);
                if (isNaN(size) || size < 1) {
                    this.errors.companySize = true;
                    this.errorMessages.companySize = 'Le nombre de personnel doit être au moins 1';
                    return false;
                }
                this.errors.companySize = false;
                this.errorMessages.companySize = '';
                return true;
            },
            
            validateContactPhone() {
                const phoneRegex = /^(\+\d{1,4})?[0-9]{9,14}$/;
                if (!this.contactPhone.trim()) {
                    this.errors.contactPhone = true;
                    this.errorMessages.contactPhone = 'Veuillez entrer un numéro de téléphone';
                    return false;
                } else if (!phoneRegex.test(this.contactPhone)) {
                    this.errors.contactPhone = true;
                    this.errorMessages.contactPhone = 'Veuillez entrer un numéro de téléphone valide';
                    return false;
                }
                this.errors.contactPhone = false;
                this.errorMessages.contactPhone = '';
                return true;
            },
            
            validateContactEmail() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!this.contactEmail.trim()) {
                    this.errors.contactEmail = true;
                    this.errorMessages.contactEmail = 'Veuillez entrer une adresse e-mail professionnelle';
                    return false;
                } else if (!emailRegex.test(this.contactEmail)) {
                    this.errors.contactEmail = true;
                    this.errorMessages.contactEmail = 'Veuillez entrer une adresse e-mail valide';
                    return false;
                }
                this.errors.contactEmail = false;
                this.errorMessages.contactEmail = '';
                return true;
            },
            
            validateForm() {
                const isCompanyNameValid = this.validateCompanyName();
                const isIndustryValid = this.validateIndustry();
                const isAddressValid = this.validateAddress();
                const isCompanySizeValid = this.validateCompanySize();
                const isContactPhoneValid = this.validateContactPhone();
                const isContactEmailValid = this.validateContactEmail();
                
                return isCompanyNameValid && isIndustryValid && isAddressValid && 
                       isCompanySizeValid && isContactPhoneValid && isContactEmailValid;
            },
            
            submitForm() {
                if (this.validateForm()) {
                    this.isLoading = true;
                    document.getElementById('company-form').submit();
                }
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
                Créer votre compte entreprise
            </h1>
            <p class="text-lg text-gray-600">
                Informations sur votre entreprise
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
                    <div class="flex-1 h-1 mx-4 bg-indigo-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        2
                    </div>
                    <div class="ml-2 font-medium text-indigo-600">Compte entreprise</div>
                    <div class="flex-1 h-1 mx-4 bg-gray-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center text-gray-500 font-bold">
                        3
                    </div>
                    <div class="ml-2 font-medium text-gray-500">Abonnement</div>
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

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8" x-data="companyForm">
                @if ($errors->any())
                <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Veuillez corriger les erreurs suivantes:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                
                <form id="company-form" action="{{ route('workflow.company.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <!-- Nom de l'entreprise -->
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="company_name" 
                                    name="company_name" 
                                    x-model="companyName"
                                    @blur="validateCompanyName()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.companyName, 'border-gray-300': !errors.companyName}"
                                    placeholder="Nom de votre entreprise"
                                    value="{{ old('company_name') }}"
                                >
                                <div x-show="errors.companyName" class="text-sm text-red-600 mt-1" x-text="errorMessages.companyName"></div>
                            </div>
                        </div>

                        <!-- Secteur d'activité -->
                        <div>
                            <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Secteur d'activité</label>
                            <div class="relative">
                                <select 
                                    id="industry" 
                                    name="industry" 
                                    x-model="industry"
                                    @blur="validateIndustry()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.industry, 'border-gray-300': !errors.industry}"
                                >
                                    <option value="" disabled selected>Sélectionnez un secteur</option>
                                    <option value="Agriculture">Agriculture</option>
                                    <option value="Agroalimentaire">Agroalimentaire</option>
                                    <option value="Banque / Assurance">Banque / Assurance</option>
                                    <option value="Commerce">Commerce</option>
                                    <option value="Communication">Communication</option>
                                    <option value="Construction / BTP">Construction / BTP</option>
                                    <option value="Éducation">Éducation</option>
                                    <option value="Énergie">Énergie</option>
                                    <option value="Industrie">Industrie</option>
                                    <option value="Informatique / Télécoms">Informatique / Télécoms</option>
                                    <option value="Santé">Santé</option>
                                    <option value="Services">Services</option>
                                    <option value="Transport / Logistique">Transport / Logistique</option>
                                    <option value="Autre">Autre</option>
                                </select>
                                <div x-show="errors.industry" class="text-sm text-red-600 mt-1" x-text="errorMessages.industry"></div>
                            </div>
                        </div>
                        
                        <!-- Adresse -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse de l'entreprise</label>
                            <div class="relative">
                                <textarea 
                                    id="address" 
                                    name="address" 
                                    x-model="address"
                                    @blur="validateAddress()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.address, 'border-gray-300': !errors.address}"
                                    rows="3"
                                    placeholder="Adresse complète de l'entreprise"
                                >{{ old('address') }}</textarea>
                                <div x-show="errors.address" class="text-sm text-red-600 mt-1" x-text="errorMessages.address"></div>
                            </div>
                        </div>
                        
                        <!-- Taille de l'entreprise -->
                        <div>
                            <label for="company_size" class="block text-sm font-medium text-gray-700 mb-1">Nombre de personnel</label>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    id="company_size" 
                                    name="company_size" 
                                    min="1"
                                    x-model="companySize"
                                    @blur="validateCompanySize()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.companySize, 'border-gray-300': !errors.companySize}"
                                    placeholder="Nombre d'employés"
                                    value="{{ old('company_size', 1) }}"
                                >
                                <div x-show="errors.companySize" class="text-sm text-red-600 mt-1" x-text="errorMessages.companySize"></div>
                            </div>
                        </div>
                        
                        <!-- Téléphone de contact -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone de contact</label>
                            <div class="relative">
                                <input 
                                    type="tel" 
                                    id="contact_phone" 
                                    name="contact_phone" 
                                    x-model="contactPhone"
                                    @blur="validateContactPhone()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.contactPhone, 'border-gray-300': !errors.contactPhone}"
                                    placeholder="Ex: +225 XX XX XX XX"
                                    value="{{ old('contact_phone') }}"
                                >
                                <div x-show="errors.contactPhone" class="text-sm text-red-600 mt-1" x-text="errorMessages.contactPhone"></div>
                            </div>
                        </div>
                        
                        <!-- Email professionnel -->
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email professionnel</label>
                            <div class="relative">
                                <input 
                                    type="email" 
                                    id="contact_email" 
                                    name="contact_email" 
                                    x-model="contactEmail"
                                    @blur="validateContactEmail()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.contactEmail, 'border-gray-300': !errors.contactEmail}"
                                    placeholder="Email professionnel de l'entreprise"
                                    value="{{ old('contact_email') }}"
                                >
                                <div x-show="errors.contactEmail" class="text-sm text-red-600 mt-1" x-text="errorMessages.contactEmail"></div>
                            </div>
                        </div>
                        
                        <!-- Boutons de navigation -->
                        <div class="flex justify-between">
                            <a 
                                href="{{ route('workflow.user') }}" 
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
                                <span x-show="!isLoading">Enregistrer et continuer</span>
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
