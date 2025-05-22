@props(['title' => 'Aucun élément trouvé', 'message' => 'Aucun élément ne correspond à vos critères de recherche.', 'icon' => 'search', 'action' => null, 'actionText' => null, 'actionUrl' => null])

<div class="text-center py-12 px-4">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-6">
        <i data-lucide="{{ $icon }}" class="h-8 w-8"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
    <p class="mt-2 text-sm text-gray-500">{{ $message }}</p>
    
    @if($action && $actionText && $actionUrl)
        <div class="mt-6">
            <a href="{{ $actionUrl }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ $actionText }}
            </a>
        </div>
    @endif
    
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
