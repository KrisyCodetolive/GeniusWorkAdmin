@extends('layouts.app')

@section('title', 'Rapports de congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i> Rapports de congés
                    </h5>
                    <div>
                        <button type="button" class="btn btn-light" id="exportReportBtn">
                            <i class="fas fa-file-export me-1"></i> Exporter
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Filtres -->
                    @include('app.conge.rapports.partials.filtres')

                    <!-- Onglets des rapports -->
                    <ul class="nav nav-tabs mt-4" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab" aria-controls="summary" aria-selected="true">
                                <i class="fas fa-list me-1"></i> Synthèse
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="by-department-tab" data-bs-toggle="tab" data-bs-target="#by-department" type="button" role="tab" aria-controls="by-department" aria-selected="false">
                                <i class="fas fa-building me-1"></i> Par département
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="by-type-tab" data-bs-toggle="tab" data-bs-target="#by-type" type="button" role="tab" aria-controls="by-type" aria-selected="false">
                                <i class="fas fa-tag me-1"></i> Par type
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="by-employee-tab" data-bs-toggle="tab" data-bs-target="#by-employee" type="button" role="tab" aria-controls="by-employee" aria-selected="false">
                                <i class="fas fa-user me-1"></i> Par employé
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="false">
                                <i class="fas fa-calendar-alt me-1"></i> Chronologie
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Contenu des onglets -->
                    <div class="tab-content mt-3" id="reportTabsContent">
                        <!-- Synthèse -->
                        <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                            @include('app.conge.rapports.partials.synthese')
                        </div>
                        
                        <!-- Par département -->
                        <div class="tab-pane fade" id="by-department" role="tabpanel" aria-labelledby="by-department-tab">
                            @include('app.conge.rapports.partials.par-departement')
                        </div>
                        
                        <!-- Par type -->
                        <div class="tab-pane fade" id="by-type" role="tabpanel" aria-labelledby="by-type-tab">
                            @include('app.conge.rapports.partials.par-type')
                        </div>
                        
                        <!-- Par employé -->
                        <div class="tab-pane fade" id="by-employee" role="tabpanel" aria-labelledby="by-employee-tab">
                            @include('app.conge.rapports.partials.par-employe')
                        </div>
                        
                        <!-- Chronologie -->
                        <div class="tab-pane fade" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                            @include('app.conge.rapports.partials.chronologie')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'export -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter le rapport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('conge.rapports.export') }}" method="POST" id="exportForm">
                    @csrf
                    <input type="hidden" name="filters" id="exportFilters">
                    <input type="hidden" name="report_type" id="exportReportType" value="summary">
                    
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts" value="1" checked>
                            <label class="form-check-label" for="include_charts">
                                Inclure les graphiques (PDF uniquement)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="startExportBtn">Exporter</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de l'export
        const exportReportBtn = document.getElementById('exportReportBtn');
        const exportForm = document.getElementById('exportForm');
        const exportFilters = document.getElementById('exportFilters');
        const exportReportType = document.getElementById('exportReportType');
        const startExportBtn = document.getElementById('startExportBtn');
        
        // Récupérer le type de rapport actif
        function getActiveReportType() {
            const activeTab = document.querySelector('#reportTabs .nav-link.active');
            return activeTab.id.replace('-tab', '');
        }
        
        // Récupérer les filtres
        function getFilters() {
            const formData = new FormData(document.getElementById('filterForm'));
            const filters = {};
            
            for (const [key, value] of formData.entries()) {
                filters[key] = value;
            }
            
            return filters;
        }
        
        // Ouvrir la modal d'export
        exportReportBtn.addEventListener('click', function() {
            exportReportType.value = getActiveReportType();
            exportFilters.value = JSON.stringify(getFilters());
            
            const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportModal.show();
        });
        
        // Lancer l'export
        startExportBtn.addEventListener('click', function() {
            exportForm.submit();
        });
        
        // Gestion des onglets
        const reportTabs = document.querySelectorAll('#reportTabs .nav-link');
        reportTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Mettre à jour le type de rapport pour l'export
                exportReportType.value = this.id.replace('-tab', '');
            });
        });
        
        // Appliquer les filtres
        document.getElementById('applyFiltersBtn').addEventListener('click', function() {
            // Récupérer les filtres
            const filters = getFilters();
            
            // Charger les données filtrées via AJAX
            fetch('{{ route("conge.rapports.data") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    filters: filters,
                    report_type: getActiveReportType()
                })
            })
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les différentes sections
                updateReportData(data);
            })
            .catch(error => {
                console.error('Erreur lors du chargement des données:', error);
            });
        });
        
        // Fonction pour mettre à jour les données du rapport
        function updateReportData(data) {
            // Mettre à jour chaque section en fonction des données reçues
            if (data.summary) {
                updateSummaryReport(data.summary);
            }
            
            if (data.byDepartment) {
                updateDepartmentReport(data.byDepartment);
            }
            
            if (data.byType) {
                updateTypeReport(data.byType);
            }
            
            if (data.byEmployee) {
                updateEmployeeReport(data.byEmployee);
            }
            
            if (data.timeline) {
                updateTimelineReport(data.timeline);
            }
        }
        
        // Fonctions de mise à jour pour chaque type de rapport
        function updateSummaryReport(data) {
            // Mise à jour des statistiques de synthèse
            document.getElementById('total-conges').textContent = data.totalConges;
            document.getElementById('jours-pris').textContent = data.joursPris;
            document.getElementById('conges-approuves').textContent = data.congesApprouves;
            document.getElementById('conges-en-attente').textContent = data.congesEnAttente;
            
            // Mise à jour du graphique de répartition par statut
            updateStatusChart(data.repartitionStatut);
            
            // Mise à jour du tableau des congés récents
            updateRecentLeavesTable(data.congesRecents);
        }
        
        function updateDepartmentReport(data) {
            // Mise à jour du graphique par département
            updateDepartmentChart(data.repartition);
            
            // Mise à jour du tableau par département
            updateDepartmentTable(data.details);
        }
        
        function updateTypeReport(data) {
            // Mise à jour du graphique par type
            updateTypeChart(data.repartition);
            
            // Mise à jour du tableau par type
            updateTypeTable(data.details);
        }
        
        function updateEmployeeReport(data) {
            // Mise à jour du tableau par employé
            updateEmployeeTable(data.details);
        }
        
        function updateTimelineReport(data) {
            // Mise à jour du graphique de chronologie
            updateTimelineChart(data.repartition);
        }
        
        // Initialisation des graphiques
        initCharts();
        
        // Charger les données initiales
        document.getElementById('applyFiltersBtn').click();
    });
    
    // Fonction d'initialisation des graphiques
    function initCharts() {
        // Initialisation des graphiques avec des données vides
        // Ces graphiques seront mis à jour lors du chargement des données
        
        // Graphique de répartition par statut
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        window.statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Approuvé', 'En attente', 'Refusé', 'Annulé'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Graphique par département
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        window.departmentChart = new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Jours de congés',
                    data: [],
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Graphique par type
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        window.typeChart = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', 
                        '#6c757d', '#17a2b8', '#fd7e14', '#20c997'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Graphique de chronologie
        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        window.timelineChart = new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Jours de congés',
                    data: [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Fonctions de mise à jour des graphiques
    function updateStatusChart(data) {
        window.statusChart.data.datasets[0].data = [
            data.approuve || 0,
            data.en_attente || 0,
            data.refuse || 0,
            data.annule || 0
        ];
        window.statusChart.update();
    }
    
    function updateDepartmentChart(data) {
        window.departmentChart.data.labels = data.map(item => item.nom);
        window.departmentChart.data.datasets[0].data = data.map(item => item.jours);
        window.departmentChart.update();
    }
    
    function updateTypeChart(data) {
        window.typeChart.data.labels = data.map(item => item.nom);
        window.typeChart.data.datasets[0].data = data.map(item => item.jours);
        window.typeChart.update();
    }
    
    function updateTimelineChart(data) {
        window.timelineChart.data.labels = data.map(item => item.periode);
        window.timelineChart.data.datasets[0].data = data.map(item => item.jours);
        window.timelineChart.update();
    }
    
    // Fonctions de mise à jour des tableaux
    function updateRecentLeavesTable(data) {
        const tbody = document.querySelector('#recent-leaves-table tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="5" class="text-center">Aucune donnée disponible</td>';
            tbody.appendChild(tr);
            return;
        }
        
        data.forEach(conge => {
            const tr = document.createElement('tr');
            
            let statusBadge = '';
            if (conge.statut === 'approuve') {
                statusBadge = '<span class="badge bg-success">Approuvé</span>';
            } else if (conge.statut === 'en_attente') {
                statusBadge = '<span class="badge bg-warning">En attente</span>';
            } else if (conge.statut === 'refuse') {
                statusBadge = '<span class="badge bg-danger">Refusé</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">Annulé</span>';
            }
            
            tr.innerHTML = `
                <td>${conge.employe}</td>
                <td>${conge.type}</td>
                <td>${conge.periode}</td>
                <td>${conge.duree} jours</td>
                <td>${statusBadge}</td>
            `;
            
            tbody.appendChild(tr);
        });
    }
    
    function updateDepartmentTable(data) {
        const tbody = document.querySelector('#department-table tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="5" class="text-center">Aucune donnée disponible</td>';
            tbody.appendChild(tr);
            return;
        }
        
        data.forEach(dept => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${dept.nom}</td>
                <td>${dept.employes}</td>
                <td>${dept.conges}</td>
                <td>${dept.jours}</td>
                <td>${dept.moyenne.toFixed(1)} jours/employé</td>
            `;
            
            tbody.appendChild(tr);
        });
    }
    
    function updateTypeTable(data) {
        const tbody = document.querySelector('#type-table tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="5" class="text-center">Aucune donnée disponible</td>';
            tbody.appendChild(tr);
            return;
        }
        
        data.forEach(type => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${type.nom}</td>
                <td>${type.conges}</td>
                <td>${type.jours}</td>
                <td>${type.duree_moyenne.toFixed(1)} jours/congé</td>
                <td>${type.pourcentage.toFixed(1)}%</td>
            `;
            
            tbody.appendChild(tr);
        });
    }
    
    function updateEmployeeTable(data) {
        const tbody = document.querySelector('#employee-table tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="6" class="text-center">Aucune donnée disponible</td>';
            tbody.appendChild(tr);
            return;
        }
        
        data.forEach(employe => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${employe.nom}</td>
                <td>${employe.departement}</td>
                <td>${employe.conges}</td>
                <td>${employe.jours}</td>
                <td>${employe.solde_restant} jours</td>
                <td>${employe.taux_utilisation.toFixed(1)}%</td>
            `;
            
            tbody.appendChild(tr);
        });
    }
</script>
@endsection
