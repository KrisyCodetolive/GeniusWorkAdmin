@extends('layouts.wizard')

@section('content')
<div x-data="bulletinMasseWizard()" x-cloak>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Génération en masse de bulletins de paie</h1>
        <p class="text-gray-600">Suivez les étapes pour générer des bulletins de paie pour plusieurs employés.</p>
    </div>

    <!-- Étapes du wizard -->
    <div class="flex justify-between mb-8">
        <div class="flex-1 text-center" :class="{'step-active': currentStep === 1, 'step-completed': currentStep > 1, 'step-inactive': currentStep < 1}">
            <div class="w-10 h-10 mx-auto rounded-full border-2 flex items-center justify-center mb-2">
                <template x-if="currentStep > 1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="currentStep <= 1">
                    <span>1</span>
                </template>
            </div>
            <div class="text-sm font-medium">Sélection des employés</div>
        </div>
        <div class="w-full mx-4 mt-5">
            <div class="h-1 bg-gray-200 rounded-full" :class="{'bg-primary-500': currentStep > 1}"></div>
        </div>
        <div class="flex-1 text-center" :class="{'step-active': currentStep === 2, 'step-completed': currentStep > 2, 'step-inactive': currentStep < 2}">
            <div class="w-10 h-10 mx-auto rounded-full border-2 flex items-center justify-center mb-2">
                <template x-if="currentStep > 2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="currentStep <= 2">
                    <span>2</span>
                </template>
            </div>
            <div class="text-sm font-medium">Paramètres communs</div>
        </div>
        <div class="w-full mx-4 mt-5">
            <div class="h-1 bg-gray-200 rounded-full" :class="{'bg-primary-500': currentStep > 2}"></div>
        </div>
        <div class="flex-1 text-center" :class="{'step-active': currentStep === 3, 'step-completed': currentStep > 3, 'step-inactive': currentStep < 3}">
            <div class="w-10 h-10 mx-auto rounded-full border-2 flex items-center justify-center mb-2">
                <template x-if="currentStep > 3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="currentStep <= 3">
                    <span>3</span>
                </template>
            </div>
            <div class="text-sm font-medium">Aperçu et validation</div>
        </div>
    </div>

    <!-- Contenu de l'étape 1 -->
    <div x-show="currentStep === 1" class="space-y-6">
        <div class="bg-primary-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold text-primary-700 mb-2">Sélection des employés</h2>
            <p class="text-primary-600">Sélectionnez les employés pour lesquels vous souhaitez générer des bulletins de paie.</p>
        </div>

        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
                <input type="checkbox" id="select_all" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="select_all" class="ml-2 block text-sm text-gray-700">Sélectionner tous les employés</label>
            </div>
            <div class="text-sm text-gray-500">
                <span x-text="selectedEmployes.length"></span> employé(s) sélectionné(s)
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sélection
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nom complet
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Matricule
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Poste
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Département
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Salaire de base
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($employeurs as $employeur)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" value="{{ $employeur->id }}" x-model="formData.employeur_ids" @change="updateSelectAll" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $employeur->nom_complet }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $employeur->matricule ?? 'Non défini' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $employeur->poste ?? 'Non défini' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $employeur->departement ?? 'Non défini' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ number_format($employeur->salaire_base ?? 0, 0, ',', ' ') }} FCFA</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div x-show="errors.employeur_ids" class="text-danger-500 text-sm mt-1" x-text="errors.employeur_ids"></div>
    </div>
    
    <!-- Contenu de l'étape 2 -->
    <div x-show="currentStep === 2" class="space-y-6">
        <div class="bg-primary-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold text-primary-700 mb-2">Paramètres communs</h2>
            <p class="text-primary-600">Définissez les paramètres communs pour tous les bulletins de paie.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="configuration_paie_id" class="block text-sm font-medium text-gray-700 mb-1">Configuration de paie</label>
                <select id="configuration_paie_id" x-model="formData.configuration_paie_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <option value="">Sélectionnez une configuration</option>
                    @foreach($configurations as $configuration)
                        <option value="{{ $configuration->id }}" {{ $configuration->est_defaut ? 'selected' : '' }}>{{ $configuration->nom }}</option>
                    @endforeach
                </select>
                <div x-show="errors.configuration_paie_id" class="text-danger-500 text-sm mt-1" x-text="errors.configuration_paie_id"></div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            <div>
                <label for="periode_debut" class="block text-sm font-medium text-gray-700 mb-1">Début de période</label>
                <input type="date" id="periode_debut" x-model="formData.periode_debut" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <div x-show="errors.periode_debut" class="text-danger-500 text-sm mt-1" x-text="errors.periode_debut"></div>
            </div>

            <div>
                <label for="periode_fin" class="block text-sm font-medium text-gray-700 mb-1">Fin de période</label>
                <input type="date" id="periode_fin" x-model="formData.periode_fin" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <div x-show="errors.periode_fin" class="text-danger-500 text-sm mt-1" x-text="errors.periode_fin"></div>
            </div>

            <div>
                <label for="date_paiement" class="block text-sm font-medium text-gray-700 mb-1">Date de paiement</label>
                <input type="date" id="date_paiement" x-model="formData.date_paiement" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <div x-show="errors.date_paiement" class="text-danger-500 text-sm mt-1" x-text="errors.date_paiement"></div>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6 mt-4">
            <div class="flex items-center">
                <input type="checkbox" id="utiliser_salaire_base_employe" x-model="formData.utiliser_salaire_base_employe" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="utiliser_salaire_base_employe" class="ml-2 block text-sm text-gray-700">Utiliser le salaire de base de chaque employé</label>
            </div>
            <p class="text-sm text-gray-500 mt-1">Si désactivé, vous pourrez définir un salaire de base commun pour tous les employés.</p>
            
            <div x-show="!formData.utiliser_salaire_base_employe" class="mt-4">
                <label for="salaire_base_commun" class="block text-sm font-medium text-gray-700 mb-1">Salaire de base commun</label>
                <div class="flex">
                    <input type="number" id="salaire_base_commun" x-model="formData.salaire_base_commun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                    <span class="ml-2 inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">FCFA</span>
                </div>
                <div x-show="errors.salaire_base_commun" class="text-danger-500 text-sm mt-1" x-text="errors.salaire_base_commun"></div>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center">
                <input type="checkbox" id="appliquer_indemnites_communes" x-model="formData.appliquer_indemnites_communes" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="appliquer_indemnites_communes" class="ml-2 block text-sm text-gray-700">Appliquer des indemnités communes</label>
            </div>
            <p class="text-sm text-gray-500 mt-1">Si activé, les mêmes indemnités seront appliquées à tous les employés.</p>
            
            <div x-show="formData.appliquer_indemnites_communes" class="mt-4 space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Indemnités communes</h3>
                
                <template x-for="(indemnite, index) in formData.indemnites_communes" :key="index">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-gray-700" x-text="indemnite.libelle || 'Nouvelle indemnité'"></h4>
                            <button type="button" @click="removeIndemniteCommune(index)" class="text-danger-500 hover:text-danger-700">
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
                                <input type="number" :id="'indemnite_montant_' + index" x-model="indemnite.montant" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div x-show="indemnite.type === 'pourcentage'">
                                <label :for="'indemnite_taux_' + index" class="block text-sm font-medium text-gray-700 mb-1">Taux (%)</label>
                                <input type="number" :id="'indemnite_taux_' + index" x-model="indemnite.taux" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'indemnite_imposable_' + index" class="flex items-center">
                                    <input type="checkbox" :id="'indemnite_imposable_' + index" x-model="indemnite.imposable" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Imposable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addIndemniteCommune()" class="flex items-center text-primary-600 hover:text-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter une indemnité
                </button>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center">
                <input type="checkbox" id="appliquer_primes_communes" x-model="formData.appliquer_primes_communes" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="appliquer_primes_communes" class="ml-2 block text-sm text-gray-700">Appliquer des primes communes</label>
            </div>
            <p class="text-sm text-gray-500 mt-1">Si activé, les mêmes primes seront appliquées à tous les employés.</p>
            
            <div x-show="formData.appliquer_primes_communes" class="mt-4 space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Primes communes</h3>
                
                <template x-for="(prime, index) in formData.primes_communes" :key="index">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium text-gray-700" x-text="prime.libelle || 'Nouvelle prime'"></h4>
                            <button type="button" @click="removePrimeCommune(index)" class="text-danger-500 hover:text-danger-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label :for="'prime_libelle_' + index" class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                                <input type="text" :id="'prime_libelle_' + index" x-model="prime.libelle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'prime_type_' + index" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select :id="'prime_type_' + index" x-model="prime.type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <option value="montant_fixe">Montant fixe</option>
                                    <option value="pourcentage">Pourcentage du salaire de base</option>
                                </select>
                            </div>
                            <div x-show="prime.type === 'montant_fixe'">
                                <label :for="'prime_montant_' + index" class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA)</label>
                                <input type="number" :id="'prime_montant_' + index" x-model="prime.montant" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div x-show="prime.type === 'pourcentage'">
                                <label :for="'prime_taux_' + index" class="block text-sm font-medium text-gray-700 mb-1">Taux (%)</label>
                                <input type="number" :id="'prime_taux_' + index" x-model="prime.taux" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label :for="'prime_imposable_' + index" class="flex items-center">
                                    <input type="checkbox" :id="'prime_imposable_' + index" x-model="prime.imposable" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Imposable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addPrimeCommune()" class="flex items-center text-primary-600 hover:text-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Ajouter une prime
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
    <div x-show="currentStep === 3" class="space-y-6">
        <div class="bg-primary-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold text-primary-700 mb-2">Aperçu et validation</h2>
            <p class="text-primary-600">Vérifiez les informations et générez les bulletins de paie.</p>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Récapitulatif des bulletins à générer</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Informations générales</h4>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Nombre d'employés</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="selectedEmployes.length"></dd>
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
                        <h4 class="font-medium text-gray-700 mb-2">Paramètres</h4>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Salaire de base</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="formData.utiliser_salaire_base_employe ? 'Salaire de base de chaque employé' : formatMontant(formData.salaire_base_commun) + ' FCFA (commun)'"></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Indemnités communes</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="formData.appliquer_indemnites_communes ? formData.indemnites_communes.length + ' indemnité(s)' : 'Non'"></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Primes communes</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="formData.appliquer_primes_communes ? formData.primes_communes.length + ' prime(s)' : 'Non'"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mt-6">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Liste des employés sélectionnés</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom complet</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricule</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salaire de base</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="employe in selectedEmployesDetails" :key="employe.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="employe.nom_complet"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="employe.matricule || 'Non défini'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="employe.poste || 'Non défini'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500" x-text="formatMontant(formData.utiliser_salaire_base_employe ? employe.salaire_base : formData.salaire_base_commun) + ' FCFA'"></div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center">
                <input type="checkbox" id="valider_directement" x-model="formData.valider_directement" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                <label for="valider_directement" class="ml-2 block text-sm text-gray-700">Valider directement les bulletins</label>
            </div>
            <p class="text-sm text-gray-500 mt-1">Si coché, les bulletins seront directement validés après leur génération. Sinon, ils seront enregistrés en brouillon.</p>
        </div>
    </div>
    
    <!-- Boutons de navigation -->
    <div class="mt-8 pt-5 border-t border-gray-200 flex justify-between">
        <button 
            type="button" 
            @click="previousStep()" 
            x-show="currentStep > 1"
            class="btn-secondary py-2 px-4 rounded-md shadow-sm text-sm font-medium">
            Précédent
        </button>
        <div class="flex space-x-3">
            <button 
                type="button" 
                @click="nextStep()" 
                x-show="currentStep < 3"
                class="btn-primary py-2 px-4 rounded-md shadow-sm text-sm font-medium">
                Suivant
            </button>
            <button 
                type="button" 
                @click="generateBulletins()" 
                x-show="currentStep === 3"
                :disabled="isSubmitting"
                class="btn-success py-2 px-4 rounded-md shadow-sm text-sm font-medium flex items-center">
                <span x-show="isSubmitting" class="mr-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span x-text="isSubmitting ? 'Génération en cours...' : 'Générer les bulletins'"></span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bulletinMasseWizard() {
        return {
            currentStep: 1,
            isSubmitting: false,
            selectAll: false,
            selectedEmployesDetails: [],
            formData: {
                employeur_ids: [],
                configuration_paie_id: '{{ $configurationDefaut ? $configurationDefaut->id : '' }}',
                periode_debut: '{{ \Carbon\Carbon::now()->startOfMonth()->format("Y-m-d") }}',
                periode_fin: '{{ \Carbon\Carbon::now()->endOfMonth()->format("Y-m-d") }}',
                date_paiement: '{{ \Carbon\Carbon::now()->format("Y-m-d") }}',
                utiliser_salaire_base_employe: true,
                salaire_base_commun: 0,
                appliquer_indemnites_communes: false,
                indemnites_communes: [],
                appliquer_primes_communes: false,
                primes_communes: [],
                calcul_auto: true,
                valider_directement: false
            },
            errors: {},
            
            get selectedEmployes() {
                return this.formData.employeur_ids || [];
            },
            
            nextStep() {
                if (this.currentStep === 1) {
                    if (!this.validateStep1()) {
                        return;
                    }
                    this.updateSelectedEmployesDetails();
                }
                
                if (this.currentStep === 2) {
                    if (!this.validateStep2()) {
                        return;
                    }
                }
                
                this.currentStep++;
            },
            
            previousStep() {
                this.currentStep--;
            },
            
            validateStep1() {
                this.errors = {};
                
                if (!this.formData.employeur_ids || this.formData.employeur_ids.length === 0) {
                    this.errors.employeur_ids = 'Veuillez sélectionner au moins un employé';
                }
                
                return Object.keys(this.errors).length === 0;
            },
            
            validateStep2() {
                this.errors = {};
                
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
                
                if (!this.formData.utiliser_salaire_base_employe && (!this.formData.salaire_base_commun || this.formData.salaire_base_commun <= 0)) {
                    this.errors.salaire_base_commun = 'Veuillez saisir un salaire de base commun valide';
                }
                
                // Valider les indemnités communes
                if (this.formData.appliquer_indemnites_communes) {
                    this.formData.indemnites_communes.forEach((indemnite, index) => {
                        if (!indemnite.libelle) {
                            this.errors[`indemnites_communes.${index}.libelle`] = 'Veuillez saisir un libellé';
                        }
                        
                        if (indemnite.type === 'montant_fixe' && (!indemnite.montant || indemnite.montant <= 0)) {
                            this.errors[`indemnites_communes.${index}.montant`] = 'Veuillez saisir un montant valide';
                        }
                        
                        if (indemnite.type === 'pourcentage' && (!indemnite.taux || indemnite.taux <= 0)) {
                            this.errors[`indemnites_communes.${index}.taux`] = 'Veuillez saisir un taux valide';
                        }
                    });
                }
                
                // Valider les primes communes
                if (this.formData.appliquer_primes_communes) {
                    this.formData.primes_communes.forEach((prime, index) => {
                        if (!prime.libelle) {
                            this.errors[`primes_communes.${index}.libelle`] = 'Veuillez saisir un libellé';
                        }
                        
                        if (prime.type === 'montant_fixe' && (!prime.montant || prime.montant <= 0)) {
                            this.errors[`primes_communes.${index}.montant`] = 'Veuillez saisir un montant valide';
                        }
                        
                        if (prime.type === 'pourcentage' && (!prime.taux || prime.taux <= 0)) {
                            this.errors[`primes_communes.${index}.taux`] = 'Veuillez saisir un taux valide';
                        }
                    });
                }
                
                return Object.keys(this.errors).length === 0;
            },
            
            toggleSelectAll() {
                if (this.selectAll) {
                    // Sélectionner tous les employés
                    this.formData.employeur_ids = @json($employeurs->pluck('id'));
                } else {
                    // Désélectionner tous les employés
                    this.formData.employeur_ids = [];
                }
            },
            
            updateSelectAll() {
                const allEmployes = @json($employeurs->pluck('id'));
                this.selectAll = allEmployes.length === this.formData.employeur_ids.length;
            },
            
            updateSelectedEmployesDetails() {
                // Récupérer les détails des employés sélectionnés
                const allEmployes = @json($employeurs);
                this.selectedEmployesDetails = allEmployes.filter(employe => this.formData.employeur_ids.includes(employe.id));
            },
            
            addIndemniteCommune() {
                this.formData.indemnites_communes.push({
                    libelle: '',
                    type: 'montant_fixe',
                    montant: 0,
                    taux: 0,
                    imposable: true
                });
            },
            
            removeIndemniteCommune(index) {
                this.formData.indemnites_communes.splice(index, 1);
            },
            
            addPrimeCommune() {
                this.formData.primes_communes.push({
                    libelle: '',
                    type: 'montant_fixe',
                    montant: 0,
                    taux: 0,
                    imposable: true
                });
            },
            
            removePrimeCommune(index) {
                this.formData.primes_communes.splice(index, 1);
            },
            
            generateBulletins() {
                if (this.isSubmitting) {
                    return;
                }
                
                this.isSubmitting = true;
                
                fetch('{{ route('paie.bulletins.wizard.process-masse') }}', {
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
                    console.error('Erreur lors de la génération des bulletins:', error);
                    showNotification('error', 'Erreur lors de la génération des bulletins');
                });
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
