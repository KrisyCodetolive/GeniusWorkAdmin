@props(['appareil', 'showActions' => true])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <div class="p-4 border-b">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ $appareil->nom }}</h3>
                <p class="text-sm text-gray-600">{{ $appareil->modele }} ({{ $appareil->fabricant }})</p>
            </div>
            <div class="flex items-center">
                @if($appareil->statut === 'actif')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="h-2 w-2 mr-1 bg-green-400 rounded-full"></span>
                        Actif
                    </span>
                @elseif($appareil->statut === 'inactif')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="h-2 w-2 mr-1 bg-gray-400 rounded-full"></span>
                        Inactif
                    </span>
                @elseif($appareil->statut === 'maintenance')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <span class="h-2 w-2 mr-1 bg-yellow-400 rounded-full"></span>
                        Maintenance
                    </span>
                @elseif($appareil->statut === 'erreur')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <span class="h-2 w-2 mr-1 bg-red-400 rounded-full"></span>
                        Erreur
                    </span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="p-4 bg-gray-50">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Adresse IP</p>
                <p class="font-medium">{{ $appareil->adresse_ip }}</p>
            </div>
            <div>
                <p class="text-gray-500">Port</p>
                <p class="font-medium">{{ $appareil->port }}</p>
            </div>
            <div>
                <p class="text-gray-500">Site</p>
                <p class="font-medium">{{ $appareil->site->nom ?? 'Non assigné' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Dernière synchronisation</p>
                <p class="font-medium">{{ $appareil->derniere_synchronisation ? $appareil->derniere_synchronisation->diffForHumans() : 'Jamais' }}</p>
            </div>
        </div>
    </div>
    
    @if($appareil->stats ?? null)
    <div class="p-4 border-t">
        <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-blue-50 rounded p-2">
                <p class="text-xs text-gray-500">Utilisateurs</p>
                <p class="font-semibold">{{ $appareil->stats['total_users'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 rounded p-2">
                <p class="text-xs text-gray-500">Pointages</p>
                <p class="font-semibold">{{ $appareil->stats['total_pointages'] ?? 0 }}</p>
            </div>
            <div class="bg-purple-50 rounded p-2">
                <p class="text-xs text-gray-500">Aujourd'hui</p>
                <p class="font-semibold">{{ $appareil->stats['pointages_today'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif
    
    @if($showActions)
    <div class="p-4 border-t flex justify-between">
        <div class="space-x-2">
            <a href="{{ route('biometrique.appareils.show', $appareil->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                Détails
            </a>
            <a href="{{ route('biometrique.appareils.edit', $appareil->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Modifier
            </a>
        </div>
        <div>
            <button onclick="synchronizeDevice('{{ $appareil->id }}')" class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                Synchroniser
            </button>
        </div>
    </div>
    @endif
</div>
