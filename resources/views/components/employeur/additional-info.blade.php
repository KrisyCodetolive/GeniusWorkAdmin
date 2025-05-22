@props(['employeur'])

<div class="mt-6 pt-6 border-t border-gray-200">
    <h3 class="text-md font-semibold text-gray-800 mb-4">
        <i class="fas fa-info-circle mr-2"></i>Informations supplémentaires
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <span class="text-gray-600 block">Compte utilisateur:</span>
            <span class="font-semibold">
                @if($employeur->user_id)
                    <span class="text-green-600">
                        <i class="fas fa-check-circle mr-1"></i> Actif
                    </span>
                @else
                    <span class="text-red-600">
                        <i class="fas fa-times-circle mr-1"></i> Non créé
                    </span>
                @endif
            </span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Dernière modification:</span>
            <span class="font-semibold">{{ $employeur->updated_at->format('d/m/Y H:i') }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Date de création:</span>
            <span class="font-semibold">{{ $employeur->created_at->format('d/m/Y H:i') }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Dernière connexion:</span>
            <span class="font-semibold">{{ $employeur->user && $employeur->user->last_login_at ? $employeur->user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</span>
        </div>
    </div>
    
    @if($employeur->config && count(json_decode($employeur->config, true)) > 0)
        <div class="mt-4">
            <span class="text-gray-600 block mb-2">Configuration:</span>
            <div class="bg-gray-100 p-3 rounded">
                <pre class="text-xs overflow-auto">{{ json_encode(json_decode($employeur->config), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif
</div>
