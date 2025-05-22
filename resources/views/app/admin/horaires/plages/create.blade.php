<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Créer une nouvelle plage horaire') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.horaires.plages.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom -->
                            <div>
                                <x-label for="nom" :value="__('Nom')" />
                                <x-input id="nom" class="block mt-1 w-full" type="text" name="nom" :value="old('nom')" required />
                                @error('nom')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <x-label for="type" :value="__('Type')" />
                                <select id="type" name="type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="standard" {{ old('type') == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="special" {{ old('type') == 'special' ? 'selected' : '' }}>Spécial</option>
                                </select>
                                @error('type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Heure de début -->
                            <div>
                                <x-label for="heure_debut" :value="__('Heure de début')" />
                                <x-input id="heure_debut" class="block mt-1 w-full" type="time" name="heure_debut" :value="old('heure_debut')" required />
                                @error('heure_debut')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Heure de fin -->
                            <div>
                                <x-label for="heure_fin" :value="__('Heure de fin')" />
                                <x-input id="heure_fin" class="block mt-1 w-full" type="time" name="heure_fin" :value="old('heure_fin')" required />
                                @error('heure_fin')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Couleur -->
                            <div>
                                <x-label for="couleur" :value="__('Couleur')" />
                                <x-input id="couleur" class="block mt-1 w-full" type="color" name="couleur" :value="old('couleur', '#3b82f6')" />
                                @error('couleur')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Est une pause -->
                            <div class="flex items-center mt-6">
                                <input id="est_pause" type="checkbox" name="est_pause" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ old('est_pause') ? 'checked' : '' }}>
                                <label for="est_pause" class="ml-2 block text-sm text-gray-900">{{ __('Cette plage est une pause') }}</label>
                                @error('est_pause')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Durée maximale (pour les pauses) -->
                            <div id="duree_max_container" class="{{ old('est_pause') ? '' : 'hidden' }}">
                                <x-label for="duree_max_minutes" :value="__('Durée maximale (minutes)')" />
                                <x-input id="duree_max_minutes" class="block mt-1 w-full" type="number" name="duree_max_minutes" :value="old('duree_max_minutes')" min="1" />
                                @error('duree_max_minutes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <x-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.horaires.plages.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                {{ __('Annuler') }}
                            </a>
                            <x-button>
                                {{ __('Créer') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const estPauseCheckbox = document.getElementById('est_pause');
            const dureeMaxContainer = document.getElementById('duree_max_container');
            
            estPauseCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    dureeMaxContainer.classList.remove('hidden');
                } else {
                    dureeMaxContainer.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
