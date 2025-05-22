@props(['sites' => []])

<div class="bg-white shadow-md rounded-lg p-4 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filtres</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="search-filter" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
            <div class="relative">
                <input type="text" id="search-filter" placeholder="Nom, modèle, IP..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div>
            <label for="site-filter" class="block text-sm font-medium text-gray-700 mb-1">Site</label>
            <select id="site-filter" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <option value="">Tous les sites</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->nom }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select id="status-filter" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <option value="">Tous les statuts</option>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
                <option value="maintenance">Maintenance</option>
                <option value="erreur">Erreur</option>
            </select>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label for="fabricant-filter" class="block text-sm font-medium text-gray-700 mb-1">Fabricant</label>
            <select id="fabricant-filter" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <option value="">Tous les fabricants</option>
                <option value="hikvision">HikVision</option>
                <option value="anviz">Anviz</option>
                <option value="zkteco">ZKTeco</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        
        <div>
            <label for="sync-filter" class="block text-sm font-medium text-gray-700 mb-1">Dernière synchronisation</label>
            <select id="sync-filter" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <option value="">Toutes les périodes</option>
                <option value="1">Dernière heure</option>
                <option value="24">Dernières 24 heures</option>
                <option value="48">Dernières 48 heures</option>
                <option value="168">Dernière semaine</option>
                <option value="never">Jamais synchronisé</option>
            </select>
        </div>
        
        <div>
            <label for="sort-filter" class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
            <select id="sort-filter" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                <option value="nom_asc">Nom (A-Z)</option>
                <option value="nom_desc">Nom (Z-A)</option>
                <option value="derniere_sync_desc">Synchronisation (récent)</option>
                <option value="derniere_sync_asc">Synchronisation (ancien)</option>
                <option value="nb_utilisateurs_desc">Utilisateurs (plus)</option>
                <option value="nb_utilisateurs_asc">Utilisateurs (moins)</option>
            </select>
        </div>
    </div>
    
    <div class="flex justify-end mt-4">
        <button type="button" id="reset-filters" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Réinitialiser les filtres
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchFilter = document.getElementById('search-filter');
    const siteFilter = document.getElementById('site-filter');
    const statusFilter = document.getElementById('status-filter');
    const fabricantFilter = document.getElementById('fabricant-filter');
    const syncFilter = document.getElementById('sync-filter');
    const sortFilter = document.getElementById('sort-filter');
    const resetButton = document.getElementById('reset-filters');
    
    // Apply filters from URL parameters on page load
    function applyFiltersFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('search')) searchFilter.value = urlParams.get('search');
        if (urlParams.has('site')) siteFilter.value = urlParams.get('site');
        if (urlParams.has('status')) statusFilter.value = urlParams.get('status');
        if (urlParams.has('fabricant')) fabricantFilter.value = urlParams.get('fabricant');
        if (urlParams.has('sync')) syncFilter.value = urlParams.get('sync');
        if (urlParams.has('sort')) sortFilter.value = urlParams.get('sort');
    }
    
    // Update URL with current filter values
    function updateUrlWithFilters() {
        const urlParams = new URLSearchParams();
        
        if (searchFilter.value) urlParams.set('search', searchFilter.value);
        if (siteFilter.value) urlParams.set('site', siteFilter.value);
        if (statusFilter.value) urlParams.set('status', statusFilter.value);
        if (fabricantFilter.value) urlParams.set('fabricant', fabricantFilter.value);
        if (syncFilter.value) urlParams.set('sync', syncFilter.value);
        if (sortFilter.value) urlParams.set('sort', sortFilter.value);
        
        // Preserve pagination if present
        const currentParams = new URLSearchParams(window.location.search);
        if (currentParams.has('page')) urlParams.set('page', currentParams.get('page'));
        
        // Update URL without reloading page
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.pushState({ path: newUrl }, '', newUrl);
        
        // Trigger filter event for external components
        const filterEvent = new CustomEvent('deviceFiltersChanged', {
            detail: {
                search: searchFilter.value,
                site: siteFilter.value,
                status: statusFilter.value,
                fabricant: fabricantFilter.value,
                sync: syncFilter.value,
                sort: sortFilter.value
            }
        });
        document.dispatchEvent(filterEvent);
    }
    
    // Reset all filters
    function resetFilters() {
        searchFilter.value = '';
        siteFilter.value = '';
        statusFilter.value = '';
        fabricantFilter.value = '';
        syncFilter.value = '';
        sortFilter.value = 'nom_asc';
        
        updateUrlWithFilters();
    }
    
    // Add event listeners
    searchFilter.addEventListener('input', debounce(updateUrlWithFilters, 500));
    siteFilter.addEventListener('change', updateUrlWithFilters);
    statusFilter.addEventListener('change', updateUrlWithFilters);
    fabricantFilter.addEventListener('change', updateUrlWithFilters);
    syncFilter.addEventListener('change', updateUrlWithFilters);
    sortFilter.addEventListener('change', updateUrlWithFilters);
    resetButton.addEventListener('click', resetFilters);
    
    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // Initialize filters from URL
    applyFiltersFromUrl();
});
</script>
