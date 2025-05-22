@props(['employee', 'showActions' => true])

<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
    <div class="p-4 flex items-center space-x-4">
        <div class="flex-shrink-0">
            @if($employee->photo_path)
                <img src="{{ Storage::url($employee->photo_path) }}" alt="{{ $employee->nom }} {{ $employee->prenom }}" class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-gray-900 truncate">{{ $employee->nom }} {{ $employee->prenom }}</h2>
            <p class="text-sm text-gray-500">{{ $employee->poste ?? 'Non défini' }}</p>
            <div class="flex items-center mt-1">
                @if($employee->departement)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $employee->departement->nom }}
                    </span>
                @endif
                @if($employee->user)
                    <span class="inline-flex items-center ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Compte utilisateur
                    </span>
                @endif
            </div>
        </div>
        @if($showActions)
            <div class="flex-shrink-0 flex space-x-2">
                <a href="{{ route('entreprise.employe.show', $employee->id) }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                <a href="{{ route('entreprise.employe.edit', $employee->id) }}" class="text-yellow-600 hover:text-yellow-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
            </div>
        @endif
    </div>
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            {{ $slot }}
        </div>
    @endif
</div>
