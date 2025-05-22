@props([
    'columns' => [], 
    'data' => [], 
    'actions' => true, 
    'striped' => true, 
    'hover' => true, 
    'bordered' => false, 
    'compact' => false,
    'searchable' => false,
    'sortable' => false,
    'pagination' => false,
    'perPage' => 10,
    'emptyMessage' => 'Aucune donnée disponible',
    'id' => null
])

@php
    $tableId = $id ?? 'data-table-' . uniqid();
    $sizeClasses = $compact ? 'px-3 py-2 text-xs' : 'px-6 py-4 text-sm';
@endphp

<div x-data="{
    columns: @js($columns),
    originalData: @js($data),
    currentData: @js($data),
    searchTerm: '',
    sortColumn: null,
    sortDirection: 'asc',
    currentPage: 1,
    perPage: @js($perPage),
    
    init() {
        this.$watch('searchTerm', () => {
            this.filterData();
            this.currentPage = 1;
        });
        
        // Initialiser les icônes Lucide après le rendu du tableau
        this.$nextTick(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    },
    
    filterData() {
        if (this.searchTerm === '') {
            this.currentData = this.originalData;
        } else {
            const term = this.searchTerm.toLowerCase();
            this.currentData = this.originalData.filter(row => {
                return Object.values(row).some(value => {
                    if (value === null || value === undefined) return false;
                    // Convertir en chaîne et nettoyer les balises HTML
                    const cleanValue = String(value).replace(/<[^>]*>/g, '');
                    return cleanValue.toLowerCase().includes(term);
                });
            });
        }
        
        if (this.sortColumn) {
            this.sortData(this.sortColumn);
        }
    },
    
    sortData(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }
        
        this.currentData = [...this.currentData].sort((a, b) => {
            // Nettoyer les balises HTML pour le tri
            const cleanValueA = a[column] === null || a[column] === undefined ? '' : 
                String(a[column]).replace(/<[^>]*>/g, '').toLowerCase();
            const cleanValueB = b[column] === null || b[column] === undefined ? '' : 
                String(b[column]).replace(/<[^>]*>/g, '').toLowerCase();
            
            if (cleanValueA < cleanValueB) return this.sortDirection === 'asc' ? -1 : 1;
            if (cleanValueA > cleanValueB) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    },
    
    get totalPages() {
        return Math.ceil(this.currentData.length / this.perPage);
    },
    
    get paginatedData() {
        if (!@js($pagination)) return this.currentData;
        
        const start = (this.currentPage - 1) * this.perPage;
        const end = start + this.perPage;
        return this.currentData.slice(start, end);
    },
    
    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
        }
    },
    
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
        }
    },
    
    goToPage(page) {
        this.currentPage = page;
    }
}" class="bg-white rounded-lg shadow-sm overflow-hidden {{ $bordered ? 'border border-gray-200' : '' }}">
    
    @if($searchable)
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    x-model="searchTerm" 
                    placeholder="Rechercher..." 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
            </div>
        </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $key => $column)
                        <th 
                            scope="col" 
                            class="{{ $sizeClasses }} text-left font-medium text-gray-500 uppercase tracking-wider {{ $sortable ? 'cursor-pointer hover:bg-gray-100' : '' }}"
                            @if($sortable) x-on:click="sortData('{{ $key }}')" @endif
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ $column }}</span>
                                @if($sortable)
                                    <span class="inline-flex flex-col">
                                        <i 
                                            data-lucide="chevron-up" 
                                            class="h-3 w-3" 
                                            x-bind:class="sortColumn === '{{ $key }}' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                                        ></i>
                                        <i 
                                            data-lucide="chevron-down" 
                                            class="h-3 w-3 -mt-1" 
                                            x-bind:class="sortColumn === '{{ $key }}' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                                        ></i>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    
                    @if($actions)
                        <th scope="col" class="{{ $sizeClasses }} text-right font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(row, index) in paginatedData" :key="index">
                    <tr 
                        x-bind:class="{ 
                            'bg-gray-50': {{ $striped ? 'index % 2' : 'false' }},
                            'hover:bg-gray-100': {{ $hover ? 'true' : 'false' }}
                        }"
                        class="transition-colors duration-150"
                    >
                        @foreach($columns as $key => $column)
                            <td class="{{ $sizeClasses }} whitespace-nowrap text-gray-500" x-html="row['{{ $key }}'] || ''"></td>
                        @endforeach
                        
                        @if($actions)
                            <td class="{{ $sizeClasses }} whitespace-nowrap text-right font-medium">
                                <div x-data="{ row: row }" class="flex justify-end space-x-2">
                                    {{ $slot }}
                                </div>
                            </td>
                        @endif
                    </tr>
                </template>
                
                <tr x-show="paginatedData.length === 0">
                    <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="{{ $sizeClasses }} text-center text-gray-500">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    @if($pagination)
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Affichage de 
                        <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                        à 
                        <span class="font-medium" x-text="Math.min(currentPage * perPage, currentData.length)"></span>
                        sur 
                        <span class="font-medium" x-text="currentData.length"></span>
                        résultats
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button
                            type="button"
                            x-on:click="previousPage"
                            x-bind:disabled="currentPage === 1"
                            x-bind:class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                        >
                            <span class="sr-only">Précédent</span>
                            <i data-lucide="chevron-left" class="h-5 w-5"></i>
                        </button>
                        
                        <template x-for="page in totalPages" :key="page">
                            <button
                                type="button"
                                x-on:click="goToPage(page)"
                                x-bind:class="{ 'bg-blue-50 border-blue-500 text-blue-600': currentPage === page }"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                <span x-text="page"></span>
                            </button>
                        </template>
                        
                        <button
                            type="button"
                            x-on:click="nextPage"
                            x-bind:disabled="currentPage === totalPages"
                            x-bind:class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                        >
                            <span class="sr-only">Suivant</span>
                            <i data-lucide="chevron-right" class="h-5 w-5"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // S'assurer que les icônes Lucide sont initialisées
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
