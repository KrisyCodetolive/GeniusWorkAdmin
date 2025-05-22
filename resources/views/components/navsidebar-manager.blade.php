<!-- Manager Navsidebar Component -->
<div class="navsidebar fixed inset-y-0 right-0 z-30 w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto"
    x-data="{ open: false }"
    :class="{'translate-x-0': open, 'translate-x-full': !open}"
    @keydown.escape.window="open = false">
    
    <!-- Header with close button -->
    <div class="flex items-center justify-between px-4 py-5 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Menu manager</h3>
        <button @click="open = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i data-lucide="x" class="h-6 w-6"></i>
        </button>
    </div>
    
    <!-- Manager Profile Section -->
    <div class="p-4 border-b">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i data-lucide="briefcase" class="h-6 w-6 text-blue-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? 'Manager' }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->email ?? 'manager@example.com' }}</p>
                <p class="text-xs text-blue-600 mt-1">Manager - {{ auth()->user()->departement->nom ?? 'Département' }}</p>
            </div>
        </div>
    </div>
    
    <!-- Team Status Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Statut de l'équipe</h4>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Présents aujourd'hui:</span>
                <span class="text-sm font-medium text-gray-900">{{ $presentCount ?? '0' }}/{{ $teamCount ?? '0' }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($presentCount ?? 0) / max(($teamCount ?? 1), 1) * 100 }}%"></div>
            </div>
            
            <div class="flex items-center justify-between mt-2">
                <span class="text-sm text-gray-600">En congé:</span>
                <span class="text-sm font-medium text-gray-900">{{ $onLeaveCount ?? '0' }}</span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">En retard:</span>
                <span class="text-sm font-medium text-gray-900">{{ $lateCount ?? '0' }}</span>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Actions rapides</h4>
        <div class="space-y-2">
            <a href="{{ route('manager.presences.validation') }}" class="flex items-center p-2 rounded-md hover:bg-blue-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                    <i data-lucide="check-circle" class="h-4 w-4 text-blue-600"></i>
                </div>
                <span>Valider les pointages</span>
            </a>
            
            <a href="{{ route('manager.conges.validation') }}" class="flex items-center p-2 rounded-md hover:bg-blue-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                    <i data-lucide="calendar-check" class="h-4 w-4 text-blue-600"></i>
                </div>
                <span>Valider les congés</span>
                @if(isset($pendingLeaveRequests) && $pendingLeaveRequests > 0)
                    <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ $pendingLeaveRequests }}
                    </span>
                @endif
            </a>
            
            <a href="{{ route('manager.heures-supplementaires.validation') }}" class="flex items-center p-2 rounded-md hover:bg-blue-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-blue-100 flex items-center justify-center mr-3">
                    <i data-lucide="clock-plus" class="h-4 w-4 text-blue-600"></i>
                </div>
                <span>Valider heures supp.</span>
                @if(isset($pendingOvertimeRequests) && $pendingOvertimeRequests > 0)
                    <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ $pendingOvertimeRequests }}
                    </span>
                @endif
            </a>
        </div>
    </div>
    
    <!-- Recent Notifications Section -->
    <div class="p-4 border-b">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notifications récentes</h4>
            <a href="{{ route('manager.notifications') }}" class="text-xs text-blue-600 hover:text-blue-800">Voir tout</a>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse(auth()->user()->notifications()->latest()->take(3)->get() as $notification)
                <div class="flex items-start p-2 rounded-md {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                    <div class="h-8 w-8 rounded-full flex-shrink-0 
                        @if($notification->data['type'] ?? '' == 'success') bg-green-100 
                        @elseif($notification->data['type'] ?? '' == 'warning') bg-amber-100 
                        @elseif($notification->data['type'] ?? '' == 'danger') bg-red-100 
                        @else bg-blue-100 @endif 
                        flex items-center justify-center mr-3">
                        <i data-lucide="{{ $notification->data['icon'] ?? 'bell' }}" class="h-4 w-4 
                            @if($notification->data['type'] ?? '' == 'success') text-green-600 
                            @elseif($notification->data['type'] ?? '' == 'warning') text-amber-600 
                            @elseif($notification->data['type'] ?? '' == 'danger') text-red-600 
                            @else text-blue-600 @endif"></i>
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
    
    <!-- Manager Shortcuts Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Raccourcis</h4>
        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('manager.dashboard') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="layout-dashboard" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Tableau de bord</span>
            </a>
            
            <a href="{{ route('manager.equipe.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="users" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Équipe</span>
            </a>
            
            <a href="{{ route('manager.presences.equipe') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="clock" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Pointages</span>
            </a>
            
            <a href="{{ route('manager.horaires.index') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="calendar" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Planning</span>
            </a>
            
            <a href="{{ route('manager.rapports.presences') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="bar-chart-2" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Rapports</span>
            </a>
            
            <a href="{{ route('manager.profil') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-blue-50">
                <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mb-1">
                    <i data-lucide="user" class="h-5 w-5 text-blue-600"></i>
                </div>
                <span class="text-xs text-center">Profil</span>
            </a>
        </div>
    </div>
    
    <!-- Footer with Logout -->
    <div class="p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i data-lucide="log-out" class="h-4 w-4 mr-2"></i>
                Déconnexion
            </button>
        </form>
    </div>
</div>

<!-- Toggle Button for Manager Navsidebar -->
<button 
    @click="$el.previousElementSibling.__x.$data.open = !$el.previousElementSibling.__x.$data.open" 
    class="fixed bottom-6 right-6 h-14 w-14 rounded-full shadow-lg bg-blue-600 text-white flex items-center justify-center focus:outline-none hover:bg-blue-700 z-30"
>
    <i data-lucide="menu" class="h-6 w-6"></i>
</button>
