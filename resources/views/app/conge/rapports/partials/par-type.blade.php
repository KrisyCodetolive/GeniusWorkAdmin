<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Répartition des congés par type</h6>
            </div>
            <div class="card-body">
                <canvas id="typeChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-table me-2"></i> Détails par type de congé</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="type-table">
                        <thead>
                            <tr>
                                <th>Type de congé</th>
                                <th>Nombre de congés</th>
                                <th>Jours de congés</th>
                                <th>Durée moyenne</th>
                                <th>Pourcentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center">Chargement des données...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th>Total</th>
                                <th id="type-total-conges">0</th>
                                <th id="type-total-jours">0</th>
                                <th id="type-duree-moyenne">0</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Analyse</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-chart-line me-2"></i> Tendances</h6>
                            <p class="mb-0" id="type-tendances">
                                Chargement des tendances...
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-lightbulb me-2"></i> Recommandations</h6>
                            <p class="mb-0" id="type-recommandations">
                                Chargement des recommandations...
                            </p>
                        </div>
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
                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Évolution mensuelle par type</h6>
            </div>
            <div class="card-body">
                <div id="type-evolution-mensuelle">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des données d'évolution...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
