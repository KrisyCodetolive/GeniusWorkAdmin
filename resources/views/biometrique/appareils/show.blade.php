@extends('layouts.app')

@section('title', 'Détails de l\'appareil biométrique')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('biometrique.appareils.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $appareil->nom }}</h1>
            
            @if($appareil->statut == 'actif')
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Actif
                </span>
            @elseif($appareil->statut == 'inactif')
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    Inactif
                </span>
            @elseif($appareil->statut == 'maintenance')
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    Maintenance
                </span>
            @elseif($appareil->statut == 'erreur')
                <span class="ml-3 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    Erreur
                </span>
            @endif
        </div>
        
        <div class="flex space-x-2">
            <a href="{{ route('biometrique.appareils.edit', $appareil) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            
            <button type="button" onclick="syncDevice('{{ $appareil->id }}')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Synchroniser
            </button>
            
            <a href="{{ route('biometrique.appareils.sync-config', $appareil->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Sync Auto
            </a>
            
            <button type="button" onclick="testDevice('{{ $appareil->id }}')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"></path>
                </svg>
                Tester la connexion
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
                        <a href="{{ route('biometrique.appareils.logs', $appareil) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Exporter les logs
                        </a>
                        <a href="{{ route('biometrique.appareils.users.export', $appareil) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Exporter les utilisateurs
                        </a>
                        <a href="{{ route('biometrique.appareils.sync-config', $appareil->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Configuration de synchronisation
                        </a>
                        <form action="{{ route('biometrique.appareils.destroy', $appareil) }}" method="POST" class="block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet appareil ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">
                                Supprimer l'appareil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Statut de l'appareil -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Statut de l'appareil</h2>
            </div>
            <div class="p-4">
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Statistiques</h2>
            </div>
            <div class="p-4">
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Actions rapides</h2>
            </div>
            <div class="p-4">
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Informations générales -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Informations générales</h2>
            </div>
            <div class="p-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->nom }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Site</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->site->nom ?? 'Non assigné' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fabricant</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->fabricant }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Modèle</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->modele }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Numéro de série</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->numero_serie ?? 'Non renseigné' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date d'ajout</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dernière modification</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dernière synchronisation</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $appareil->derniere_sync ? $appareil->derniere_sync->format('d/m/Y H:i') : 'Jamais' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Configuration réseau -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Configuration réseau</h2>
            </div>
            <div class="p-4">
            </div>
        </div>
    </div>
    
    <!-- Onglets -->
    <div x-data="{ activeTab: 'users' }" class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'users'" :class="{ 'border-blue-500 text-blue-600': activeTab === 'users', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'users' }" class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Utilisateurs
                </button>
                <button @click="activeTab = 'logs'" :class="{ 'border-blue-500 text-blue-600': activeTab === 'logs', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'logs' }" class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Logs d'activité
                </button>
                <button @click="activeTab = 'pointages'" :class="{ 'border-blue-500 text-blue-600': activeTab === 'pointages', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pointages' }" class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Pointages
                </button>
                <button @click="activeTab = 'map'" :class="{ 'border-blue-500 text-blue-600': activeTab === 'map', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'map' }" class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Carte
                </button>
            </nav>
        </div>
        
        <div class="p-4">
            <!-- Utilisateurs -->
            <div x-show="activeTab === 'users'">
            </div>
            
            <!-- Logs -->
            <div x-show="activeTab === 'logs'" x-cloak>
            </div>
            
            <!-- Pointages -->
            <div x-show="activeTab === 'pointages'" x-cloak>
            </div>
            
            <!-- Carte -->
            <div x-show="activeTab === 'map'" x-cloak>
            </div>
        </div>
    </div>
    
    <!-- Notes -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Notes</h2>
        </div>
        <div class="p-4">
            <form action="{{ route('biometrique.appareils.update-notes', $appareil) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <textarea name="notes" id="notes" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ $appareil->notes }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Enregistrer les notes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals -->
<x-biometrique.components.modals.device-sync-modal :appareil="$appareil" />
<x-biometrique.components.modals.device-test-modal :appareil="$appareil" />
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sync and test modals
    window.syncDevice = function(deviceId) {
        Alpine.store('syncModal', { appareilId: deviceId });
        window.dispatchEvent(new CustomEvent('show-sync-modal', { detail: { appareilId: deviceId } }));
    };
    
    window.testDevice = function(deviceId) {
        Alpine.store('testModal', { appareilId: deviceId });
        window.dispatchEvent(new CustomEvent('show-test-modal', { detail: { appareilId: deviceId } }));
    };
});
</script>
@endpush
