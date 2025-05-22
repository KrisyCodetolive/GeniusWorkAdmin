<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nouvelle demande de permutation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('permutations.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Motif -->
                            <div class="md:col-span-2">
                                <label for="motif" class="block text-sm font-medium text-gray-700">Motif de la permutation</label>
                                <textarea id="motif" name="motif" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('motif') }}</textarea>
                                @error('motif')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Séparateur -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="px-2 bg-white text-sm text-gray-500">Employé 1</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Employeur 1 -->
                            <div>
                                <label for="employeur_1_id" class="block text-sm font-medium text-gray-700">Employé 1</label>
                                <select id="employeur_1_id" name="employeur_1_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @if(isset($employeur))
                                        <option value="{{ $employeur->id }}">{{ $employeur->user->name }}</option>
                                    @else
                                        @foreach(App\Models\Employeur::with('user')->get() as $emp)
                                            <option value="{{ $emp->id }}" {{ old('employeur_1_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('employeur_1_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Plage Horaire 1 -->
                            <div>
                                <label for="plage_horaire_1_id" class="block text-sm font-medium text-gray-700">Horaire actuel</label>
                                <select id="plage_horaire_1_id" name="plage_horaire_1_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($plagesHoraires as $plage)
                                        <option value="{{ $plage->id }}" {{ old('plage_horaire_1_id') == $plage->id ? 'selected' : '' }}>{{ $plage->nom }} ({{ $plage->getPlageFormatee() }})</option>
                                    @endforeach
                                </select>
                                @error('plage_horaire_1_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Séparateur -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="px-2 bg-white text-sm text-gray-500">Employé 2</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Employeur 2 -->
                            <div>
                                <label for="employeur_2_id" class="block text-sm font-medium text-gray-700">Employé 2</label>
                                <select id="employeur_2_id" name="employeur_2_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($employeurs as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employeur_2_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('employeur_2_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Plage Horaire 2 -->
                            <div>
                                <label for="plage_horaire_2_id" class="block text-sm font-medium text-gray-700">Horaire actuel</label>
                                <select id="plage_horaire_2_id" name="plage_horaire_2_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($plagesHoraires as $plage)
                                        <option value="{{ $plage->id }}" {{ old('plage_horaire_2_id') == $plage->id ? 'selected' : '' }}>{{ $plage->nom }} ({{ $plage->getPlageFormatee() }})</option>
                                    @endforeach
                                </select>
                                @error('plage_horaire_2_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-6 space-x-3">
                            <a href="{{ route('permutations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
