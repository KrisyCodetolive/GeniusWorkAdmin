@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Rapports de présence')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Rapports de présence</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('entreprise.dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Rapports de présence</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtres du rapport
                </div>
                <div class="card-body">
                    <form id="rapportForm" method="GET" action="{{ route('entreprise.presences.rapport') }}">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ request('date_debut', now()->startOfMonth()->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ request('date_fin', now()->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="departement_id" class="form-label">Département</label>
                                    <select class="form-select" id="departement_id" name="departement_id">
                                        <option value="">Tous les départements</option>
                                        @foreach($entreprise->departements as $departement)
                                            <option value="{{ $departement->id }}" {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                                                {{ $departement->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">Employé</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">Tous les employés</option>
                                        @foreach($entreprise->users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->nom }} {{ $user->prenom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="site_id" class="form-label">Site</label>
                                    <select class="form-select" id="site_id" name="site_id">
                                        <option value="">Tous les sites</option>
                                        @foreach($entreprise->sites as $site)
                                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                                {{ $site->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type_rapport" class="form-label">Type de rapport</label>
                                    <select class="form-select" id="type_rapport" name="type_rapport">
                                        <option value="heures_travaillees" {{ request('type_rapport') == 'heures_travaillees' ? 'selected' : '' }}>Heures travaillées</option>
                                        <option value="retards" {{ request('type_rapport') == 'retards' ? 'selected' : '' }}>Retards</option>
                                        <option value="absences" {{ request('type_rapport') == 'absences' ? 'selected' : '' }}>Absences</option>
                                        <option value="heures_supplementaires" {{ request('type_rapport') == 'heures_supplementaires' ? 'selected' : '' }}>Heures supplémentaires</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Générer le rapport
                            </button>
                            <div>
                                <button type="button" class="btn btn-success" id="exportExcel">
                                    <i class="fas fa-file-excel me-1"></i> Exporter en Excel
                                </button>
                                <button type="button" class="btn btn-danger" id="exportPdf">
                                    <i class="fas fa-file-pdf me-1"></i> Exporter en PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Résumé
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Heures travaillées</h5>
                                    <h2 class="display-4">{{ isset($statistiques['heures_travaillees']) ? number_format($statistiques['heures_travaillees'], 1) : '0' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Retards</h5>
                                    <h2 class="display-4">{{ isset($statistiques['retards']) ? $statistiques['retards'] : '0' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Absences</h5>
                                    <h2 class="display-4">{{ isset($statistiques['absences']) ? $statistiques['absences'] : '0' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Heures supp.</h5>
                                    <h2 class="display-4">{{ isset($statistiques['heures_supplementaires']) ? number_format($statistiques['heures_supplementaires'], 1) : '0' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <canvas id="presenceChart" width="100%" height="50"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="retardChart" width="100%" height="50"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Détails du rapport
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="rapportTable">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Département</th>
                                    <th>Heures travaillées</th>
                                    <th>Retards</th>
                                    <th>Absences</th>
                                    <th>Heures supplémentaires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($rapportDetails) && count($rapportDetails) > 0)
                                    @foreach($rapportDetails as $detail)
                                        <tr>
                                            <td>{{ $detail['nom'] }} {{ $detail['prenom'] }}</td>
                                            <td>{{ $detail['departement'] }}</td>
                                            <td>{{ number_format($detail['heures_travaillees'], 1) }} h</td>
                                            <td>{{ $detail['retards'] }}</td>
                                            <td>{{ $detail['absences'] }}</td>
                                            <td>{{ number_format($detail['heures_supplementaires'], 1) }} h</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Aucune donnée disponible pour la période sélectionnée</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation du tableau avec DataTables
        $('#rapportTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
            },
            responsive: true,
            paging: true,
            info: true,
            searching: true
        });

        // Configuration des graphiques si des données sont disponibles
        @if(isset($statistiquesJours))
            // Graphique de présence
            const ctxPresence = document.getElementById('presenceChart').getContext('2d');
            const presenceChart = new Chart(ctxPresence, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($statistiquesJours)) !!},
                    datasets: [{
                        label: 'Heures travaillées',
                        data: {!! json_encode(array_map(function($jour) { return $jour['heures_travaillees']; }, $statistiquesJours)) !!},
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Évolution des heures travaillées'
                        },
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Heures'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });

            // Graphique de retards
            const ctxRetard = document.getElementById('retardChart').getContext('2d');
            const retardChart = new Chart(ctxRetard, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($statistiquesJours)) !!},
                    datasets: [{
                        label: 'Retards',
                        data: {!! json_encode(array_map(function($jour) { return $jour['retards']; }, $statistiquesJours)) !!},
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Évolution des retards'
                        },
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de retards'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        @else
            // Afficher des graphiques vides ou un message si aucune donnée n'est disponible
            const ctxPresence = document.getElementById('presenceChart').getContext('2d');
            const presenceChart = new Chart(ctxPresence, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Heures travaillées',
                        data: [],
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Évolution des heures travaillées (aucune donnée)'
                        }
                    }
                }
            });

            const ctxRetard = document.getElementById('retardChart').getContext('2d');
            const retardChart = new Chart(ctxRetard, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Retards',
                        data: [],
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Évolution des retards (aucune donnée)'
                        }
                    }
                }
            });
        @endif

        // Gestion des exports
        document.getElementById('exportExcel').addEventListener('click', function() {
            const form = document.getElementById('rapportForm');
            const formData = new FormData(form);
            formData.append('format', 'excel');
            
            // Rediriger vers la route d'export avec tous les paramètres
            const params = new URLSearchParams(formData);
            window.location.href = "{{ route('entreprise.presences.rapport') }}" + "?" + params.toString();
        });

        document.getElementById('exportPdf').addEventListener('click', function() {
            const form = document.getElementById('rapportForm');
            const formData = new FormData(form);
            formData.append('format', 'pdf');
            
            // Rediriger vers la route d'export avec tous les paramètres
            const params = new URLSearchParams(formData);
            window.location.href = "{{ route('entreprise.presences.rapport') }}" + "?" + params.toString();
        });
    });
</script>
@endsection
