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
    const clockElement = document.getElementById('current-time');
    const secondsElement = document.getElementById('current-seconds');
    const dateElement = document.getElementById('current-date');
    const scannerInput = document.getElementById('scanner-input');
    const scanButton = document.getElementById('scan-button');
    const notificationArea = document.getElementById('notification-area');
    const recentLogsContainer = document.getElementById('recent-logs');
    const focusIndicator = document.getElementById('focus-indicator');
    const scanStatus = document.getElementById('scan-status');
    const scanLine = document.getElementById('scan-line');
    const siteIdElement = document.getElementById('site-id');
    
    // Variables globales
    let scanning = true;
    let scanBuffer = '';
    let lastKeyTime = Date.now();
    const SCAN_TIMEOUT = 50;
    
    // Configuration CSRF pour les requêtes Ajax
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    /**
     * Initialisation des fonctionnalités
     */
    function init() {
        updateClock();
        setInterval(updateClock, 1000);
        setupScannerEvents();
        updateRecentLogs();
        setInterval(updateRecentLogs, 30000); // Rafraîchir les logs toutes les 30 secondes
    }
    
    /**
     * Met à jour l'horloge en temps réel
     */
    function updateClock() {
        const now = new Date();
        const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        if (clockElement) {
            clockElement.textContent = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit'
            });
        }
        
        if (secondsElement) {
            secondsElement.textContent = now.getSeconds().toString().padStart(2, '0');
        }
        
        if (dateElement) {
            dateElement.textContent = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        }
    }
    
    /**
     * Configure les événements du scanner
     */
    function setupScannerEvents() {
        if (!scannerInput || !scanButton) return;
        
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
                } else if (e.target.value.trim() !== '') {
                    processScan(e.target.value.trim());
                    e.target.value = '';
                }
            }
        });
    }
    
    /**
     * Traite un code scanné ou saisi
     * @param {string} scannedData - Le code scanné ou saisi
     */
    function processScan(scannedData) {
        if (!scanning) return;
        scanning = false;
        
        // Vérifier que le code n'est pas vide
        if (!scannedData || scannedData.trim() === '') {
            showError('Veuillez scanner un code ou saisir un identifiant');
            scanning = true;
            return;
        }
        
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
        
        // Appel au serveur
        fetch('/gwork/webclock/process-physical', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                idno: processedCode,
                site_id: siteIdElement ? siteIdElement.value : null
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || err.error || 'Erreur réseau: ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                throw new Error(data.message || data.error);
            }
            
            // Si une redirection est spécifiée, y aller directement
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            
            // Sinon, afficher la notification de succès
            showNotification({
                type: data.data ? data.data.type : 'clockin',
                employee: data.data ? data.data.employee : 'Pointage enregistré'
            });
            
            // Mettre à jour les logs récents
            updateRecentLogs();
            
            // Réinitialisation après succès
            setTimeout(() => {
                scanning = true;
                focusIndicator.classList.remove('border-green-500/50');
                scanStatus.textContent = 'En attente de scan...';
                scanLine.classList.remove('scan-success');
                focusIndicator.classList.remove('border-green-500/30');
                scannerInput.focus();
            }, 2000);
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
                scannerInput.focus();
            }, 2000);
        });
    }
    
    /**
     * Récupère les logs récents depuis le serveur
     */
    function updateRecentLogs() {
        if (!recentLogsContainer) return;
        
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
                
                const template = document.getElementById('log-template');
                
                // Vider le conteneur
                recentLogsContainer.innerHTML = '';
                
                if (logs.length === 0) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'text-center text-gray-500 py-4';
                    emptyMessage.textContent = 'Aucun pointage récent';
                    recentLogsContainer.appendChild(emptyMessage);
                    return;
                }
                
                // Ajouter chaque log
                logs.forEach(log => {
                    if (template) {
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
                        
                        recentLogsContainer.appendChild(logElement);
                    } else {
                        // Fallback si le template n'existe pas
                        const logItem = document.createElement('div');
                        logItem.className = 'flex items-center p-3 border-b border-blue-100/50 hover:bg-blue-50/50 transition-colors';
                        logItem.innerHTML = `
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
                        `;
                        recentLogsContainer.appendChild(logItem);
                    }
                });
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des logs:', error);
                recentLogsContainer.innerHTML = `
                    <div class="text-center text-red-500 py-4">
                        Erreur lors du chargement des pointages
                    </div>
                `;
            });
    }
    
    // Affichage des notifications
    let notificationTimeout;
    function showNotification(data) {
        const notificationArea = document.getElementById('notification-area');
        const notificationIcon = document.getElementById('notification-icon');
        const notificationTitle = document.getElementById('notification-title');
        const notificationMessage = document.getElementById('notification-message');
        
        if (!notificationArea || !notificationIcon || !notificationTitle || !notificationMessage) {
            console.error('Éléments de notification non trouvés');
            return;
        }
        
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
            case 'error':
                bgColor = 'bg-gradient-to-br from-red-400 to-red-500';
                title = 'Erreur';
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
    
    // Initialiser l'application
    init();
});
