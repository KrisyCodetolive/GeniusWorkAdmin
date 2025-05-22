@extends('layouts.app')

@section('title', 'Fusion de filiales')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Fusion de filiales</h1>
        <a href="{{ route('admin.filiales.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Informations importantes</h2>
        </div>
        <div class="p-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention :</strong> La fusion de filiales est une opération irréversible. Tous les départements, employés et données associées à la filiale source seront transférés vers la filiale cible.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Conséquences de la fusion :</h3>
                <ul class="list-disc pl-5 text-gray-600 space-y-1">
                    <li>Tous les départements de la filiale source seront transférés vers la filiale cible</li>
                    <li>Les employés associés à la filiale source seront réaffectés à la filiale cible</li>
                    <li>Les codes des départements seront automatiquement mis à jour</li>
                    <li>La filiale source sera marquée comme inactive après la fusion</li>
                    <li>L'historique des deux filiales sera préservé</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Formulaire de fusion</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.filiales.fusion') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="filiale_cible_id" class="block text-sm font-medium text-gray-700 mb-2">Filiale cible (qui recevra les départements et employés)</label>
                    <select id="filiale_cible_id" name="filiale_cible_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('filiale_cible_id') border-red-500 @enderror" required>
                        <option value="">Sélectionnez une filiale</option>
                        @foreach($filiales as $filiale)
                            <option value="{{ $filiale->id }}" {{ old('filiale_cible_id') == $filiale->id ? 'selected' : '' }}>
                                {{ $filiale->nom }} ({{ $filiale->code }}) - {{ $filiale->departements->count() }} départements
                            </option>
                        @endforeach
                    </select>
                    @error('filiale_cible_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="filiale_source_id" class="block text-sm font-medium text-gray-700 mb-2">Filiale source (qui sera fusionnée dans la cible)</label>
                    <select id="filiale_source_id" name="filiale_source_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('filiale_source_id') border-red-500 @enderror" required>
                        <option value="">Sélectionnez une filiale</option>
                        @foreach($filiales as $filiale)
                            <option value="{{ $filiale->id }}" {{ old('filiale_source_id') == $filiale->id ? 'selected' : '' }}>
                                {{ $filiale->nom }} ({{ $filiale->code }}) - {{ $filiale->departements->count() }} départements
                            </option>
                        @endforeach
                    </select>
                    @error('filiale_source_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <strong>Confirmation requise :</strong> Veuillez confirmer que vous comprenez les conséquences de cette action et que vous souhaitez procéder à la fusion.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Fusionner les filiales
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cibleSelect = document.getElementById('filiale_cible_id');
        const sourceSelect = document.getElementById('filiale_source_id');
        
        // Fonction pour mettre à jour les options disponibles
        function updateOptions() {
            const cibleValue = cibleSelect.value;
            const sourceValue = sourceSelect.value;
            
            // Réinitialiser les options
            Array.from(cibleSelect.options).forEach(option => {
                option.disabled = option.value === sourceValue && option.value !== '';
            });
            
            Array.from(sourceSelect.options).forEach(option => {
                option.disabled = option.value === cibleValue && option.value !== '';
            });
        }
        
        // Ajouter des écouteurs d'événements
        cibleSelect.addEventListener('change', updateOptions);
        sourceSelect.addEventListener('change', updateOptions);
        
        // Initialiser les options
        updateOptions();
    });
</script>
@endsection
