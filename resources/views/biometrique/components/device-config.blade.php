@props(['appareil'])

<div class="bg-white rounded-lg shadow-md p-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Configuration</h3>
    
    <div class="space-y-4">
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Paramètres réseau</h4>
            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Adresse IP</p>
                        <p class="font-medium">{{ $appareil->adresse_ip }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Port</p>
                        <p class="font-medium">{{ $appareil->port }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Protocole</p>
                        <p class="font-medium">{{ $appareil->protocole }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Timeout</p>
                        <p class="font-medium">{{ $appareil->timeout ?? '30s' }}</p>
                    </div>
                </div>
                <button onclick="showNetworkConfigModal('{{ $appareil->id }}')" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Modifier
                </button>
            </div>
        </div>
        
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Paramètres d'authentification</h4>
            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Nom d'utilisateur</p>
                        <p class="font-medium">{{ $appareil->username ?? '******' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Mot de passe</p>
                        <p class="font-medium">••••••</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Clé API</p>
                        <p class="font-medium">{{ substr($appareil->api_key ?? 'Non définie', 0, 8) . (strlen($appareil->api_key ?? '') > 8 ? '...' : '') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Méthode d'authentification</p>
                        <p class="font-medium">{{ $appareil->auth_method ?? 'Basic' }}</p>
                    </div>
                </div>
                <button onclick="showAuthConfigModal('{{ $appareil->id }}')" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Modifier
                </button>
            </div>
        </div>
        
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Paramètres de synchronisation</h4>
            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Fréquence</p>
                        <p class="font-medium">{{ $appareil->sync_frequency ?? 'Toutes les 30 minutes' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Dernière synchronisation</p>
                        <p class="font-medium">{{ $appareil->derniere_synchronisation ? $appareil->derniere_synchronisation->format('d/m/Y H:i') : 'Jamais' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Mode de synchronisation</p>
                        <p class="font-medium">{{ $appareil->sync_mode ?? 'Automatique' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Données à synchroniser</p>
                        <p class="font-medium">{{ $appareil->sync_data ?? 'Tout' }}</p>
                    </div>
                </div>
                <button onclick="showSyncConfigModal('{{ $appareil->id }}')" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Modifier
                </button>
            </div>
        </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between">
        <button onclick="testConnection('{{ $appareil->id }}')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Tester la connexion
        </button>
        
        <button onclick="resetDevice('{{ $appareil->id }}')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
            Réinitialiser
        </button>
    </div>
</div>
