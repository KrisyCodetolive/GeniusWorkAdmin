@props(['appareil' => null, 'chartId' => 'presence-chart', 'height' => '300px'])

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Évolution des pointages</h3>
            <div class="flex space-x-2">
                <select id="{{ $chartId }}-period" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="day">Aujourd'hui</option>
                    <option value="week" selected>Cette semaine</option>
                    <option value="month">Ce mois</option>
                    <option value="year">Cette année</option>
                </select>
                <select id="{{ $chartId }}-type" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="all">Tous les types</option>
                    <option value="entree">Entrées</option>
                    <option value="sortie">Sorties</option>
                    <option value="pause">Pauses</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="p-4">
        <canvas id="{{ $chartId }}" style="height: {{ $height }}; width: 100%;"></canvas>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Données simulées pour le graphique
        const generateData = (period, type) => {
            let labels = [];
            let datasets = [];
            
            if (period === 'day') {
                labels = ['8h', '9h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h'];
                
                if (type === 'all' || type === 'entree') {
                    datasets.push({
                        label: 'Entrées',
                        data: [15, 8, 5, 2, 1, 3, 2, 1, 0, 0, 0, 0],
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'sortie') {
                    datasets.push({
                        label: 'Sorties',
                        data: [0, 1, 2, 3, 5, 10, 3, 2, 4, 7, 12, 8],
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'pause') {
                    datasets.push({
                        label: 'Pauses',
                        data: [0, 0, 2, 5, 12, 8, 5, 2, 0, 0, 0, 0],
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        tension: 0.4
                    });
                }
            } else if (period === 'week') {
                labels = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                
                if (type === 'all' || type === 'entree') {
                    datasets.push({
                        label: 'Entrées',
                        data: [32, 35, 30, 33, 28, 5, 2],
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'sortie') {
                    datasets.push({
                        label: 'Sorties',
                        data: [32, 35, 30, 33, 28, 5, 2],
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'pause') {
                    datasets.push({
                        label: 'Pauses',
                        data: [25, 28, 24, 26, 22, 3, 1],
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        tension: 0.4
                    });
                }
            } else if (period === 'month') {
                labels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'];
                
                if (type === 'all' || type === 'entree') {
                    datasets.push({
                        label: 'Entrées',
                        data: [150, 165, 140, 155],
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'sortie') {
                    datasets.push({
                        label: 'Sorties',
                        data: [150, 165, 140, 155],
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'pause') {
                    datasets.push({
                        label: 'Pauses',
                        data: [120, 130, 110, 125],
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        tension: 0.4
                    });
                }
            } else if (period === 'year') {
                labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                
                if (type === 'all' || type === 'entree') {
                    datasets.push({
                        label: 'Entrées',
                        data: [620, 580, 650, 630, 640, 610, 590, 450, 630, 650, 620, 580],
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'sortie') {
                    datasets.push({
                        label: 'Sorties',
                        data: [620, 580, 650, 630, 640, 610, 590, 450, 630, 650, 620, 580],
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        tension: 0.4
                    });
                }
                
                if (type === 'all' || type === 'pause') {
                    datasets.push({
                        label: 'Pauses',
                        data: [500, 460, 520, 510, 520, 490, 470, 360, 510, 520, 500, 460],
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        tension: 0.4
                    });
                }
            }
            
            return { labels, datasets };
        };
        
        // Initialiser le graphique
        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
        let presenceChart = new Chart(ctx, {
            type: 'line',
            data: generateData('week', 'all'),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        
        // Mettre à jour le graphique lors du changement de période ou de type
        const updateChart = () => {
            const period = document.getElementById('{{ $chartId }}-period').value;
            const type = document.getElementById('{{ $chartId }}-type').value;
            const newData = generateData(period, type);
            
            presenceChart.data.labels = newData.labels;
            presenceChart.data.datasets = newData.datasets;
            presenceChart.update();
        };
        
        document.getElementById('{{ $chartId }}-period').addEventListener('change', updateChart);
        document.getElementById('{{ $chartId }}-type').addEventListener('change', updateChart);
        
        // Si un appareil spécifique est fourni, ajuster les données en conséquence
        @if($appareil)
            // Ici, vous pourriez faire une requête AJAX pour obtenir les données spécifiques à l'appareil
            // ou utiliser des données passées via le contrôleur
        @endif
    });
</script>
@endpush
