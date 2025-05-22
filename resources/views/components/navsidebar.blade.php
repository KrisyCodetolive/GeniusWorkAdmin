<!-- Navsidebar Component -->
<div class="navsidebar fixed inset-y-0 right-0 z-30 w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto"
    x-data="{ open: false }"
    :class="{'translate-x-0': open, 'translate-x-full': !open}"
    @keydown.escape.window="open = false">
    
    <!-- Header with close button -->
    <div class="flex items-center justify-between px-4 py-5 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Menu rapide</h3>
        <button @click="open = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i data-lucide="x" class="h-6 w-6"></i>
        </button>
    </div>
    
    <!-- User Profile Section -->
    <div class="p-4 border-b">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                <i data-lucide="user" class="h-6 w-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? 'Utilisateur' }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->email ?? 'email@example.com' }}</p>
                <p class="text-xs text-indigo-600 mt-1">
                    @if(auth()->user()->hasRole('admin'))
                        Administrateur
                    @elseif(auth()->user()->hasRole('manager'))
                        Manager
                    @elseif(auth()->user()->hasRole('employe'))
                        Employé
                    @elseif(auth()->user()->hasRole('superadmin'))
                        Super Administrateur
                    @else
                        Utilisateur
                    @endif
                </p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Actions rapides</h4>
        <div class="space-y-2">
            <a href="{{ route('employe.pointage.form') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-indigo-100 flex items-center justify-center mr-3">
                    <i data-lucide="log-in" class="h-4 w-4 text-indigo-600"></i>
                </div>
                <span>Pointer maintenant</span>
            </a>
            
            <a href="{{ route('employe.conges.create') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-green-100 flex items-center justify-center mr-3">
                    <i data-lucide="calendar-plus" class="h-4 w-4 text-green-600"></i>
                </div>
                <span>Demander un congé</span>
            </a>
            
            <a href="{{ route('employe.permutations.create') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-amber-100 flex items-center justify-center mr-3">
                    <i data-lucide="repeat" class="h-4 w-4 text-amber-600"></i>
                </div>
                <span>Permutation d'horaire</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Notifications Section -->
    <div class="p-4 border-b">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notifications récentes</h4>
            <a href="{{ route('employe.notifications') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Voir tout</a>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse(auth()->user()->notifications()->latest()->take(3)->get() as $notification)
                <div class="flex items-start p-2 rounded-md {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                    <div class="h-8 w-8 rounded-full flex-shrink-0 
                        @if($notification->data['type'] ?? '' == 'success') bg-green-100 
                        @elseif($notification->data['type'] ?? '' == 'warning') bg-amber-100 
                        @elseif($notification->data['type'] ?? '' == 'danger') bg-red-100 
                        @else bg-indigo-100 @endif 
                        flex items-center justify-center mr-3">
                        <i data-lucide="{{ $notification->data['icon'] ?? 'bell' }}" class="h-4 w-4 
                            @if($notification->data['type'] ?? '' == 'success') text-green-600 
                            @elseif($notification->data['type'] ?? '' == 'warning') text-amber-600 
                            @elseif($notification->data['type'] ?? '' == 'danger') text-red-600 
                            @else text-indigo-600 @endif"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500 text-center py-3">
                    Aucune notification récente
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Shortcuts Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Raccourcis</h4>
        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('employe.dashboard') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="layout-dashboard" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Tableau de bord</span>
            </a>
            
            <a href="{{ route('employe.presences') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="clock" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Pointages</span>
            </a>
            
            <a href="{{ route('employe.horaires') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="calendar" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Planning</span>
            </a>
            
            <a href="{{ route('employe.conges') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="calendar-off" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Congés</span>
            </a>
            
            <a href="{{ route('employe.profil') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="user" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Profil</span>
            </a>
            
            <a href="{{ route('employe.parametres') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="settings" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Paramètres</span>
            </a>
        </div>
    </div>
    
    <!-- Footer with Logout -->
    <div class="p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i data-lucide="log-out" class="h-4 w-4 mr-2"></i>
                Déconnexion
            </button>
        </form>
    </div>
</div>

<!-- Toggle Button for Navsidebar -->
<button 
    @click="$el.previousElementSibling.__x.$data.open = !$el.previousElementSibling.__x.$data.open" 
    class="fixed bottom-6 right-6 h-14 w-14 rounded-full shadow-lg bg-indigo-600 text-white flex items-center justify-center focus:outline-none hover:bg-indigo-700 z-30"
>
    <i data-lucide="menu" class="h-6 w-6"></i>
</button>
