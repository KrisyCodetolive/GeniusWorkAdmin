/**
 * Gestion des graphiques de statistiques pour le tableau de bord des présences
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les graphiques
    initCharts();
    
    // Écouter les événements Livewire pour mettre à jour les graphiques
    if (typeof Livewire !== 'undefined') {
        Livewire.on('refreshCharts', stats => {
            updateCharts(stats);
        });
    }
    
    // Variables globales pour les graphiques
    let dailyChart, weeklyChart, hourlyChart;
    
    /**
     * Initialise les graphiques avec les données initiales
     */
    function initCharts() {
        // Vérifier si les éléments canvas existent
        if (!document.getElementById('dailyChart') || 
            !document.getElementById('weeklyChart') || 
            !document.getElementById('hourlyChart')) {
            console.warn('Les éléments canvas pour les graphiques ne sont pas disponibles');
            return;
        }
        
        // Initialiser le graphique des entrées/sorties par jour
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
                datasets: [
                    {
                        label: 'Entrées',
                        data: getInitialData('daily_entries'),
                        backgroundColor: 'rgba(34, 197, 94, 0.5)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Sorties',
                        data: getInitialData('daily_exits'),
                        backgroundColor: 'rgba(239, 68, 68, 0.5)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Initialiser le graphique de distribution hebdomadaire
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        weeklyChart = new Chart(weeklyCtx, {
            type: 'pie',
            data: {
                labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
                datasets: [
                    {
                        data: getInitialData('weekly_distribution'),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(139, 92, 246, 0.7)',
                            'rgba(236, 72, 153, 0.7)',
                            'rgba(107, 114, 128, 0.7)'
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Initialiser le graphique des entrées/sorties par heure
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourLabels = Array.from({length: 15}, (_, i) => `${i + 6}h`);
        
        hourlyChart = new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourLabels,
                datasets: [
                    {
                        label: 'Entrées',
                        data: getInitialData('hourly_entries', 15),
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Sorties',
                        data: getInitialData('hourly_exits', 15),
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    
    /**
     * Récupère les données initiales pour les graphiques
     * @param {string} key - Clé des données à récupérer
     * @param {number} length - Longueur du tableau à créer si les données n'existent pas
     * @returns {Array} - Tableau de données
     */
    function getInitialData(key, length = 7) {
        // Essayer de récupérer les données depuis l'élément data-stats
        const statsElement = document.getElementById('presence-stats-data');
        if (statsElement && statsElement.dataset.stats) {
            try {
                const stats = JSON.parse(statsElement.dataset.stats);
                if (stats[key]) {
                    return stats[key];
                }
            } catch (e) {
                console.error('Erreur lors de la récupération des données initiales:', e);
            }
        }
        
        // Retourner un tableau vide par défaut
        return Array(length).fill(0);
    }
    
    /**
     * Met à jour les graphiques avec de nouvelles données
     * @param {Object} stats - Nouvelles données de statistiques
     */
    function updateCharts(stats) {
        // Vérifier si les graphiques sont initialisés
        if (!dailyChart || !weeklyChart || !hourlyChart) {
            console.warn('Les graphiques ne sont pas initialisés');
            return;
        }
        
        // Mettre à jour les compteurs
        updateCounter('entrees-count', stats.entrees_today);
        updateCounter('sorties-count', stats.sorties_today);
        updateCounter('present-count', stats.currently_present);
        updateCounter('pause-count', stats.on_break);
        
        // Mettre à jour le graphique des entrées/sorties par jour
        if (stats.daily_entries && stats.daily_exits) {
            dailyChart.data.datasets[0].data = stats.daily_entries;
            dailyChart.data.datasets[1].data = stats.daily_exits;
            dailyChart.update();
        }
        
        // Mettre à jour le graphique de distribution hebdomadaire
        if (stats.weekly_distribution) {
            weeklyChart.data.datasets[0].data = stats.weekly_distribution;
            weeklyChart.update();
        }
        
        // Mettre à jour le graphique des entrées/sorties par heure
        if (stats.hourly_entries && stats.hourly_exits) {
            hourlyChart.data.datasets[0].data = stats.hourly_entries;
            hourlyChart.data.datasets[1].data = stats.hourly_exits;
            hourlyChart.update();
        }
        
        // Ajouter une animation aux cartes de statistiques
        animateStatsCards();
    }
    
    /**
     * Met à jour un compteur avec une animation
     * @param {string} id - ID de l'élément à mettre à jour
     * @param {number|string} value - Nouvelle valeur
     */
    function updateCounter(id, value) {
        const element = document.getElementById(id);
        if (element) {
            const oldValue = parseInt(element.textContent, 10) || 0;
            const newValue = parseInt(value, 10) || 0;
            
            if (oldValue !== newValue) {
                element.textContent = newValue;
                element.classList.add('updated');
                setTimeout(() => {
                    element.classList.remove('updated');
                }, 1000);
            }
        }
    }
    
    /**
     * Ajoute une animation aux cartes de statistiques
     */
    function animateStatsCards() {
        document.querySelectorAll('.stats-card').forEach(card => {
            card.classList.add('animate-pulse');
            setTimeout(() => {
                card.classList.remove('animate-pulse');
            }, 1000);
        });
    }
});
