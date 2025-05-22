<!-- Zone des derniers pointages -->
<div class="flex flex-col bg-white/50 rounded-3xl p-4 lg:p-6 backdrop-blur-sm h-full">
    <div class="flex items-center mb-3 lg:mb-4">
        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-blue-500/10 flex items-center justify-center mr-3">
            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-xl lg:text-2xl font-medium bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            Derniers pointages
        </h3>
    </div>

    <!-- Liste des pointages -->
    <div id="recent-logs" class="space-y-2 flex-1 overflow-y-auto custom-scrollbar pr-1">
        <!-- Les pointages seront injectés ici via JavaScript -->
    </div>
</div>

<!-- Template pour un pointage -->
<template id="log-template">
    <div class="bg-white/70 rounded-xl p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
                    <span class="text-blue-600 font-medium initials"></span>
                </div>
                <div>
                    <div class="font-medium text-gray-800 text-sm lg:text-base name"></div>
                    <div class="text-xs lg:text-sm text-gray-500 time-ago"></div>
                </div>
            </div>
            <span class="px-2 lg:px-3 py-1 rounded-full text-xs lg:text-sm type-badge"></span>
        </div>
    </div>
</template>

<script>
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

            const container = document.getElementById('recent-logs');
            const template = document.getElementById('log-template');
            container.innerHTML = '';

            if (logs.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-4">Aucun pointage récent</div>';
                return;
            }

            logs.forEach(log => {
                const clone = template.content.cloneNode(true);
                
                // Mise à jour des éléments
                clone.querySelector('.initials').textContent = log.initials || '??';
                clone.querySelector('.name').textContent = log.name || 'Employé inconnu';
                clone.querySelector('.time-ago').textContent = log.timeAgo || 'Date inconnue';
                
                const typeBadge = clone.querySelector('.type-badge');
                typeBadge.textContent = log.type || 'Inconnu';
                
                // Ajouter les classes séparément
                if (log.type === 'Entrée') {
                    typeBadge.classList.add('bg-green-100', 'text-green-600');
                } else {
                    typeBadge.classList.add('bg-blue-100', 'text-blue-600');
                }
                
                container.appendChild(clone);
            });
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des pointages:', error);
            const container = document.getElementById('recent-logs');
            container.innerHTML = `
                <div class="text-center text-red-500 py-4">
                    Une erreur est survenue lors de la récupération des pointages.<br>
                    <span class="text-sm">${error.message}</span>
                </div>
            `;
        });
}

// Mise à jour initiale
updateRecentLogs();

// Mise à jour toutes les 30 secondes
setInterval(updateRecentLogs, 30000);
</script>
