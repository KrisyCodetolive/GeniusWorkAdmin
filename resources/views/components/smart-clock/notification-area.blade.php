<!-- Composant de zone de notification pour le Smart Clock -->
<div id="notification-area" class="fixed top-4 right-4 z-50 w-full max-w-sm space-y-2 pointer-events-none">
    <!-- Les notifications seront injectées ici par JavaScript -->
</div>

<style>
    /* Styles pour les animations des notifications */
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notification-enter {
        animation: slideIn 0.3s forwards;
    }
    
    .notification-exit {
        animation: slideOut 0.3s forwards;
    }
</style>
