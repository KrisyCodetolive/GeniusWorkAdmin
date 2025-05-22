// Script pour charger Chart.js dynamiquement
document.addEventListener('DOMContentLoaded', function() {
    loadChartJs();
});

document.addEventListener('livewire:navigated', function() {
    loadChartJs();
});

// Écouter les changements de thème (mode clair/sombre)
document.addEventListener('theme-changed', function() {
    loadChartJs();
});

function loadChartJs() {
    if (document.getElementById('visitesChart')) {
        // Vérifier si Chart.js est déjà chargé
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = function() {
                initializeCharts();
            };
            document.head.appendChild(script);
        } else {
            initializeCharts();
        }
    }
}

function initializeCharts() {
    const chartElement = document.getElementById('visitesChart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        const chartDataElement = document.getElementById('chartData');
        const chartThemeElement = document.getElementById('chartTheme');
        
        if (chartDataElement) {
            try {
                const chartData = JSON.parse(chartDataElement.textContent);
                let isDarkMode = false;
                
                // Détecter le mode sombre
                if (chartThemeElement) {
                    const themeData = JSON.parse(chartThemeElement.textContent);
                    isDarkMode = themeData.isDarkMode || document.documentElement.classList.contains('dark');
                } else {
                    isDarkMode = document.documentElement.classList.contains('dark');
                }
                
                // Détruire le graphique existant s'il y en a un
                if (window.visitesChart instanceof Chart) {
                    window.visitesChart.destroy();
                }
                
                // Configurer les couleurs en fonction du thème
                const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                const textColor = isDarkMode ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)';
                
                window.visitesChart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            },
                            tooltip: {
                                backgroundColor: isDarkMode ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)',
                                titleColor: isDarkMode ? '#fff' : '#000',
                                bodyColor: isDarkMode ? '#fff' : '#000',
                                borderColor: isDarkMode ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.2)',
                                borderWidth: 1
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            x: {
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Erreur lors de l\'initialisation du graphique:', e);
            }
        }
    }
}
