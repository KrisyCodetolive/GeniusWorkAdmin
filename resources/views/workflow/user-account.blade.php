@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userForm', () => ({
            fullName: '',
            email: '',
            telephone: '',
            password: '',
            passwordConfirmation: '',
            showPassword: false,
            showPasswordConfirmation: false,
            isLoading: false,
            errors: {
                fullName: false,
                email: false,
                telephone: false,
                password: false,
                passwordConfirmation: false
            },
            errorMessages: {
                fullName: '',
                email: '',
                telephone: '',
                password: '',
                passwordConfirmation: ''
            },
            
            validateFullName() {
                if (!this.fullName.trim()) {
                    this.errors.fullName = true;
                    this.errorMessages.fullName = 'Veuillez entrer votre nom complet';
                    return false;
                }
                this.errors.fullName = false;
                this.errorMessages.fullName = '';
                return true;
            },
            
            validateEmail() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!this.email.trim()) {
                    this.errors.email = true;
                    this.errorMessages.email = 'Veuillez entrer votre adresse e-mail';
                    return false;
                } else if (!emailRegex.test(this.email)) {
                    this.errors.email = true;
                    this.errorMessages.email = 'Veuillez entrer une adresse e-mail valide';
                    return false;
                }
                this.errors.email = false;
                this.errorMessages.email = '';
                return true;
            },
            
            validateTelephone() {
                if (!this.telephone.trim()) {
                    this.errors.telephone = true;
                    this.errorMessages.telephone = 'Veuillez entrer votre numéro de téléphone';
                    return false;
                } else if (this.telephone.trim().length < 8) {
                    this.errors.telephone = true;
                    this.errorMessages.telephone = 'Veuillez entrer un numéro de téléphone valide';
                    return false;
                }
                this.errors.telephone = false;
                this.errorMessages.telephone = '';
                return true;
            },
            
            validatePassword() {
                if (!this.password) {
                    this.errors.password = true;
                    this.errorMessages.password = 'Veuillez entrer un mot de passe';
                    return false;
                } else if (this.password.length < 8) {
                    this.errors.password = true;
                    this.errorMessages.password = 'Le mot de passe doit contenir au moins 8 caractères';
                    return false;
                }
                this.errors.password = false;
                this.errorMessages.password = '';
                return true;
            },
            
            validatePasswordConfirmation() {
                if (!this.passwordConfirmation) {
                    this.errors.passwordConfirmation = true;
                    this.errorMessages.passwordConfirmation = 'Veuillez confirmer votre mot de passe';
                    return false;
                } else if (this.password !== this.passwordConfirmation) {
                    this.errors.passwordConfirmation = true;
                    this.errorMessages.passwordConfirmation = 'Les mots de passe ne correspondent pas';
                    return false;
                }
                this.errors.passwordConfirmation = false;
                this.errorMessages.passwordConfirmation = '';
                return true;
            },
            
            validateForm() {
                const isFullNameValid = this.validateFullName();
                const isEmailValid = this.validateEmail();
                const isPhoneValid = this.validateTelephone();
                const isPasswordValid = this.validatePassword();
                const isPasswordConfirmationValid = this.validatePasswordConfirmation();
                
                return isFullNameValid && isEmailValid && isPhoneValid && isPasswordValid && isPasswordConfirmationValid;
            },
            
            submitForm() {
                if (this.validateForm()) {
                    this.isLoading = true;
                    document.getElementById('user-form').submit();
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
                Créer votre compte utilisateur
            </h1>
            <p class="text-lg text-gray-600">
                Première étape pour accéder à Genius Work
            </p>
        </div>

        <!-- Steps indicator -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div class="flex-1 flex items-center">
                    <div class="bg-indigo-500 rounded-full w-10 h-10 flex items-center justify-center text-white font-bold">
                        1
                    </div>
                    <div class="ml-2 font-medium text-indigo-600">Compte utilisateur</div>
                    <div class="flex-1 h-1 mx-4 bg-indigo-200"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center text-gray-500 font-bold">
                        2
                    </div>
                    <div class="ml-2 font-medium text-gray-500">Compte entreprise</div>
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
            <div class="p-8" x-data="userForm">
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
                
                <form id="user-form" action="{{ route('workflow.user.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <!-- Nom Complet -->
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="full_name" 
                                    name="full_name" 
                                    x-model="fullName"
                                    @blur="validateFullName()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.fullName, 'border-gray-300': !errors.fullName}"
                                    placeholder="Entrez votre nom complet"
                                    value="{{ old('full_name') }}"
                                >
                                <div x-show="errors.fullName" class="text-sm text-red-600 mt-1" x-text="errorMessages.fullName"></div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
                            <div class="relative">
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    x-model="email"
                                    @blur="validateEmail()"
                                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.email, 'border-gray-300': !errors.email}"
                                    placeholder="Entrez votre adresse e-mail"
                                    value="{{ old('email') }}"
                                >
                                <div x-show="errors.email" class="text-sm text-red-600 mt-1" x-text="errorMessages.email"></div>
                            </div>
                        </div>
                        
                        <!-- Téléphone -->
                        <div>
                            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone</label>
                            <div class="relative">
                                <div class="flex shadow-sm rounded-lg overflow-hidden">
                                    <div class="flex-shrink-0">
                                        <select 
                                            id="country_code" 
                                            name="country_code" 
                                            class="h-full py-2 pl-3 pr-2 border-0 bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                            <option value="+225" selected>+225</option>
                                            <option value="+233">+233</option>
                                            <option value="+234">+234</option>
                                            <option value="+221">+221</option>
                                            <option value="+237">+237</option>
                                            <option value="+228">+228</option>
                                            <option value="+229">+229</option>
                                            <option value="+226">+226</option>
                                            <option value="+223">+223</option>
                                            <option value="+227">+227</option>
                                            <option value="+241">+241</option>
                                            <option value="+242">+242</option>
                                            <option value="+243">+243</option>
                                            <option value="+235">+235</option>
                                            <option value="+236">+236</option>
                                            <option value="+240">+240</option>
                                            <option value="+244">+244</option>
                                            <option value="+33">+33</option>
                                            <option value="+1">+1</option>
                                            <option value="+44">+44</option>
                                        </select>
                                    </div>
                                    <input 
                                        type="tel" 
                                        id="telephone" 
                                        name="telephone" 
                                        x-model="telephone"
                                        @blur="validateTelephone()"
                                        class="flex-1 border-0 px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        :class="{'bg-red-50': errors.telephone, 'bg-white': !errors.telephone}"
                                        placeholder="Exemple: 07047504XX"
                                        value="{{ old('telephone') }}"
                                    >
                                </div>
                                <div x-show="errors.telephone" class="text-sm text-red-600 mt-1" x-text="errorMessages.telephone"></div>
                                <p class="text-xs text-gray-500 mt-1">Ce numéro sera utilisé pour les communications importantes concernant votre compte.</p>
                            </div>
                        </div>
                        
                        <!-- Mot de passe -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                            <div class="relative">
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    id="password" 
                                    name="password" 
                                    x-model="password"
                                    @blur="validatePassword()"
                                    class="w-full px-4 py-2 pr-10 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.password, 'border-gray-300': !errors.password}"
                                    placeholder="Minimum 8 caractères"
                                >
                                <button 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    @click="showPassword = !showPassword"
                                >
                                    <svg x-show="!showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                    </svg>
                                </button>
                                <div x-show="errors.password" class="text-sm text-red-600 mt-1" x-text="errorMessages.password"></div>
                            </div>
                        </div>
                        
                        <!-- Confirmation mot de passe -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmation mot de passe</label>
                            <div class="relative">
                                <input 
                                    :type="showPasswordConfirmation ? 'text' : 'password'" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    x-model="passwordConfirmation"
                                    @blur="validatePasswordConfirmation()"
                                    class="w-full px-4 py-2 pr-10 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    :class="{'border-red-300': errors.passwordConfirmation, 'border-gray-300': !errors.passwordConfirmation}"
                                    placeholder="Confirmez votre mot de passe"
                                >
                                <button 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                >
                                    <svg x-show="!showPasswordConfirmation" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="showPasswordConfirmation" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                    </svg>
                                </button>
                                <div x-show="errors.passwordConfirmation" class="text-sm text-red-600 mt-1" x-text="errorMessages.passwordConfirmation"></div>
                            </div>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="flex justify-end">
                            <button 
                                type="button" 
                                @click="submitForm()"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                :disabled="isLoading"
                            >
                                <span x-show="!isLoading">Suivant</span>
                                <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="isLoading">Chargement...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
