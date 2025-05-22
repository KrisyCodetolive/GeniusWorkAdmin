<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i> Évolution des congés dans le temps</h6>
            </div>
            <div class="card-body">
                <canvas id="timelineChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Répartition mensuelle</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="monthly-distribution-table">
                                <thead>
                                    <tr class="text-center">
                                        <th>Mois</th>
                                        <th>Jan</th>
                                        <th>Fév</th>
                                        <th>Mar</th>
                                        <th>Avr</th>
                                        <th>Mai</th>
                                        <th>Juin</th>
                                        <th>Juil</th>
                                        <th>Août</th>
                                        <th>Sep</th>
                                        <th>Oct</th>
                                        <th>Nov</th>
                                        <th>Déc</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <th>Jours de congés</th>
                                        <td id="month-1">0</td>
                                        <td id="month-2">0</td>
                                        <td id="month-3">0</td>
                                        <td id="month-4">0</td>
                                        <td id="month-5">0</td>
                                        <td id="month-6">0</td>
                                        <td id="month-7">0</td>
                                        <td id="month-8">0</td>
                                        <td id="month-9">0</td>
                                        <td id="month-10">0</td>
                                        <td id="month-11">0</td>
                                        <td id="month-12">0</td>
                                        <td id="month-total">0</td>
                                    </tr>
                                    <tr class="text-center">
                                        <th>Nombre de congés</th>
                                        <td id="count-1">0</td>
                                        <td id="count-2">0</td>
                                        <td id="count-3">0</td>
                                        <td id="count-4">0</td>
                                        <td id="count-5">0</td>
                                        <td id="count-6">0</td>
                                        <td id="count-7">0</td>
                                        <td id="count-8">0</td>
                                        <td id="count-9">0</td>
                                        <td id="count-10">0</td>
                                        <td id="count-11">0</td>
                                        <td id="count-12">0</td>
                                        <td id="count-total">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Périodes de forte demande</h6>
            </div>
            <div class="card-body">
                <div class="list-group" id="high-demand-periods">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des données...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-calendar-minus me-2"></i> Périodes de faible demande</h6>
            </div>
            <div class="card-body">
                <div class="list-group" id="low-demand-periods">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des données...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Analyse saisonnière</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-chart-line me-2"></i> Tendances</h6>
                            <p class="mb-0" id="timeline-tendances">
                                Chargement des tendances...
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-lightbulb me-2"></i> Recommandations</h6>
                            <p class="mb-0" id="timeline-recommandations">
                                Chargement des recommandations...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
