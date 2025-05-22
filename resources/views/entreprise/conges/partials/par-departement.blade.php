<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Répartition des congés par département</h6>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-table me-2"></i> Détails par département</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="department-table">
                        <thead>
                            <tr>
                                <th>Département</th>
                                <th>Nombre d'employés</th>
                                <th>Nombre de congés</th>
                                <th>Jours de congés</th>
                                <th>Moyenne par employé</th>
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
                                <th id="dept-total-employes">0</th>
                                <th id="dept-total-conges">0</th>
                                <th id="dept-total-jours">0</th>
                                <th id="dept-moyenne-globale">0</th>
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
                            <p class="mb-0" id="dept-tendances">
                                Chargement des tendances...
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-lightbulb me-2"></i> Recommandations</h6>
                            <p class="mb-0" id="dept-recommandations">
                                Chargement des recommandations...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
