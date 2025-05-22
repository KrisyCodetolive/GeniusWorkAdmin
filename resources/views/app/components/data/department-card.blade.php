@props(['department', 'showActions' => true])

<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
    <div class="p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ $department->nom }}</h3>
                <p class="text-sm text-gray-500">{{ $department->code }}</p>
            </div>
            
            @if($department->filiale)
                <x-ui.badge type="primary">{{ $department->filiale->nom }}</x-ui.badge>
            @endif
        </div>
        
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Employés</p>
                <p class="text-lg font-semibold">{{ $department->employeurs_count ?? count($department->employeurs) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Budget</p>
                <p class="text-lg font-semibold">{{ number_format($department->budget, 0, ',', ' ') }} €</p>
            </div>
        </div>
        
        @if($department->responsable)
            <div class="mt-4 flex items-center">
                <div class="flex-shrink-0">
                    @if($department->responsable->photo_path)
                        <img src="{{ Storage::url($department->responsable->photo_path) }}" alt="{{ $department->responsable->nom }}" class="h-8 w-8 rounded-full">
                    @else
                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-500">Responsable</p>
                    <p class="text-sm font-medium">{{ $department->responsable->nom }} {{ $department->responsable->prenom }}</p>
                </div>
            </div>
        @endif
    </div>
    
    @if($showActions)
        <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 flex justify-end space-x-2">
            <a href="{{ route('entreprise.departements.show', $department->id) }}" class="text-blue-600 hover:text-blue-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </a>
            <a href="{{ route('entreprise.departements.edit', $department->id) }}" class="text-yellow-600 hover:text-yellow-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </a>
        </div>
    @endif
</div>
