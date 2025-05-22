@props(['appareil'])

<div class="bg-white rounded-lg shadow-md p-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions rapides</h3>
    
    <div class="grid grid-cols-2 gap-3">
        <button onclick="synchronizeDevice('{{ $appareil->id }}')" class="flex flex-col items-center justify-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Synchroniser</span>
            <span class="text-xs text-gray-500 mt-1">Données et utilisateurs</span>
        </button>
        
        <button onclick="testConnection('{{ $appareil->id }}')" class="flex flex-col items-center justify-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Tester</span>
            <span class="text-xs text-gray-500 mt-1">Vérifier la connexion</span>
        </button>
        
        <button onclick="restartDevice('{{ $appareil->id }}')" class="flex flex-col items-center justify-center p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Redémarrer</span>
            <span class="text-xs text-gray-500 mt-1">Redémarrer l'appareil</span>
        </button>
        
        <button onclick="showBackupModal('{{ $appareil->id }}')" class="flex flex-col items-center justify-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Sauvegarder</span>
            <span class="text-xs text-gray-500 mt-1">Données et configuration</span>
        </button>
    </div>
    
    <div class="mt-4 pt-3 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-3">
            <button onclick="showFirmwareModal('{{ $appareil->id }}')" class="flex items-center justify-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Mise à jour firmware</span>
            </button>
            
            <button onclick="showFactoryResetModal('{{ $appareil->id }}')" class="flex items-center justify-center p-2 bg-red-50 rounded-lg hover:bg-red-100 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Réinitialisation usine</span>
            </button>
        </div>
    </div>
</div>
