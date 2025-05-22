@props(['appareil'])

<div class="bg-white rounded-lg shadow-md p-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques</h3>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="bg-blue-50 rounded-lg p-3 text-center">
            <p class="text-sm text-gray-500">Utilisateurs enregistrés</p>
            <p class="text-2xl font-bold text-blue-600">{{ $appareil->stats['total_users'] ?? 0 }}</p>
        </div>
        
        <div class="bg-green-50 rounded-lg p-3 text-center">
            <p class="text-sm text-gray-500">Pointages totaux</p>
            <p class="text-2xl font-bold text-green-600">{{ $appareil->stats['total_pointages'] ?? 0 }}</p>
        </div>
    </div>
    
    <div class="space-y-3">
        <div>
            <div class="flex justify-between mb-1">
                <span class="text-xs font-medium text-gray-700">Aujourd'hui</span>
                <span class="text-xs font-medium text-gray-700">{{ $appareil->stats['pointages_today'] ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, (($appareil->stats['pointages_today'] ?? 0) / max(1, ($appareil->stats['total_users'] ?? 1))) * 100) }}%"></div>
            </div>
        </div>
        
        <div>
            <div class="flex justify-between mb-1">
                <span class="text-xs font-medium text-gray-700">Cette semaine</span>
                <span class="text-xs font-medium text-gray-700">{{ $appareil->stats['pointages_week'] ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, (($appareil->stats['pointages_week'] ?? 0) / max(1, (($appareil->stats['total_users'] ?? 1) * 5))) * 100) }}%"></div>
            </div>
        </div>
        
        <div>
            <div class="flex justify-between mb-1">
                <span class="text-xs font-medium text-gray-700">Ce mois</span>
                <span class="text-xs font-medium text-gray-700">{{ $appareil->stats['pointages_month'] ?? 0 }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, (($appareil->stats['pointages_month'] ?? 0) / max(1, (($appareil->stats['total_users'] ?? 1) * 20))) * 100) }}%"></div>
            </div>
        </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Répartition des pointages</h4>
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="bg-gray-50 rounded p-2">
                <p class="text-gray-500">Entrées</p>
                <p class="font-semibold text-blue-600">{{ $appareil->stats['entrees'] ?? 0 }}</p>
            </div>
            <div class="bg-gray-50 rounded p-2">
                <p class="text-gray-500">Sorties</p>
                <p class="font-semibold text-red-600">{{ $appareil->stats['sorties'] ?? 0 }}</p>
            </div>
            <div class="bg-gray-50 rounded p-2">
                <p class="text-gray-500">Pauses</p>
                <p class="font-semibold text-yellow-600">{{ $appareil->stats['pauses'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
