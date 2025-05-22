@extends('layouts.app')

@section('title', 'Gestion des appareils biométriques')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4 md:mb-0">Gestion des appareils biométriques</h1>
        
        <div class="flex space-x-2">
            <a href="{{ route('biometrique.appareils.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Ajouter un appareil
            </a>
            
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="sync-all-btn">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Tout synchroniser
            </button>
            
            <div class="relative" x-data="{ open: false }">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" @click="open = !open">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                    Actions
                </button>
                
                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" x-cloak>
                    <div class="py-1">
                        <a href="{{ route('biometrique.appareils.export') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Exporter (CSV)
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" id="import-btn">
                            Importer
                        </a>
                        <a href="{{ route('biometrique.appareils.rapport') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Générer un rapport
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtres -->
    
    <!-- Tableau des appareils -->
</div>

<!-- Modals -->
<x-biometrique.components.modals.device-sync-modal />
<x-biometrique.components.modals.device-test-modal />

<!-- Import Modal -->
<div x-data="{ open: false }" x-on:show-import-modal.window="open = true" x-on:hide-import-modal.window="open = false" class="relative">
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40"
        x-cloak
    ></div>

    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-50 overflow-y-auto"
        x-cloak
    >
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div class="absolute right-0 top-0 pr-4 pt-4">
                    <button 
                        type="button" 
                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        x-on:click="open = false"
                    >
                        <span class="sr-only">Fermer</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">
                            Importer des appareils
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Importez une liste d'appareils à partir d'un fichier CSV.
                            </p>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('biometrique.appareils.import') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="import_file" class="block text-sm font-medium text-gray-700">Fichier CSV</label>
                            <input type="file" name="import_file" id="import_file" accept=".csv" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Options d'importation</label>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="update_existing" id="update_existing" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="update_existing" class="ml-2 block text-sm text-gray-700">Mettre à jour les appareils existants</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="skip_header" id="skip_header" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                    <label for="skip_header" class="ml-2 block text-sm text-gray-700">Ignorer la première ligne (en-têtes)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <a href="{{ route('biometrique.appareils.template') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                Télécharger un modèle CSV
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                            Importer
                        </button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" x-on:click="open = false">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Import button
    document.getElementById('import-btn').addEventListener('click', function(e) {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('show-import-modal'));
    });
    
    // Sync all button
    document.getElementById('sync-all-btn').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir synchroniser tous les appareils actifs ? Cette opération peut prendre plusieurs minutes.')) {
            // Show loading state
            const button = this;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Synchronisation en cours...';
            button.disabled = true;
            
            // AJAX call to sync all devices
            fetch('/biometrique/appareils/sync-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                // Show notification
                const notification = document.createElement('div');
                notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg';
                notification.textContent = data.message || 'Synchronisation lancée avec succès';
                document.body.appendChild(notification);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                // Error notification
                const notification = document.createElement('div');
                notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg';
                notification.textContent = 'Erreur lors de la synchronisation';
                document.body.appendChild(notification);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            });
        }
    });
    
    // Initialize sync and test modals
    window.syncDevice = function(deviceId) {
        Alpine.store('syncModal', { appareilId: deviceId });
        window.dispatchEvent(new CustomEvent('show-sync-modal', { detail: { appareilId: deviceId } }));
    };
    
    window.testDevice = function(deviceId) {
        Alpine.store('testModal', { appareilId: deviceId });
        window.dispatchEvent(new CustomEvent('show-test-modal', { detail: { appareilId: deviceId } }));
    };
    
    // Listen for sync completion event to refresh data
    window.addEventListener('deviceSyncCompleted', function(event) {
        // Refresh the device data
        // This could be an AJAX call to get updated data for the specific device
        // or a full page refresh if needed
        if (event.detail && event.detail.appareilId) {
            console.log('Device sync completed for ID:', event.detail.appareilId);
            // Refresh the specific device data here
        }
    });
});
</script>
@endpush
