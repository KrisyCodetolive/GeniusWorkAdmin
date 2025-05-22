<!-- Superadmin Navsidebar Component -->
<div class="navsidebar fixed inset-y-0 right-0 z-30 w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto"
    x-data="{ open: false }"
    :class="{'translate-x-0': open, 'translate-x-full': !open}"
    @keydown.escape.window="open = false">
    
    <!-- Header with close button -->
    <div class="flex items-center justify-between px-4 py-5 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Menu superadmin</h3>
        <button @click="open = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i data-lucide="x" class="h-6 w-6"></i>
        </button>
    </div>
    
    <!-- Superadmin Profile Section -->
    <div class="p-4 border-b">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                <i data-lucide="shield" class="h-6 w-6 text-red-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? 'Superadmin' }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->email ?? 'superadmin@example.com' }}</p>
                <p class="text-xs text-red-600 mt-1">Administrateur système</p>
            </div>
        </div>
    </div>
    
    <!-- System Status Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Statut du système</h4>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Entreprises actives:</span>
                <span class="text-sm font-medium text-gray-900">{{ $activeCompanies ?? '0' }}</span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Utilisateurs actifs:</span>
                <span class="text-sm font-medium text-gray-900">{{ $activeUsers ?? '0' }}</span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Utilisation serveur:</span>
                <span class="text-sm font-medium text-gray-900">{{ $serverUsage ?? '0' }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $serverUsage ?? 0 }}%"></div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Actions rapides</h4>
        <div class="space-y-2">
            <a href="{{ route('superadmin.entreprises.create') }}" class="flex items-center p-2 rounded-md hover:bg-red-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-red-100 flex items-center justify-center mr-3">
                    <i data-lucide="building-plus" class="h-4 w-4 text-red-600"></i>
                </div>
                <span>Ajouter une entreprise</span>
            </a>
            
            <a href="{{ route('superadmin.utilisateurs.create') }}" class="flex items-center p-2 rounded-md hover:bg-red-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-red-100 flex items-center justify-center mr-3">
                    <i data-lucide="user-plus" class="h-4 w-4 text-red-600"></i>
                </div>
                <span>Ajouter un utilisateur</span>
            </a>
            
            <a href="{{ route('superadmin.sauvegardes.create') }}" class="flex items-center p-2 rounded-md hover:bg-red-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-red-100 flex items-center justify-center mr-3">
                    <i data-lucide="database" class="h-4 w-4 text-red-600"></i>
                </div>
                <span>Créer une sauvegarde</span>
            </a>
        </div>
    </div>
    
    <!-- Recent System Alerts Section -->
    <div class="p-4 border-b">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Alertes système</h4>
            <a href="{{ route('superadmin.logs.index') }}" class="text-xs text-red-600 hover:text-red-800">Voir tout</a>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($systemAlerts ?? [] as $alert)
                <div class="flex items-start p-2 rounded-md {{ $alert->read ? 'bg-white' : 'bg-red-50' }}">
                    <div class="h-8 w-8 rounded-full flex-shrink-0 
                        @if($alert->level == 'info') bg-blue-100 
                        @elseif($alert->level == 'warning') bg-amber-100 
                        @elseif($alert->level == 'error') bg-red-100 
                        @else bg-gray-100 @endif 
                        flex items-center justify-center mr-3">
                        <i data-lucide="{{ $alert->icon ?? 'alert-triangle' }}" class="h-4 w-4 
                            @if($alert->level == 'info') text-blue-600 
                            @elseif($alert->level == 'warning') text-amber-600 
                            @elseif($alert->level == 'error') text-red-600 
                            @else text-gray-600 @endif"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $alert->title }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500 text-center py-3">
                    Aucune alerte système récente
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Superadmin Shortcuts Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Raccourcis</h4>
        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('superadmin.dashboard') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="layout-dashboard" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Tableau de bord</span>
            </a>
            
            <a href="{{ route('superadmin.entreprises.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="building" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Entreprises</span>
            </a>
            
            <a href="{{ route('superadmin.utilisateurs.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="users" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Utilisateurs</span>
            </a>
            
            <a href="{{ route('superadmin.abonnements.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="credit-card" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Abonnements</span>
            </a>
            
            <a href="{{ route('superadmin.statistiques.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="bar-chart-2" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Statistiques</span>
            </a>
            
            <a href="{{ route('superadmin.parametres.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-red-50">
                <div class="h-10 w-10 rounded-md bg-red-100 flex items-center justify-center mb-1">
                    <i data-lucide="settings" class="h-5 w-5 text-red-600"></i>
                </div>
                <span class="text-xs text-center">Paramètres</span>
            </a>
        </div>
    </div>
    
    <!-- Footer with Logout -->
    <div class="p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i data-lucide="log-out" class="h-4 w-4 mr-2"></i>
                Déconnexion
            </button>
        </form>
    </div>
</div>

<!-- Toggle Button for Superadmin Navsidebar -->
<button 
    @click="$el.previousElementSibling.__x.$data.open = !$el.previousElementSibling.__x.$data.open" 
    class="fixed bottom-6 right-6 h-14 w-14 rounded-full shadow-lg bg-red-600 text-white flex items-center justify-center focus:outline-none hover:bg-red-700 z-30"
>
    <i data-lucide="menu" class="h-6 w-6"></i>
</button>
