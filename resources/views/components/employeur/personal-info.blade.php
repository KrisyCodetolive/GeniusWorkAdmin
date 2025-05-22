@props(['employeur'])

<div>
    <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
        <i class="fas fa-user-circle mr-2"></i>Informations personnelles
    </h3>
    
    <div class="space-y-3">
        <div>
            <span class="text-gray-600 block">Nom complet:</span>
            <span class="font-semibold">{{ $employeur->getNomComplet() }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Email:</span>
            <span class="font-semibold">{{ $employeur->email }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Téléphone:</span>
            <span class="font-semibold">{{ $employeur->telephone }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Date de naissance:</span>
            <span class="font-semibold">{{ $employeur->date_naissance ? $employeur->date_naissance->format('d/m/Y') : 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Âge:</span>
            <span class="font-semibold">{{ $employeur->date_naissance ? $employeur->date_naissance->age . ' ans' : 'Non défini' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Adresse:</span>
            <span class="font-semibold">{{ $employeur->adresse ?: 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Nationalité:</span>
            <span class="font-semibold">{{ $employeur->nationalite ?: 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">État civil:</span>
            <span class="font-semibold">{{ $employeur->etat_civil ?: 'Non défini' }}</span>
        </div>
    </div>
</div>
