<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Détails des heures supplémentaires') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('supplementaires.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Retour') }}
                </a>
                @if($supplementaire->statut == 'en_attente')
                <a href="{{ route('supplementaires.edit', $supplementaire->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                                @if($supplementaire->statut == 'approuve') bg-green-100 text-green-800 
                                @elseif($supplementaire->statut == 'en_attente') bg-yellow-100 text-yellow-800 
                                @elseif($supplementaire->statut == 'rejete') bg-red-100 text-red-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $supplementaire->statut)) }}
                            </span>
                        </div>
                        
                        @if($isManager && $supplementaire->statut == 'en_attente')
                        <div class="flex space-x-2">
                            <form action="{{ route('supplementaires.valider', $supplementaire->id) }}" method="POST" class="inline">
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

                    <!-- Informations -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Informations générales</h3>
                            <div class="mt-4 border-t border-gray-200">
                                <dl class="divide-y divide-gray-200">
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Employé</dt>
                                        <dd class="text-sm text-gray-900">{{ $supplementaire->employeur->user->name }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                                        <dd class="text-sm text-gray-900">{{ $supplementaire->date->format('d/m/Y') }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Heure de début</dt>
                                        <dd class="text-sm text-gray-900">{{ $supplementaire->heure_debut->format('H:i') }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Heure de fin</dt>
                                        <dd class="text-sm text-gray-900">{{ $supplementaire->heure_fin->format('H:i') }}</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Durée</dt>
                                        <dd class="text-sm text-gray-900">{{ number_format($supplementaire->nombre_heures, 2) }} heures</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Taux de majoration</dt>
                                        <dd class="text-sm text-gray-900">{{ $supplementaire->taux_majoration }}%</dd>
                                    </div>
                                    <div class="py-3 flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Montant</dt>
                                        <dd class="text-sm text-gray-900 font-semibold">{{ $supplementaire->getMontantFormate() }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Détails de la demande</h3>
                            <div class="mt-4 border-t border-gray-200">
                                <dl class="divide-y divide-gray-200">
                                    <div class="py-3">
                                        <dt class="text-sm font-medium text-gray-500">Motif</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $supplementaire->motif }}</dd>
                                    </div>
                                    
                                    @if($supplementaire->commentaire)
                                    <div class="py-3">
                                        <dt class="text-sm font-medium text-gray-500">Commentaire</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $supplementaire->commentaire }}</dd>
                                    </div>
                                    @endif
                                    
                                    @if($supplementaire->validateur)
                                    <div class="py-3">
                                        <dt class="text-sm font-medium text-gray-500">Validé par</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $supplementaire->validateur->name }}</dd>
                                    </div>
                                    <div class="py-3">
                                        <dt class="text-sm font-medium text-gray-500">Date de validation</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $supplementaire->date_validation->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>

                    @if($supplementaire->statut == 'en_attente')
                    <!-- Bouton de suppression -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <form action="{{ route('supplementaires.destroy', $supplementaire->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
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
                <form action="{{ route('supplementaires.rejeter', $supplementaire->id) }}" method="POST">
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
