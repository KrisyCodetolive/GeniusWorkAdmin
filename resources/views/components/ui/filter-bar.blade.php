@props([
    'route' => '#',
    'method' => 'GET',
    'filters' => [],
    'resetButton' => true,
    'applyButton' => true,
    'collapseByDefault' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden']) }} x-data="{ collapsed: {{ $collapseByDefault ? 'true' : 'false' }} }">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-800 flex items-center">
            <i data-lucide="filter" class="h-5 w-5 mr-2 text-blue-600"></i>
            Filtres de recherche
        </h3>
        <button @click="collapsed = !collapsed" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i data-lucide="chevron-down" x-show="!collapsed" class="h-5 w-5 transition-transform"></i>
            <i data-lucide="chevron-up" x-show="collapsed" class="h-5 w-5 transition-transform" style="display: none;"></i>
        </button>
    </div>
    
    <div class="p-5" x-show="!collapsed" style="display: block;">
        <form action="{{ $route }}" method="{{ $method }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                {{ $slot }}
            </div>
            
            @if($resetButton || $applyButton)
                <div class="flex justify-end mt-6 space-x-3">
                    @if($resetButton)
                        <button type="reset" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors duration-200 text-sm font-medium flex items-center">
                            <i data-lucide="refresh-cw" class="h-4 w-4 mr-1.5"></i> Réinitialiser
                        </button>
                    @endif
                    
                    @if($applyButton)
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium flex items-center">
                            <i data-lucide="filter" class="h-4 w-4 mr-1.5"></i> Appliquer les filtres
                        </button>
                    @endif
                </div>
            @endif
        </form>
    </div>
</div>
