<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Modifier la permutation') }}
            </h2>
            <a href="{{ route('permutations.show', $permutation->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Retour') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('permutations.update', $permutation->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Statut actuel -->
                        <div class="mb-6">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($permutation->statut == 'approuve') bg-green-100 text-green-800 
                                @elseif($permutation->statut == 'en_attente') bg-yellow-100 text-yellow-800 
                                @elseif($permutation->statut == 'rejete') bg-red-100 text-red-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $permutation->statut)) }}
                            </span>
                        </div>

                        <!-- Date de permutation -->
                        <div class="mb-4">
                            <x-input-label for="date" :value="__('Date de permutation')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="$permutation->date->format('Y-m-d')" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <!-- Employé 1 (non modifiable) -->
                        <div class="mb-4">
                            <x-input-label :value="__('Employé 1')" />
                            <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-gray-700">
                                {{ $permutation->employeur1->user->name }}
                                <input type="hidden" name="employeur1_id" value="{{ $permutation->employeur1_id }}">
                            </div>
                        </div>

                        <!-- Plage horaire 1 -->
                        <div class="mb-4">
                            <x-input-label for="plage_horaire1_id" :value="__('Horaire actuel (Employé 1)')" />
                            <select id="plage_horaire1_id" name="plage_horaire1_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($plagesHoraires as $plage)
                                    <option value="{{ $plage->id }}" {{ $permutation->plage_horaire1_id == $plage->id ? 'selected' : '' }}>
                                        {{ $plage->nom }} ({{ $plage->getPlageFormatee() }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('plage_horaire1_id')" class="mt-2" />
                        </div>

                        <!-- Employé 2 -->
                        <div class="mb-4">
                            <x-input-label for="employeur2_id" :value="__('Employé 2')" />
                            <select id="employeur2_id" name="employeur2_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($employes as $employe)
                                    <option value="{{ $employe->id }}" {{ $permutation->employeur2_id == $employe->id ? 'selected' : '' }}>
                                        {{ $employe->user->name }} - {{ $employe->departement->nom ?? 'Sans département' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employeur2_id')" class="mt-2" />
                        </div>

                        <!-- Plage horaire 2 -->
                        <div class="mb-4">
                            <x-input-label for="plage_horaire2_id" :value="__('Horaire actuel (Employé 2)')" />
                            <select id="plage_horaire2_id" name="plage_horaire2_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($plagesHoraires as $plage)
                                    <option value="{{ $plage->id }}" {{ $permutation->plage_horaire2_id == $plage->id ? 'selected' : '' }}>
                                        {{ $plage->nom }} ({{ $plage->getPlageFormatee() }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('plage_horaire2_id')" class="mt-2" />
                        </div>

                        <!-- Motif -->
                        <div class="mb-4">
                            <x-input-label for="motif" :value="__('Motif de la permutation')" />
                            <textarea id="motif" name="motif" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ $permutation->motif }}</textarea>
                            <x-input-error :messages="$errors->get('motif')" class="mt-2" />
                        </div>

                        <!-- Commentaire -->
                        <div class="mb-4">
                            <x-input-label for="commentaire" :value="__('Commentaire (optionnel)')" />
                            <textarea id="commentaire" name="commentaire" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $permutation->commentaire }}</textarea>
                            <x-input-error :messages="$errors->get('commentaire')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Mettre à jour') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
