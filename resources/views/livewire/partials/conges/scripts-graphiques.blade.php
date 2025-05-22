<script>
    document.addEventListener('livewire:initialized', function () {
        // Configuration des couleurs
        const colors = {
            primary: '#4F46E5',
            secondary: '#10B981',
            tertiary: '#F59E0B',
            quaternary: '#EC4899',
            background: {
                light: 'rgba(79, 70, 229, 0.1)',
                dark: 'rgba(79, 70, 229, 0.2)'
            }
        };

        // Fonction pour obtenir un dégradé de couleurs
        function getGradientColors(count) {
            const baseColors = [
                '#4F46E5', // Indigo
                '#10B981', // Emerald
                '#F59E0B', // Amber
                '#EC4899', // Pink
                '#8B5CF6', // Violet
                '#06B6D4', // Cyan
                '#F97316', // Orange
                '#EF4444', // Red
                '#14B8A6', // Teal
                '#6366F1'  // Indigo
            ];
            
            // Si nous avons suffisamment de couleurs de base, les utiliser
            if (count <= baseColors.length) {
                return baseColors.slice(0, count);
            }
            
            // Sinon, générer des couleurs supplémentaires
            const result = [...baseColors];
            for (let i = baseColors.length; i < count; i++) {
                const hue = (i * 137) % 360; // Nombre d'or pour une bonne distribution
                result.push(`hsl(${hue}, 70%, 60%)`);
            }
            return result;
        }

        // Graphique des congés par mois
        const ctxMois = document.getElementById('congesParMoisChart').getContext('2d');
        new Chart(ctxMois, {
            type: 'bar',
            data: {
                labels: @json($chartData['congesParMois']['labels']),
                datasets: [{
                    label: 'Nombre de congés',
                    data: @json($chartData['congesParMois']['data']),
                    backgroundColor: colors.background.light,
                    borderColor: colors.primary,
                    borderWidth: 2
                }]
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
                }
            }
        });

        // Graphique des congés par type
        const ctxType = document.getElementById('congesParTypeChart').getContext('2d');
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: @json($chartData['congesParType']['labels']),
                datasets: [{
                    data: @json($chartData['congesParType']['data']),
                    backgroundColor: getGradientColors(@json($chartData['congesParType']['labels']).length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Graphique des congés par statut
        const ctxStatut = document.getElementById('congesParStatutChart').getContext('2d');
        new Chart(ctxStatut, {
            type: 'pie',
            data: {
                labels: @json($chartData['congesParStatut']['labels']),
                datasets: [{
                    data: @json($chartData['congesParStatut']['data']),
                    backgroundColor: getGradientColors(@json($chartData['congesParStatut']['labels']).length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Graphique des top employés
        const ctxTopEmployes = document.getElementById('topEmployesChart').getContext('2d');
        new Chart(ctxTopEmployes, {
            type: 'horizontalBar',
            data: {
                labels: @json($chartData['topEmployes']['labels']),
                datasets: [{
                    label: 'Jours de congés',
                    data: @json($chartData['topEmployes']['data']),
                    backgroundColor: colors.background.light,
                    borderColor: colors.secondary,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
