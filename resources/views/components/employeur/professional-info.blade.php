@props(['employeur', 'typesContrat'])

<div>
    <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
        <i class="fas fa-briefcase mr-2"></i>Informations professionnelles
    </h3>
    
    <div class="space-y-3">
        <div>
            <span class="text-gray-600 block">Entreprise:</span>
            <span class="font-semibold">{{ $employeur->entreprise->nom ?? 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Filiale:</span>
            <span class="font-semibold">{{ $employeur->filiale->nom ?? 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Département:</span>
            <span class="font-semibold">{{ $employeur->departement->nom ?? 'Non défini' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Poste:</span>
            <span class="font-semibold">{{ $employeur->poste ?: 'Non défini' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Type de contrat:</span>
            <span class="font-semibold">{{ $typesContrat[$employeur->type_contrat] ?? $employeur->type_contrat ?? 'Non défini' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Date d'embauche:</span>
            <span class="font-semibold">{{ $employeur->date_embauche ? $employeur->date_embauche->format('d/m/Y') : 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Ancienneté:</span>
            <span class="font-semibold">{{ $employeur->date_embauche ? $employeur->date_embauche->diffInYears(now()) . ' ans' : 'Non définie' }}</span>
        </div>
        
        <div>
            <span class="text-gray-600 block">Supérieur hiérarchique:</span>
            <span class="font-semibold">{{ $employeur->superieur ? $employeur->superieur->getNomComplet() : 'Non défini' }}</span>
        </div>
    </div>
</div>
