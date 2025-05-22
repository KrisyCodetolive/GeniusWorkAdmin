/**
 * Smart Clock - Système de pointage pour GENIUS WORK
 * 
 * Ce script gère les fonctionnalités de l'horloge intelligente:
 * - Affichage de l'heure en temps réel
 * - Gestion du scanner de badges
 * - Traitement des pointages (entrée/sortie)
 * - Affichage des logs récents
 */

document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const clockElement = document.getElementById('clock');
    const dateElement = document.getElementById('date');
    const scanInput = document.getElementById('scan-input');
    const scanButton = document.getElementById('scan-button');
    const notificationArea = document.getElementById('notification-area');
    const recentLogsContainer = document.getElementById('recent-logs');
    const siteIdElement = document.getElementById('site-id');
    
    // Variables globales
    let scanning = false;
    let lastScanTime = 0;
    const SCAN_COOLDOWN = 2000; // 2 secondes de délai entre les scans
    
    // Configuration CSRF pour les requêtes Ajax
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    
    /**
     * Initialisation des fonctionnalités
     */
    function init() {
        updateClock();
        setInterval(updateClock, 1000);
        setupScannerEvents();
        fetchRecentLogs();
        setInterval(fetchRecentLogs, 30000); // Rafraîchir les logs toutes les 30 secondes
    }
    
    /**
     * Met à jour l'horloge en temps réel
     */
    function updateClock() {
        const now = new Date();
        
        // Format de l'heure: HH:MM:SS
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        if (clockElement) {
            clockElement.innerHTML = `
                <span class="hours">${hours}</span>
                <span class="colon">:</span>
                <span class="minutes">${minutes}</span>
                <span class="colon">:</span>
                <span class="seconds">${seconds}</span>
            `;
        }
        
        // Format de la date: jour de la semaine, jour mois année
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('fr-FR', options);
        
        if (dateElement) {
            dateElement.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
        }
    }
    
    /**
     * Configure les événements du scanner
     */
    function setupScannerEvents() {
        if (!scanInput || !scanButton) return;
        
        // Focus sur l'input quand on clique sur le bouton
        scanButton.addEventListener('click', function() {
            scanInput.focus();
        });
        
        // Traitement du scan quand on appuie sur Entrée
        scanInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScan(scanInput.value);
            }
        });
        
        // Auto-focus sur l'input au chargement
        setTimeout(() => {
            scanInput.focus();
        }, 500);
    }
    
    /**
     * Traite un code scanné ou saisi
     * @param {string} code - Le code scanné ou saisi
     */
    function processScan(code) {
        // Éviter les scans multiples trop rapides
        const now = Date.now();
        if (now - lastScanTime < SCAN_COOLDOWN) {
            return;
        }
        lastScanTime = now;
        
        // Vérifier que le code n'est pas vide
        if (!code || code.trim() === '') {
            showNotification('Veuillez scanner un code ou saisir un identifiant', 'warning');
            return;
        }
        
        // Récupérer l'ID du site
        const siteId = siteIdElement ? siteIdElement.value : null;
        if (!siteId) {
            showNotification('Erreur: ID du site non trouvé', 'error');
            return;
        }
        
        // Animation de scan
        startScanAnimation();
        
        // Envoyer la requête au serveur
        axios.post('/smart-clock/process-scan', {
            code: code,
            site_id: siteId
        })
        .then(response => {
            const data = response.data;
            
            if (data.success) {
                // Afficher le message de succès
                showNotification(data.message, 'success');
                
                // Rafraîchir les logs récents
                fetchRecentLogs();
                
                // Jouer un son de succès
                playSound('success');
            } else {
                // Afficher le message d'erreur
                showNotification(data.message || 'Une erreur est survenue', 'error');
                
                // Jouer un son d'erreur
                playSound('error');
            }
        })
        .catch(error => {
            console.error('Erreur lors du traitement du scan:', error);
            
            // Message d'erreur par défaut
            let errorMessage = 'Une erreur est survenue lors du traitement du scan';
            
            // Si l'erreur vient du serveur, utiliser son message
            if (error.response && error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
            
            showNotification(errorMessage, 'error');
            playSound('error');
        })
        .finally(() => {
            // Réinitialiser l'input et arrêter l'animation
            scanInput.value = '';
            stopScanAnimation();
            scanInput.focus();
        });
    }
    
    /**
     * Récupère les logs récents depuis le serveur
     */
    function fetchRecentLogs() {
        if (!recentLogsContainer) return;
        
        axios.get('/smart-clock/recent-logs')
            .then(response => {
                const logs = response.data;
                
                if (logs.length === 0) {
                    recentLogsContainer.innerHTML = `
                        <div class="text-center py-6 text-gray-500">
                            <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2">Aucun pointage récent</p>
                        </div>
                    `;
                    return;
                }
                
                // Générer le HTML pour chaque log
                let logsHtml = '';
                logs.forEach(log => {
                    logsHtml += `
                        <div class="flex items-center p-3 border-b border-blue-100/50 hover:bg-blue-50/50 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold bg-${log.color}-500">
                                    ${log.initials}
                                </div>
                            </div>
                            <div class="ml-3 flex-grow">
                                <p class="text-sm font-medium text-gray-800">${log.name}</p>
                                <div class="flex items-center">
                                    <span class="text-xs text-${log.color}-600 font-medium px-2 py-0.5 rounded-full bg-${log.color}-100 mr-2">${log.type}</span>
                                    <span class="text-xs text-gray-500">${log.time}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                recentLogsContainer.innerHTML = logsHtml;
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des logs récents:', error);
                recentLogsContainer.innerHTML = `
                    <div class="text-center py-6 text-red-500">
                        <svg class="w-10 h-10 mx-auto text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2">Erreur lors du chargement des pointages récents</p>
                    </div>
                `;
            });
    }
    
    /**
     * Affiche une notification
     * @param {string} message - Le message à afficher
     * @param {string} type - Le type de notification (success, error, warning, info)
     */
    function showNotification(message, type = 'info') {
        if (!notificationArea) return;
        
        // Définir la classe en fonction du type
        let bgClass = 'bg-blue-100 text-blue-800';
        let icon = `<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>`;
        
        switch (type) {
            case 'success':
                bgClass = 'bg-green-100 text-green-800';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>`;
                break;
            case 'error':
                bgClass = 'bg-red-100 text-red-800';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>`;
                break;
            case 'warning':
                bgClass = 'bg-yellow-100 text-yellow-800';
                icon = `<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>`;
                break;
        }
        
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `${bgClass} px-4 py-3 rounded-lg shadow-md mb-3 flex items-center transform transition-all duration-300 translate-y-0 opacity-0`;
        notification.innerHTML = `
            ${icon}
            <span>${message}</span>
        `;
        
        // Ajouter au conteneur
        notificationArea.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.classList.remove('translate-y-0', 'opacity-0');
            notification.classList.add('translate-y-0', 'opacity-100');
        }, 10);
        
        // Supprimer après un délai
        setTimeout(() => {
            notification.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
    
    /**
     * Démarre l'animation de scan
     */
    function startScanAnimation() {
        if (!scanInput) return;
        
        scanning = true;
        const scannerContainer = document.getElementById('scanner-container');
        if (scannerContainer) {
            scannerContainer.classList.add('scanning');
        }
    }
    
    /**
     * Arrête l'animation de scan
     */
    function stopScanAnimation() {
        if (!scanInput) return;
        
        scanning = false;
        const scannerContainer = document.getElementById('scanner-container');
        if (scannerContainer) {
            scannerContainer.classList.remove('scanning');
        }
    }
    
    /**
     * Joue un son
     * @param {string} type - Le type de son (success, error)
     */
    function playSound(type) {
        let sound;
        
        switch (type) {
            case 'success':
                sound = new Audio('/sounds/success.mp3');
                break;
            case 'error':
                sound = new Audio('/sounds/error.mp3');
                break;
            default:
                return;
        }
        
        sound.volume = 0.5;
        sound.play().catch(e => console.log('Erreur de lecture audio:', e));
    }
    
    // Initialiser l'application
    init();
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

        // Extraire le code de l'URL si nécessaire
        let processedCode = scannedData;
        
        // Si c'est une URL, extraire uniquement le code à la fin
        if (scannedData.includes('/')) {
            // Extraire le dernier segment de l'URL
            const urlParts = scannedData.split('/');
            processedCode = urlParts[urlParts.length - 1];
            console.log('Code extrait de l\'URL:', processedCode);
        }

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
            body: JSON.stringify({ idno: processedCode })
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

        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit'
            });
        }
        
        const secondsElement = document.getElementById('current-seconds');
        if (secondsElement) {
            secondsElement.textContent = now.getSeconds().toString().padStart(2, '0');
        }
        
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
            dateElement.textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
    
    // Charger les logs récents au démarrage
    updateRecentLogs();
    
    // Mettre à jour les logs toutes les 30 secondes
    setInterval(updateRecentLogs, 30000);

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
    function updateRecentLogs() {
        fetch('/gwork/webclock/recent-logs')
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || 'Erreur serveur');
                    });
                }
                return response.json();
            })
            .then(logs => {
                if (!Array.isArray(logs)) {
                    throw new Error('Format de données invalide');
                }
                
                const logsContainer = document.getElementById('recent-logs');
                const template = document.getElementById('log-template');
                
                // Vider le conteneur
                logsContainer.innerHTML = '';
                
                if (logs.length === 0) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'text-center text-gray-500 py-4';
                    emptyMessage.textContent = 'Aucun pointage récent';
                    logsContainer.appendChild(emptyMessage);
                    return;
                }
                
                // Ajouter chaque log
                logs.forEach(log => {
                    const logElement = template.content.cloneNode(true);
                    
                    // Remplir les données
                    logElement.querySelector('.name').textContent = log.name;
                    logElement.querySelector('.initials').textContent = log.initials;
                    logElement.querySelector('.time-ago').textContent = log.time;
                    
                    const badge = logElement.querySelector('.type-badge');
                    badge.textContent = log.type;
                    
                    // Appliquer la couleur
                    switch (log.color) {
                        case 'green':
                            badge.classList.add('bg-green-100', 'text-green-800');
                            break;
                        case 'red':
                            badge.classList.add('bg-red-100', 'text-red-800');
                            break;
                        case 'orange':
                            badge.classList.add('bg-orange-100', 'text-orange-800');
                            break;
                        case 'blue':
                            badge.classList.add('bg-blue-100', 'text-blue-800');
                            break;
                        default:
                            badge.classList.add('bg-gray-100', 'text-gray-800');
                    }
                    
                    logsContainer.appendChild(logElement);
                });
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des logs:', error);
                const logsContainer = document.getElementById('recent-logs');
                logsContainer.innerHTML = `
                    <div class="text-center text-red-500 py-4">
                        Erreur lors du chargement des pointages
                    </div>
                `;
            });
    }

    function playAudio(base64Audio) {
        try {
            const audio = new Audio("data:audio/mp3;base64," + base64Audio);
            audio.play().catch(e => console.warn('Erreur de lecture audio:', e));
        } catch (error) {
            console.error('Erreur lors de la création de l\'audio:', error);
        }
    }
})});
