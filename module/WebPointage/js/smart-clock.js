document.addEventListener('DOMContentLoaded', function() {
    var url = '{{url("/")}}';

    // Éléments du DOM
    const scannerInput = document.getElementById('scanner-input');
    const scanButton = document.getElementById('scan-button');
    const focusIndicator = document.getElementById('focus-indicator');
    const scanStatus = document.getElementById('scan-status');
    const scanLine = document.getElementById('scan-line');
    // Variables de contrôle
    let scanBuffer = '';
    let scanning = true;
    let lastKeyTime = Date.now();
    const SCAN_TIMEOUT = 50;

    // Fonction de focus avec indication visuelle
    function focusInput() {
        scannerInput.focus();
        focusIndicator.classList.add('border-blue-500/50');
        setTimeout(() => focusIndicator.classList.remove('border-blue-500/50'), 300);
    }

    // Initialisation du focus
    focusInput();

    // Maintien du focus
    document.addEventListener('click', (e) => {
        if (e.target !== scannerInput) {
            e.preventDefault();
            focusInput();
        }
    });

    // Gestion du bouton de scan
    scanButton.addEventListener('click', (e) => {
        e.preventDefault();
        focusInput();
    });

    // Gestion des entrées
    scannerInput.addEventListener('input', function(e) {
        console.log('Input event triggered:', e.target.value); // Debug
        const currentTime = Date.now();
        
        // Réinitialisation du buffer si délai dépassé
        if (currentTime - lastKeyTime > SCAN_TIMEOUT) {
            console.log('Buffer reset due to timeout'); // Debug
            scanBuffer = '';
        }
        
        lastKeyTime = currentTime;
        scanBuffer += e.target.value;
        console.log('Current buffer:', scanBuffer); // Debug
        
        // Mise à jour du statut
        scanStatus.textContent = 'Lecture en cours...';
        
        // Si le scan est complet (par exemple, se termine par un retour chariot)
        if (scanBuffer.includes('\n') || scanBuffer.includes('\r')) {
            console.log('Processing complete scan'); // Debug
            processScan(scanBuffer.trim());
            scanBuffer = '';
            e.target.value = '';
        } else {
            e.target.value = ''; // Nettoyage de l'input
        }
    });

    // Traitement à l'appui sur Entrée
    scannerInput.addEventListener('keypress', function(e) {
        console.log('Keypress event:', e.key); // Debug
        if (e.key === 'Enter') {
            e.preventDefault();
            console.log('Enter pressed, current buffer:', scanBuffer); // Debug
            if (scanBuffer.length > 0) {
                processScan(scanBuffer.trim());
                scanBuffer = '';
            }
        }
    });

    // Fonction de traitement du scan
    function processScan(scannedData) {
        if (!scanning) return;
        scanning = false;

        // Effet visuel de scan initial
        focusIndicator.classList.add('border-green-500/50');
        scanStatus.textContent = 'Traitement en cours...';
        scanLine.classList.add('scan-success');
        focusIndicator.classList.add('border-green-500/30');

        // Retour haptique
        if (navigator.vibrate) {
            navigator.vibrate([100, 50, 100]);
        }

        // Appel initial au serveur 
        fetch('/gwork/webclock/initiate-clocking', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ idno: scannedData })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || 'Erreur réseau: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                throw new Error(data.error);
            }
            
            // Redirection vers la page de transition
            window.location.href = '/gwork/smart-clock-transition?id=' + data.requestId;
        })
        .catch(error => {
            console.error('Erreur:', error);
            showError("Erreur lors du pointage: " + error.message);
            scanStatus.textContent = 'Erreur de lecture';
            
            // Réinitialisation en cas d'erreur
            setTimeout(() => {
                scanning = true;
                focusIndicator.classList.remove('border-green-500/50');
                scanStatus.textContent = 'En attente de scan...';
                scanLine.classList.remove('scan-success');
                focusIndicator.classList.remove('border-green-500/30');
                focusInput();
            }, 2000);
        });
    }

    // Mise à jour de l'horloge avec animation fluide
    function updateClock() {
        const now = new Date();
        const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        document.getElementById('current-time').textContent = now.toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit'
        });
        document.getElementById('current-seconds').textContent = now.getSeconds().toString().padStart(2, '0');
        document.getElementById('current-date').textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Affichage des notifications
    let notificationTimeout;
    function showNotification(data) {
        const notificationArea = document.getElementById('notification-area');
        const notificationIcon = document.getElementById('notification-icon');
        const notificationTitle = document.getElementById('notification-title');
        const notificationMessage = document.getElementById('notification-message');

        // Définition des styles selon le type
        let bgColor, title;
        switch(data.type) {
            case 'clockin':
                bgColor = 'bg-gradient-to-br from-green-400 to-green-500';
                title = 'Entrée enregistrée';
                break;
            case 'clockout':
                bgColor = 'bg-gradient-to-br from-blue-400 to-blue-500';
                title = 'Sortie enregistrée';
                break;
            case 'return_clockin':
                bgColor = 'bg-gradient-to-br from-purple-400 to-purple-500';
                title = 'Retour enregistré';
                break;
            default:
                bgColor = 'bg-gradient-to-br from-gray-400 to-gray-500';
                title = 'Notification';
        }

        // Mise à jour du contenu
        notificationIcon.className = `w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg ${bgColor}`;
        notificationTitle.textContent = title;
        notificationMessage.textContent = data.employee;

        // Animation d'apparition
        notificationArea.classList.remove('translate-y-[-150%]', 'opacity-0');
        notificationArea.classList.add('translate-y-0', 'opacity-100');

        // Annulation du timeout précédent si existant
        if (notificationTimeout) {
            clearTimeout(notificationTimeout);
        }

        // Nouveau timeout pour la disparition
        notificationTimeout = setTimeout(() => {
            notificationArea.classList.add('translate-y-[-150%]', 'opacity-0');
            notificationArea.classList.remove('translate-y-0', 'opacity-100');
        }, 3000);
    }

    // Affichage des erreurs
    function showError(message) {
        showNotification({
            type: 'error',
            employee: message
        });
    }

    // Mise à jour des logs
    function updateRecentLogs(data) {
        const recentLogs = document.getElementById('recent-logs');
        const logEntry = document.createElement('div');
        logEntry.className = 'bg-gray-50 rounded-xl p-4 transform transition-all duration-300 hover:bg-white hover:shadow-md';

        const typeColors = {
            'clockin': 'from-green-400 to-green-500',
            'clockout': 'from-blue-400 to-blue-500',
            'return_clockin': 'from-purple-400 to-purple-500'
        };

        logEntry.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br ${typeColors[data.type]} flex items-center justify-center text-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="${data.type === 'clockin' ? 'M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 0118 0 9 9 0 01-18 0z' : 
                                   data.type === 'clockout' ? 'M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z' :
                                   'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'}" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-800">${data.employee}</div>
                        <div class="text-sm text-gray-500">${data.time}</div>
                    </div>
                </div>
            </div>
        `;

        logEntry.style.opacity = '0';
        logEntry.style.transform = 'translateY(-10px)';
        recentLogs.insertBefore(logEntry, recentLogs.firstChild);

        requestAnimationFrame(() => {
            logEntry.style.opacity = '1';
            logEntry.style.transform = 'translateY(0)';
        });

        if (recentLogs.children.length > 5) {
            const lastChild = recentLogs.lastChild;
            lastChild.style.opacity = '0';
            lastChild.style.transform = 'translateY(10px)';
            setTimeout(() => recentLogs.removeChild(lastChild), 300);
        }
    }

    function playAudio(base64Audio) {
        try {
            const audio = new Audio("data:audio/mp3;base64," + base64Audio);
            audio.play().catch(e => console.warn('Erreur de lecture audio:', e));
        } catch (error) {
            console.error('Erreur lors de la création de l\'audio:', error);
        }
    }
});
