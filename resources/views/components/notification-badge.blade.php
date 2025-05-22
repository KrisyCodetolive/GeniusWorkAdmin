<div class="dropdown notification-dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        @if($count > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $count > 99 ? '99+' : $count }}
                <span class="visually-hidden">notifications non lues</span>
            </span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 500px; overflow-y: auto;">
        <li>
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Notifications</h6>
                @if($count > 0)
                    <a href="{{ route('notifications.mark-all-read') }}" class="text-decoration-none" 
                       onclick="event.preventDefault(); document.getElementById('mark-all-read-form').submit();">
                        <small>Tout marquer comme lu</small>
                    </a>
                    <form id="mark-all-read-form" action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endif
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        @forelse($notifications as $notification)
            <li>
                <a class="dropdown-item notification-item {{ $notification->estLue() ? 'notification-read' : 'notification-unread' }}" 
                   href="{{ route('notifications.show', $notification->id) }}">
                    <div class="d-flex justify-content-between">
                        <span class="notification-title">{{ ucfirst($notification->type) }}</span>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="notification-text mb-0">{{ \Illuminate\Support\Str::limit($notification->message, 100) }}</p>
                </a>
            </li>
        @empty
            <li><span class="dropdown-item">Aucune notification</span></li>
        @endforelse
        
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                Voir toutes les notifications
            </a>
        </li>
    </ul>
</div>

<style>
    .notification-dropdown .dropdown-toggle::after {
        display: none;
    }
    
    .notification-unread {
        background-color: rgba(13, 110, 253, 0.05);
        font-weight: 500;
    }
    
    .notification-title {
        font-weight: 600;
    }
    
    .notification-text {
        font-size: 0.875rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour rafraîchir le badge de notifications
        function refreshNotificationBadge() {
            fetch('{{ route("api.notifications.unread") }}')
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour le contenu du composant avec les nouvelles données
                    // Cette partie dépend de votre implémentation frontend
                });
        }
        
        // Rafraîchir les notifications toutes les minutes
        setInterval(refreshNotificationBadge, 60000);
    });
</script>
