@extends('layouts.wizard')

@section('content')
<div x-data="bulletinWizard()" x-cloak>
    <div class="mb-8 bg-white p-6 rounded-lg shadow-md border-l-4 border-primary-500">
        <h1 class="text-2xl font-bold text-gray-800">Génération d'un bulletin de paie</h1>
        <p class="text-gray-600 mt-2">Suivez les étapes pour générer un bulletin de paie pour un employé.</p>
    </div>

    <!-- Étapes du wizard -->
    <div class="mb-8 bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between">
            <!-- Étape 1 -->
            <div class="flex-1 text-center relative">
                <div 
                    class="w-14 h-14 mx-auto rounded-full flex items-center justify-center mb-3 transition-all duration-500 shadow-md z-10 border-2"
                    :class="{
                        'bg-primary-500 border-primary-600 text-white': currentStep >= 1,
                        'bg-white border-gray-300 text-gray-500': currentStep < 1,
                        'transform scale-110': currentStep === 1
                    }"
                >
                    <template x-if="currentStep > 1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 animate-check" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span class="text-lg font-bold">1</span>
                    </template>
                </div>
                <div class="text-sm font-medium transition-all duration-300" :class="{'text-primary-700 font-semibold': currentStep === 1, 'text-gray-900': currentStep > 1, 'text-gray-500': currentStep < 1}">
                    <span class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Informations générales
                    </span>
                </div>
            </div>
            
            <!-- Ligne de connexion 1-2 -->
            <div class="w-full mx-2 flex items-center justify-center">
                <div class="h-1 w-full rounded-full transition-all duration-500" :class="{'bg-primary-500': currentStep > 1, 'bg-gray-200': currentStep <= 1}"></div>
            </div>
            
            <!-- Étape 2 -->
            <div class="flex-1 text-center relative">
                <div 
                    class="w-14 h-14 mx-auto rounded-full flex items-center justify-center mb-3 transition-all duration-500 shadow-md z-10 border-2"
                    :class="{
                        'bg-primary-500 border-primary-600 text-white': currentStep >= 2,
                        'bg-white border-gray-300 text-gray-500': currentStep < 2,
                        'transform scale-110': currentStep === 2
                    }"
                >
                    <template x-if="currentStep > 2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 animate-check" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span class="text-lg font-bold">2</span>
                    </template>
                </div>
                <div class="text-sm font-medium transition-all duration-300" :class="{'text-primary-700 font-semibold': currentStep === 2, 'text-gray-900': currentStep > 2, 'text-gray-500': currentStep < 2}">
                    <span class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Éléments de rémunération
                    </span>
                </div>
            </div>
            
            <!-- Ligne de connexion 2-3 -->
            <div class="w-full mx-2 flex items-center justify-center">
                <div class="h-1 w-full rounded-full transition-all duration-500" :class="{'bg-primary-500': currentStep > 2, 'bg-gray-200': currentStep <= 2}"></div>
            </div>
            
            <!-- Étape 3 -->
            <div class="flex-1 text-center relative">
                <div 
                    class="w-14 h-14 mx-auto rounded-full flex items-center justify-center mb-3 transition-all duration-500 shadow-md z-10 border-2"
                    :class="{
                        'bg-primary-500 border-primary-600 text-white': currentStep >= 3,
                        'bg-white border-gray-300 text-gray-500': currentStep < 3,
                        'transform scale-110': currentStep === 3
                    }"
                >
                    <template x-if="currentStep > 3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 animate-check" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="currentStep <= 3">
                        <span class="text-lg font-bold">3</span>
                    </template>
                </div>
                <div class="text-sm font-medium transition-all duration-300" :class="{'text-primary-700 font-semibold': currentStep === 3, 'text-gray-900': currentStep > 3, 'text-gray-500': currentStep < 3}">
                    <span class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Récapitulatif
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes check {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-check {
            animation: check 0.5s ease-in-out;
        }
    </style>

    <!-- Contenu de l'étape 1 -->
    <div x-show="currentStep === 1" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="bg-primary-50 p-6 rounded-lg mb-6 shadow-sm border-l-4 border-primary-500">
            <h2 class="text-xl font-semibold text-primary-700 mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Informations générales
            </h2>
            <p class="text-primary-600">Sélectionnez l'employé et définissez les paramètres de base du bulletin de paie.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-lg shadow-sm">
            <div class="transition-all duration-200 hover:shadow-md p-4 rounded-lg border border-gray-100">
                <label for="employeur_id" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Employé
                </label>
                <select id="employeur_id" x-model="formData.employeur_id" @change="loadEmployeurInfo()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                    <option value="">Sélectionnez un employé</option>
                    @foreach($employeurs as $employeur)
                        <option value="{{ $employeur->id }}">{{ $employeur->nom_complet }}</option>
                    @endforeach
                </select>
                <div x-show="errors.employeur_id" class="text-danger-500 text-sm mt-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="errors.employeur_id"></span>
                </div>
            </div>

            <div class="transition-all duration-200 hover:shadow-md p-4 rounded-lg border border-gray-100">
                <label for="configuration_paie_id" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Configuration de paie
                </label>
                <select id="configuration_paie_id" x-model="formData.configuration_paie_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                    <option value="">Sélectionnez une configuration</option>
                    @foreach($configurations as $configuration)
                        <option value="{{ $configuration->id }}" {{ $configuration->est_defaut ? 'selected' : '' }}>{{ $configuration->nom }}</option>
                    @endforeach
                </select>
                <div x-show="errors.configuration_paie_id" class="text-danger-500 text-sm mt-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="errors.configuration_paie_id"></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 bg-white p-6 rounded-lg shadow-sm">
            <div class="transition-all duration-200 hover:shadow-md p-4 rounded-lg border border-gray-100">
                <label for="periode_debut" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Début de période
                </label>
                <input type="date" id="periode_debut" x-model="formData.periode_debut" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                <div x-show="errors.periode_debut" class="text-danger-500 text-sm mt-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="errors.periode_debut"></span>
                </div>
            </div>

            <div class="transition-all duration-200 hover:shadow-md p-4 rounded-lg border border-gray-100">
                <label for="periode_fin" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Fin de période
                </label>
                <input type="date" id="periode_fin" x-model="formData.periode_fin" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                <div x-show="errors.periode_fin" class="text-danger-500 text-sm mt-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="errors.periode_fin"></span>
                </div>
            </div>

            <div class="transition-all duration-200 hover:shadow-md p-4 rounded-lg border border-gray-100">
                <label for="date_paiement" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                    Date de paiement
                </label>
                <input type="date" id="date_paiement" x-model="formData.date_paiement" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                <div x-show="errors.date_paiement" class="text-danger-500 text-sm mt-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="errors.date_paiement"></span>
                </div>
            </div>
        </div>

        <div x-show="employeurInfo" class="mt-6 bg-white rounded-lg shadow-sm border-l-4 border-primary-500 overflow-hidden transition-all duration-300" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="bg-primary-50 p-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h3 class="text-lg font-medium text-primary-700">Informations de l'employé</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Matricule</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.matricule || 'Non défini'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Poste</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.poste || 'Non défini'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Département</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.departement || 'Non défini'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Date d'embauche</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.date_embauche || 'Non définie'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Catégorie</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.categorie || 'Non définie'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Échelon</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.echelon || 'Non défini'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Situation familiale</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.situation_familiale || 'Non définie'"></p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Nombre d'enfants</p>
                        </div>
                        <p class="font-medium text-gray-900 ml-5" x-text="employeurInfo.nombre_enfants || '0'"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenu de l'étape 2 -->
    <div x-show="currentStep === 2" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="bg-primary-50 p-6 rounded-lg mb-6 shadow-sm border-l-4 border-primary-500">
            <h2 class="text-xl font-semibold text-primary-700 mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Éléments de rémunération
            </h2>
            <p class="text-primary-600">Définissez le salaire de base et les éléments de rémunération supplémentaires.</p>
        </div>
        
        <div class="mb-6">
            <label for="salaire_base" class="block text-sm font-medium text-gray-700 mb-1">Salaire de base</label>
            <div class="flex">
                <input type="number" id="salaire_base" x-model="formData.salaire_base" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <span class="ml-2 inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">FCFA</span>
            </div>
            <div x-show="errors.salaire_base" class="text-danger-500 text-sm mt-1" x-text="errors.salaire_base"></div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Indemnités</h3>
            <div class="space-y-4">
                <template x-for="(indemnite, index) in formData.indemnites" :key="index">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-gray-700" x-text="indemnite.libelle || 'Nouvelle indemnité'"></h4>
                            <button type="button" @click="removeIndemnite(index)" class="text-danger-500 hover:text-danger-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label :for="'indemnite_libelle_' + index" class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                                <input type="text" :id="'indemnite_libelle_' + index" x-model="indemnite.libelle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'indemnite_type_' + index" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select :id="'indemnite_type_' + index" x-model="indemnite.type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <option value="montant_fixe">Montant fixe</option>
                                    <option value="pourcentage">Pourcentage du salaire de base</option>
                                </select>
                            </div>
                            <div x-show="indemnite.type === 'montant_fixe'">
                                <label :for="'indemnite_montant_' + index" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA)</label>
                                <input type="number" :id="'indemnite_montant_' + index" x-model="indemnite.montant" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div x-show="indemnite.type === 'pourcentage'">
                                <label :for="'indemnite_taux_' + index" class="block text-sm font-medium text-gray-700 mb-1">Taux (%)</label>
                                <input type="number" :id="'indemnite_taux_' + index" x-model="indemnite.taux" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'indemnite_imposable_' + index" class="flex items-center">
                                    <input type="checkbox" :id="'indemnite_imposable_' + index" x-model="indemnite.imposable" @change="calculateElements()" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Imposable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addIndemnite()" class="flex items-center text-primary-600 hover:text-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter une indemnité
                </button>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium text-primary-700">Primes</h3>
            </div>
            <div class="space-y-4">
                <template x-for="(prime, index) in formData.primes" :key="index">
                    <div class="bg-white p-4 rounded-lg border-l-4 border-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-gray-800 flex items-center" >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="prime.libelle || 'Nouvelle prime'"></span>
                            </h4>
                            <button type="button" @click="removePrime(index)" class="text-danger-500 hover:text-danger-700 p-1 rounded-full hover:bg-danger-50 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-sm">
                                <label :for="'prime_libelle_' + index" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Libellé
                                </label>
                                <input type="text" :id="'prime_libelle_' + index" x-model="prime.libelle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-sm">
                                <label :for="'prime_type_' + index" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                    </svg>
                                    Type
                                </label>
                                <select :id="'prime_type_' + index" x-model="prime.type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                                    <option value="montant_fixe">Montant fixe</option>
                                    <option value="pourcentage">Pourcentage du salaire de base</option>
                                </select>
                            </div>
                            <div x-show="prime.type === 'montant_fixe'" class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-sm">
                                <label :for="'prime_montant_' + index" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Montant (FCFA)
                                </label>
                                <div class="relative">
                                    <input type="number" :id="'prime_montant_' + index" x-model="prime.montant" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">FCFA</span>
                                    </div>
                                </div>
                            </div>
                            <div x-show="prime.type === 'pourcentage'" class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-sm">
                                <label :for="'prime_taux_' + index" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Taux (%)
                                </label>
                                <div class="relative">
                                    <input type="number" :id="'prime_taux_' + index" x-model="prime.taux" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 transition-all duration-200">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 transition-all duration-200 hover:shadow-sm">
                                <label :for="'prime_imposable_' + index" class="flex items-center">
                                    <input type="checkbox" :id="'prime_imposable_' + index" x-model="prime.imposable" @change="calculateElements()" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Imposable
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addPrime()" class="flex items-center justify-center w-full py-2 px-4 bg-primary-50 hover:bg-primary-100 text-primary-700 font-medium rounded-lg border border-primary-200 transition-all duration-200 hover:shadow-sm group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Ajouter une prime</span>
                </button>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Retenues</h3>
            <div class="space-y-4">
                <template x-for="(retenue, index) in formData.retenues" :key="index">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-gray-700" x-text="retenue.libelle || 'Nouvelle retenue'"></h4>
                            <button type="button" @click="removeRetenue(index)" class="text-danger-500 hover:text-danger-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label :for="'retenue_libelle_' + index" class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                                <input type="text" :id="'retenue_libelle_' + index" x-model="retenue.libelle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'retenue_type_' + index" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select :id="'retenue_type_' + index" x-model="retenue.type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <option value="montant_fixe">Montant fixe</option>
                                    <option value="pourcentage">Pourcentage du salaire de base</option>
                                </select>
                            </div>
                            <div x-show="retenue.type === 'montant_fixe'">
                                <label :for="'retenue_montant_' + index" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA)</label>
                                <input type="number" :id="'retenue_montant_' + index" x-model="retenue.montant" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div x-show="retenue.type === 'pourcentage'">
                                <label :for="'retenue_taux_' + index" class="block text-sm font-medium text-gray-700 mb-1">Taux (%)</label>
                                <input type="number" :id="'retenue_taux_' + index" x-model="retenue.taux" @change="calculateElements()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addRetenue()" class="flex items-center text-primary-600 hover:text-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter une retenue
                </button>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center">
                <input type="checkbox" id="calcul_auto" x-model="formData.calcul_auto" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="calcul_auto" class="ml-2 block text-sm text-gray-700">Calcul automatique des éléments</label>
            </div>
            <p class="text-sm text-gray-500 mt-1">Si activé, les cotisations et autres éléments seront calculés automatiquement selon la configuration de paie.</p>
        </div>
    </div>
    
    <!-- Contenu de l'étape 3 -->
    <div x-show="currentStep === 3" class="space-y-6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="bg-primary-50 p-6 rounded-lg mb-6 shadow-sm border-l-4 border-primary-500">
            <h2 class="text-xl font-semibold text-primary-700 mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Aperçu et validation
            </h2>
            <p class="text-primary-600">Vérifiez les informations et générez le bulletin de paie.</p>
        </div>
        
        <div x-show="isCalculating" class="flex justify-center items-center py-12">
            <svg class="animate-spin h-10 w-10 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-3 text-lg text-gray-700">Calcul en cours...</span>
        </div>
        
        <div x-show="!isCalculating && calculationResults" class="space-y-6">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Récapitulatif du bulletin</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Informations générales</h4>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Employé</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="employeurInfo?.nom_complet || '-'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Période</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatDate(formData.periode_debut) + ' au ' + formatDate(formData.periode_fin)"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Date de paiement</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatDate(formData.date_paiement)"></dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Salaire</h4>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Salaire de base</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(formData.salaire_base) + ' FCFA'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Total indemnités</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(calculationResults.total_indemnites) + ' FCFA'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Total primes</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(calculationResults.total_primes) + ' FCFA'"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="font-medium text-gray-700 mb-2">Résultats</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Salaire brut</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(calculationResults.salaire_brut) + ' FCFA'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Total retenues salariales</dt>
                                    <dd class="text-sm font-medium text-gray-900 text-danger-600" x-text="formatMontant(calculationResults.total_retenues) + ' FCFA'"></dd>
                                </div>
                                <div class="flex justify-between font-bold">
                                    <dt class="text-gray-700">Salaire net</dt>
                                    <dd class="text-success-600" x-text="formatMontant(calculationResults.salaire_net) + ' FCFA'"></dd>
                                </div>
                            </dl>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Charges patronales</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(calculationResults.total_charges_patronales) + ' FCFA'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Coût total employeur</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="formatMontant(calculationResults.cout_total) + ' FCFA'"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center">
                    <input type="checkbox" id="valider_directement" x-model="formData.valider_directement" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <label for="valider_directement" class="ml-2 block text-sm text-gray-700">Valider directement le bulletin</label>
                </div>
                <p class="text-sm text-gray-500 mt-1">Si coché, le bulletin sera directement validé après sa génération. Sinon, il sera enregistré en brouillon.</p>
            </div>
        </div>
    </div>
    
    <!-- Boutons de navigation -->
    <div class="mt-8 pt-5 border-t border-gray-200 flex justify-between bg-white p-6 rounded-lg shadow-md">
        <button 
            type="button" 
            @click="previousStep()" 
            x-show="currentStep > 1"
            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 py-3 px-6 rounded-lg shadow-sm text-sm font-medium flex items-center transition-all duration-300 hover:shadow-md hover:translate-x-[-2px] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500 group-hover:animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span>Précédent</span>
        </button>
        <div class="flex space-x-3">
            <button 
                type="button" 
                @click="nextStep()" 
                x-show="currentStep < 3"
                class="bg-primary-600 hover:bg-primary-700 text-white py-3 px-6 rounded-lg shadow-sm text-sm font-medium flex items-center transition-all duration-300 hover:shadow-md hover:translate-x-[2px] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 group">
                <span>Suivant</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <button 
                type="button" 
                @click="generateBulletin()" 
                x-show="currentStep === 3"
                :disabled="isSubmitting"
                :class="{'opacity-75 cursor-not-allowed': isSubmitting}"
                class="bg-success-600 hover:bg-success-700 text-white py-3 px-8 rounded-lg shadow-md text-sm font-medium flex items-center justify-center transition-all duration-300 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 transform hover:scale-105 group">
                <span x-show="isSubmitting" class="mr-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span x-text="isSubmitting ? 'Génération en cours...' : 'Générer le bulletin'" class="font-semibold"></span>
                <svg x-show="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bulletinWizard() {
        return {
            currentStep: 1,
            isSubmitting: false,
            isCalculating: false,
            employeurInfo: null,
            calculationResults: null,
            formData: {
                employeur_id: '',
                configuration_paie_id: '{{ $configurationDefaut ? $configurationDefaut->id : '' }}',
                periode_debut: '{{ \Carbon\Carbon::now()->startOfMonth()->format("Y-m-d") }}',
                periode_fin: '{{ \Carbon\Carbon::now()->endOfMonth()->format("Y-m-d") }}',
                date_paiement: '{{ \Carbon\Carbon::now()->format("Y-m-d") }}',
                salaire_base: 0,
                indemnites: [],
                primes: [],
                retenues: [],
                calcul_auto: true,
                valider_directement: false
            },
            errors: {},
            
            nextStep() {
                if (this.currentStep === 1) {
                    if (!this.validateStep1()) {
                        return;
                    }
                }
                
                if (this.currentStep === 2) {
                    if (!this.validateStep2()) {
                        return;
                    }
                    this.calculateElements(true);
                }
                
                this.currentStep++;
            },
            
            previousStep() {
                this.currentStep--;
            },
            
            validateStep1() {
                this.errors = {};
                
                if (!this.formData.employeur_id) {
                    this.errors.employeur_id = 'Veuillez sélectionner un employé';
                }
                
                if (!this.formData.configuration_paie_id) {
                    this.errors.configuration_paie_id = 'Veuillez sélectionner une configuration de paie';
                }
                
                if (!this.formData.periode_debut) {
                    this.errors.periode_debut = 'Veuillez sélectionner une date de début de période';
                }
                
                if (!this.formData.periode_fin) {
                    this.errors.periode_fin = 'Veuillez sélectionner une date de fin de période';
                }
                
                if (this.formData.periode_debut && this.formData.periode_fin && new Date(this.formData.periode_debut) > new Date(this.formData.periode_fin)) {
                    this.errors.periode_fin = 'La date de fin doit être postérieure à la date de début';
                }
                
                if (!this.formData.date_paiement) {
                    this.errors.date_paiement = 'Veuillez sélectionner une date de paiement';
                }
                
                return Object.keys(this.errors).length === 0;
            },
            
            validateStep2() {
                this.errors = {};
                
                if (!this.formData.salaire_base || this.formData.salaire_base <= 0) {
                    this.errors.salaire_base = 'Veuillez saisir un salaire de base valide';
                }
                
                // Valider les indemnités
                this.formData.indemnites.forEach((indemnite, index) => {
                    if (!indemnite.libelle) {
                        this.errors[`indemnites.${index}.libelle`] = 'Veuillez saisir un libellé';
                    }
                    
                    if (indemnite.type === 'montant_fixe' && (!indemnite.montant || indemnite.montant <= 0)) {
                        this.errors[`indemnites.${index}.montant`] = 'Veuillez saisir un montant valide';
                    }
                    
                    if (indemnite.type === 'pourcentage' && (!indemnite.taux || indemnite.taux <= 0)) {
                        this.errors[`indemnites.${index}.taux`] = 'Veuillez saisir un taux valide';
                    }
                });
                
                // Valider les primes
                this.formData.primes.forEach((prime, index) => {
                    if (!prime.libelle) {
                        this.errors[`primes.${index}.libelle`] = 'Veuillez saisir un libellé';
                    }
                    
                    if (prime.type === 'montant_fixe' && (!prime.montant || prime.montant <= 0)) {
                        this.errors[`primes.${index}.montant`] = 'Veuillez saisir un montant valide';
                    }
                    
                    if (prime.type === 'pourcentage' && (!prime.taux || prime.taux <= 0)) {
                        this.errors[`primes.${index}.taux`] = 'Veuillez saisir un taux valide';
                    }
                });
                
                // Valider les retenues
                this.formData.retenues.forEach((retenue, index) => {
                    if (!retenue.libelle) {
                        this.errors[`retenues.${index}.libelle`] = 'Veuillez saisir un libellé';
                    }
                    
                    if (retenue.type === 'montant_fixe' && (!retenue.montant || retenue.montant <= 0)) {
                        this.errors[`retenues.${index}.montant`] = 'Veuillez saisir un montant valide';
                    }
                    
                    if (retenue.type === 'pourcentage' && (!retenue.taux || retenue.taux <= 0)) {
                        this.errors[`retenues.${index}.taux`] = 'Veuillez saisir un taux valide';
                    }
                });
                
                return Object.keys(this.errors).length === 0;
            },
            
            loadEmployeurInfo() {
                if (!this.formData.employeur_id) {
                    this.employeurInfo = null;
                    this.formData.salaire_base = 0;
                    return;
                }
                
                fetch(`{{ route('paie.bulletins.ajax.employeur-info') }}?employeur_id=${this.formData.employeur_id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.employeurInfo = data.employeur;
                            this.formData.salaire_base = this.employeurInfo.salaire_base || 0;
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des informations de l\'employeur:', error);
                        showNotification('error', 'Erreur lors de la récupération des informations de l\'employeur');
                    });
            },
            
            calculateElements(showLoading = false) {
                if (!this.formData.employeur_id || !this.formData.configuration_paie_id || !this.formData.salaire_base) {
                    return;
                }
                
                if (showLoading) {
                    this.isCalculating = true;
                }
                
                const data = {
                    employeur_id: this.formData.employeur_id,
                    configuration_paie_id: this.formData.configuration_paie_id,
                    salaire_base: this.formData.salaire_base,
                    indemnites: this.formData.indemnites,
                    primes: this.formData.primes,
                    retenues: this.formData.retenues
                };
                
                fetch('{{ route('paie.bulletins.ajax.calculate-elements') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    this.isCalculating = false;
                    if (data.success) {
                        this.calculationResults = data.resultats;
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    this.isCalculating = false;
                    console.error('Erreur lors du calcul des éléments:', error);
                    showNotification('error', 'Erreur lors du calcul des éléments');
                });
            },
            
            generateBulletin() {
                if (this.isSubmitting) {
                    return;
                }
                
                this.isSubmitting = true;
                
                fetch('{{ route('paie.bulletins.wizard.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(response => response.json())
                .then(data => {
                    this.isSubmitting = false;
                    if (data.success) {
                        showNotification('success', data.message);
                        window.location.href = data.redirect_url;
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Erreur lors de la génération du bulletin:', error);
                    showNotification('error', 'Erreur lors de la génération du bulletin');
                });
            },
            
            addIndemnite() {
                this.formData.indemnites.push({
                    libelle: '',
                    type: 'montant_fixe',
                    montant: 0,
                    taux: 0,
                    imposable: true
                });
            },
            
            removeIndemnite(index) {
                this.formData.indemnites.splice(index, 1);
                this.calculateElements();
            },
            
            addPrime() {
                this.formData.primes.push({
                    libelle: '',
                    type: 'montant_fixe',
                    montant: 0,
                    taux: 0,
                    imposable: true
                });
            },
            
            removePrime(index) {
                this.formData.primes.splice(index, 1);
                this.calculateElements();
            },
            
            addRetenue() {
                this.formData.retenues.push({
                    libelle: '',
                    type: 'montant_fixe',
                    montant: 0,
                    taux: 0
                });
            },
            
            removeRetenue(index) {
                this.formData.retenues.splice(index, 1);
                this.calculateElements();
            },
            
            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            },
            
            formatMontant(montant) {
                return new Intl.NumberFormat('fr-FR').format(montant || 0);
            }
        };
    }
</script>
@endpush
@endsection
