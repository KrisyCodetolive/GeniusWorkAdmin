@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Profil</h2>
    </div>
    <div class="p-6 flex flex-col items-center">
        @if($employeur->photo)
            <img src="{{ Storage::url($employeur->photo) }}" alt="{{ $employeur->getNomComplet() }}" class="h-32 w-32 rounded-full object-cover mb-4">
        @else
            <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                <i class="fas fa-user text-gray-400 text-5xl"></i>
            </div>
        @endif
        
        <h3 class="text-xl font-semibold text-gray-800">{{ $employeur->getNomComplet() }}</h3>
        <p class="text-gray-600 mb-4">{{ $employeur->poste }}</p>
        
        <div class="w-full border-t border-gray-200 pt-4">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Code employé:</span>
                <span class="font-semibold">{{ $employeur->code_employe }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Matricule:</span>
                <span class="font-semibold">{{ $employeur->matricule ?: 'Non défini' }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Statut:</span>
                @if($employeur->statut == 'actif')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Actif
                    </span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Inactif
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
