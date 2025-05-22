<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Détails de la permutation') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('permutations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Retour') }}
                </a>
                @if($permutation->statut == 'en_attente')
                <a href="{{ route('permutations.edit', $permutation->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Modifier') }}
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Statut -->
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($permutation->statut == 'approuve') bg-green-100 text-green-800 
                                @elseif($permutation->statut == 'en_attente') bg-yellow-100 text-yellow-800 
                                @elseif($permutation->statut == 'rejete') bg-red-100 text-red-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $permutation->statut)) }}
                            </span>
                        </div>
                        
                        @if($isManager && $permutation->statut == 'en_attente')
                        <div class="flex space-x-2">
                            <form action="{{ route('permutations.valider', $permutation->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approuver
                                </button>
                            </form>
                            
                            <button type="button" onclick="document.getElementById('modal-rejeter').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Rejeter
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Informations générales -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informations générales</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Date de permutation</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permutation->date->format('d/m/Y') }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $permutation->statut)) }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Motif</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permutation->motif }}</dd>
                                </div>
                                @if($permutation->commentaire)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Commentaire</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permutation->commentaire }}</dd>
                                </div>
                                @endif
                                @if($permutation->validateur)
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Validé par</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permutation->validateur->name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Date de validation</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $permutation->date_validation->format('d/m/Y H:i') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Détails de la permutation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Employé 1 -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                            <div class="bg-blue-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-blue-900">Employé 1</h3>
                            </div>
                            <div class="p-4">
                                <dl class="divide-y divide-gray-200">
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->employeur1->user->name }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Département</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->employeur1->departement->nom ?? 'Non assigné' }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Horaire actuel</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->plageHoraire1->nom }} ({{ $permutation->plageHoraire1->getPlageFormatee() }})</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Employé 2 -->
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                            <div class="bg-green-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-green-900">Employé 2</h3>
                            </div>
                            <div class="p-4">
                                <dl class="divide-y divide-gray-200">
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->employeur2->user->name }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Département</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->employeur2->departement->nom ?? 'Non assigné' }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Horaire actuel</dt>
                                        <dd class="text-sm text-gray-900">{{ $permutation->plageHoraire2->nom }} ({{ $permutation->plageHoraire2->getPlageFormatee() }})</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Visualisation de la permutation -->
                    <div class="mt-8 bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-purple-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-purple-900">Résumé de la permutation</h3>
                        </div>
                        <div class="p-4">
                            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">{{ $permutation->employeur1->user->name }}</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $permutation->plageHoraire1->getPlageFormatee() }}</p>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">{{ $permutation->employeur2->user->name }}</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $permutation->plageHoraire2->getPlageFormatee() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($permutation->statut == 'en_attente')
                    <!-- Bouton de suppression -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <form action="{{ route('permutations.destroy', $permutation->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande de permutation ?');">
                            @csrf
                            @method('DELETE')
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de rejet -->
    <div id="modal-rejeter" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Motif de rejet</h3>
                <form action="{{ route('permutations.rejeter', $permutation->id) }}" method="POST">
                    @csrf
                    <div>
                        <label for="commentaire" class="block text-sm font-medium text-gray-700">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required></textarea>
                    </div>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('modal-rejeter').classList.add('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Annuler
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Rejeter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
