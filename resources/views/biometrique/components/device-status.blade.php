@props(['appareil'])

<div class="bg-white rounded-lg shadow-md p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">État de l'appareil</h3>
        <span id="last-updated-{{ $appareil->id }}" class="text-xs text-gray-500">Mis à jour il y a {{ rand(1, 5) }} min</span>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center">
                <div id="connection-status-{{ $appareil->id }}" class="h-3 w-3 rounded-full {{ $appareil->statut === 'actif' ? 'bg-green-500' : 'bg-red-500' }} mr-2"></div>
                <span class="text-sm font-medium">Connexion</span>
            </div>
            <p id="connection-message-{{ $appareil->id }}" class="mt-1 text-xs text-gray-500">
                {{ $appareil->statut === 'actif' ? 'Connecté' : 'Déconnecté' }}
            </p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center">
                <div id="memory-status-{{ $appareil->id }}" class="h-3 w-3 rounded-full {{ rand(0, 1) ? 'bg-green-500' : 'bg-yellow-500' }} mr-2"></div>
                <span class="text-sm font-medium">Mémoire</span>
            </div>
            <p id="memory-usage-{{ $appareil->id }}" class="mt-1 text-xs text-gray-500">
                Utilisation: {{ rand(30, 90) }}%
            </p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center">
                <div id="battery-status-{{ $appareil->id }}" class="h-3 w-3 rounded-full {{ rand(0, 2) == 0 ? 'bg-red-500' : (rand(0, 1) ? 'bg-yellow-500' : 'bg-green-500') }} mr-2"></div>
                <span class="text-sm font-medium">Batterie</span>
            </div>
            <p id="battery-level-{{ $appareil->id }}" class="mt-1 text-xs text-gray-500">
                Niveau: {{ rand(10, 100) }}%
            </p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center">
                <div id="sync-status-{{ $appareil->id }}" class="h-3 w-3 rounded-full {{ $appareil->derniere_synchronisation && $appareil->derniere_synchronisation->diffInHours() < 24 ? 'bg-green-500' : 'bg-yellow-500' }} mr-2"></div>
                <span class="text-sm font-medium">Synchronisation</span>
            </div>
            <p id="sync-time-{{ $appareil->id }}" class="mt-1 text-xs text-gray-500">
                Dernière: {{ $appareil->derniere_synchronisation ? $appareil->derniere_synchronisation->format('d/m/Y H:i') : 'Jamais' }}
            </p>
        </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Journaux récents</h4>
        <div id="recent-logs-{{ $appareil->id }}" class="text-xs text-gray-600 space-y-1 max-h-32 overflow-y-auto">
            <p class="py-1 px-2 bg-gray-50 rounded">{{ now()->subMinutes(rand(1, 10))->format('H:i:s') }} - Utilisateur identifié (ID: {{ rand(1000, 9999) }})</p>
            <p class="py-1 px-2 bg-gray-50 rounded">{{ now()->subMinutes(rand(11, 20))->format('H:i:s') }} - Synchronisation des données</p>
            <p class="py-1 px-2 bg-gray-50 rounded">{{ now()->subMinutes(rand(21, 30))->format('H:i:s') }} - Mise à jour de la configuration</p>
            <p class="py-1 px-2 bg-gray-50 rounded">{{ now()->subMinutes(rand(31, 40))->format('H:i:s') }} - Utilisateur identifié (ID: {{ rand(1000, 9999) }})</p>
        </div>
    </div>
</div>
