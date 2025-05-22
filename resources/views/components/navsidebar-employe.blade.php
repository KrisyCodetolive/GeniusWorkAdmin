<!-- Employé Navsidebar Component -->
<div class="navsidebar fixed inset-y-0 right-0 z-30 w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto"
    x-data="{ open: false }"
    :class="{'translate-x-0': open, 'translate-x-full': !open}"
    @keydown.escape.window="open = false">
    
    <!-- Header with close button -->
    <div class="flex items-center justify-between px-4 py-5 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Menu employé</h3>
        <button @click="open = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <i data-lucide="x" class="h-6 w-6"></i>
        </button>
    </div>
    
    <!-- Employee Profile Section -->
    <div class="p-4 border-b">
        <div class="flex items-center space-x-3">
            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                <i data-lucide="user" class="h-6 w-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? 'Employé' }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->email ?? 'employe@example.com' }}</p>
                <p class="text-xs text-indigo-600 mt-1">{{ auth()->user()->department ?? 'Département' }}</p>
            </div>
        </div>
    </div>
    
    <!-- Attendance Status Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Statut de présence</h4>
        
        @php
            // Récupérer le dernier pointage de l'utilisateur pour aujourd'hui
            $dernierPointage = null;
            $statut = 'absent'; // Par défaut: absent
            $couleurStatut = 'bg-gray-100 text-gray-600'; // Couleur par défaut
            
            if(auth()->check()) {
                // Dans un environnement réel, ceci serait remplacé par une requête au modèle Presence
                // $dernierPointage = auth()->user()->presences()->whereDate('date_heure', today())->latest()->first();
                
                // Simulation pour démonstration
                $dernierPointage = (object)[
                    'type' => 'entree',
                    'date_heure' => now()->subHours(3),
                    'site' => (object)['nom' => 'Bureau principal']
                ];
                
                if($dernierPointage) {
                    if($dernierPointage->type == 'entree') {
                        $statut = 'présent';
                        $couleurStatut = 'bg-green-100 text-green-600';
                    } elseif($dernierPointage->type == 'pause') {
                        $statut = 'en pause';
                        $couleurStatut = 'bg-amber-100 text-amber-600';
                    } elseif($dernierPointage->type == 'sortie') {
                        $statut = 'sorti';
                        $couleurStatut = 'bg-blue-100 text-blue-600';
                    }
                }
            }
        @endphp
        
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $couleurStatut }}">
                    <i data-lucide="clock" class="h-3 w-3 mr-1"></i>
                    {{ ucfirst($statut) }}
                </span>
            </div>
            <span class="text-xs text-gray-500">
                @if($dernierPointage)
                    {{ $dernierPointage->date_heure->format('H:i') }}
                @else
                    --:--
                @endif
            </span>
        </div>
        
        <div class="text-xs text-gray-600 mb-3">
            @if($dernierPointage)
                Site: {{ $dernierPointage->site->nom }}
            @else
                Aucun pointage aujourd'hui
            @endif
        </div>
        
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('pointage.entree') }}" class="flex items-center justify-center px-3 py-2 bg-indigo-50 text-indigo-600 rounded-md hover:bg-indigo-100 text-xs font-medium">
                <i data-lucide="log-in" class="h-3 w-3 mr-1"></i>
                Pointer entrée
            </a>
            <a href="{{ route('pointage.sortie') }}" class="flex items-center justify-center px-3 py-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 text-xs font-medium">
                <i data-lucide="log-out" class="h-3 w-3 mr-1"></i>
                Pointer sortie
            </a>
        </div>
    </div>
    
    <!-- Notifications Section -->
    <div class="p-4 border-b">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notifications</h4>
            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Voir tout</a>
        </div>
        
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @php
                // Simulation de notifications pour démonstration
                $notifications = [
                    (object)[
                        'id' => 1,
                        'type' => 'retard',
                        'message' => 'Retard de 15 minutes enregistré le 15/06/2023',
                        'read' => false,
                        'created_at' => now()->subDays(1)
                    ],
                    (object)[
                        'id' => 2,
                        'type' => 'validation',
                        'message' => 'Votre demande de congé a été approuvée',
                        'read' => true,
                        'created_at' => now()->subDays(3)
                    ],
                    (object)[
                        'id' => 3,
                        'type' => 'sortie_manquante',
                        'message' => 'Sortie non pointée le 10/06/2023',
                        'read' => false,
                        'created_at' => now()->subDays(5)
                    ]
                ];
            @endphp
            
            @forelse($notifications as $notification)
                <div class="flex items-start p-2 rounded-md {{ $notification->read ? 'bg-white' : 'bg-indigo-50' }}">
                    <div class="h-8 w-8 rounded-full flex-shrink-0 
                        @if($notification->type == 'retard') bg-amber-100 
                        @elseif($notification->type == 'validation') bg-green-100 
                        @elseif($notification->type == 'sortie_manquante') bg-red-100 
                        @else bg-indigo-100 @endif 
                        flex items-center justify-center mr-3">
                        <i data-lucide="{{ 
                            $notification->type == 'retard' ? 'alert-triangle' : 
                            ($notification->type == 'validation' ? 'check-circle' : 
                            ($notification->type == 'sortie_manquante' ? 'alert-circle' : 'bell')) 
                        }}" class="h-4 w-4 
                            @if($notification->type == 'retard') text-amber-600 
                            @elseif($notification->type == 'validation') text-green-600 
                            @elseif($notification->type == 'sortie_manquante') text-red-600 
                            @else text-indigo-600 @endif"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->message }}</p>
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
    
    <!-- Quick Actions Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Actions rapides</h4>
        <div class="space-y-2">
            <a href="{{ route('conges.create') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-indigo-100 flex items-center justify-center mr-3">
                    <i data-lucide="calendar" class="h-4 w-4 text-indigo-600"></i>
                </div>
                <span>Demander un congé</span>
            </a>
            
            <a href="{{ route('heures-supplementaires.create') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-indigo-100 flex items-center justify-center mr-3">
                    <i data-lucide="clock-plus" class="h-4 w-4 text-indigo-600"></i>
                </div>
                <span>Déclarer des heures supp.</span>
            </a>
            
            <a href="{{ route('pointage.historique') }}" class="flex items-center p-2 rounded-md hover:bg-indigo-50 text-sm">
                <div class="h-8 w-8 rounded-md bg-indigo-100 flex items-center justify-center mr-3">
                    <i data-lucide="history" class="h-4 w-4 text-indigo-600"></i>
                </div>
                <span>Historique de pointage</span>
            </a>
        </div>
    </div>
    
    <!-- Shortcuts Section -->
    <div class="p-4 border-b">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Raccourcis</h4>
        <div class="grid grid-cols-3 gap-2">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="layout-dashboard" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Tableau de bord</span>
            </a>
            
            <a href="{{ route('planning') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="calendar" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Planning</span>
            </a>
            
            <a href="{{ route('equipe') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="users" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Équipe</span>
            </a>
            
            <a href="{{ route('documents') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="file-text" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Documents</span>
            </a>
            
            <a href="{{ route('profil') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
                <div class="h-10 w-10 rounded-md bg-indigo-100 flex items-center justify-center mb-1">
                    <i data-lucide="user" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <span class="text-xs text-center">Profil</span>
            </a>
            
            <a href="{{ route('parametres') }}" class="flex flex-col items-center p-2 rounded-md hover:bg-indigo-50">
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

<!-- Toggle Button for Employe Navsidebar -->
<button 
    @click="$el.previousElementSibling.__x.$data.open = !$el.previousElementSibling.__x.$data.open" 
    class="fixed bottom-6 right-6 h-14 w-14 rounded-full shadow-lg bg-indigo-600 text-white flex items-center justify-center focus:outline-none hover:bg-indigo-700 z-30"
>
    <i data-lucide="menu" class="h-6 w-6"></i>
</button>
