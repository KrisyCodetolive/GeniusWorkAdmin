@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-sitemap mr-2"></i>Structure Organisationnelle
        </h2>
    </div>
    <div class="p-6">
        <div class="flex flex-col space-y-4">
            <!-- Hiérarchie ascendante -->
            @if($employeur->superieur)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Supérieur hiérarchique</h3>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($employeur->superieur->photo)
                                <img src="{{ Storage::url($employeur->superieur->photo) }}" alt="{{ $employeur->superieur->getNomComplet() }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-user text-blue-500"></i>
                                </div>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">{{ $employeur->superieur->getNomComplet() }}</p>
                            <p class="text-xs text-gray-500">{{ $employeur->superieur->poste }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Département -->
            <div class="bg-gray-50 p-3 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Département</h3>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $employeur->departement->nom ?? 'Non défini' }}</p>
                        <p class="text-xs text-gray-500">{{ $employeur->departement->code ?? '' }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $employeur->departement->employes_count ?? '0' }} employés</span>
                </div>
            </div>
            
            <!-- Subordonnés -->
            @if(isset($employeur->subordonnes) && count($employeur->subordonnes) > 0)
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Subordonnés ({{ count($employeur->subordonnes) }})</h3>
                    <ul class="space-y-2">
                        @foreach($employeur->subordonnes->take(3) as $subordonne)
                            <li class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($subordonne->photo)
                                        <img src="{{ Storage::url($subordonne->photo) }}" alt="{{ $subordonne->getNomComplet() }}" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400 text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-800">{{ $subordonne->getNomComplet() }}</p>
                                    <p class="text-xs text-gray-500">{{ $subordonne->poste }}</p>
                                </div>
                            </li>
                        @endforeach
                        
                        @if(count($employeur->subordonnes) > 3)
                            <li class="text-center text-sm text-blue-600 hover:text-blue-800 mt-2">
                                <a href="{{ route('admin.employeurs.organigramme', ['focus' => $employeur->id]) }}">
                                    Voir tous les subordonnés ({{ count($employeur->subordonnes) }})
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
        
        <div class="mt-6">
            <a href="{{ route('admin.employeurs.organigramme', ['focus' => $employeur->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                <i class="fas fa-sitemap mr-1"></i> Voir l'organigramme complet
                <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
    </div>
</div>
