<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i> Détails par employé</h6>
                <div>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="employee-search" placeholder="Rechercher un employé...">
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="employee-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="employee-table">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Nombre de congés</th>
                                <th>Jours pris</th>
                                <th>Solde restant</th>
                                <th>Taux d'utilisation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Chargement des données...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="2">Total / Moyenne</th>
                                <th id="emp-total-conges">0</th>
                                <th id="emp-total-jours">0</th>
                                <th id="emp-total-solde">0</th>
                                <th id="emp-taux-moyen">0%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-3">
                    <nav aria-label="Pagination des employés">
                        <ul class="pagination justify-content-center" id="employee-pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-trophy me-2"></i> Top 5 des employés (jours pris)</h6>
            </div>
            <div class="card-body">
                <div class="list-group" id="top-employees">
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
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Employés avec solde élevé</h6>
            </div>
            <div class="card-body">
                <div class="list-group" id="high-balance-employees">
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Analyse</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-chart-line me-2"></i> Tendances</h6>
                            <p class="mb-0" id="emp-tendances">
                                Chargement des tendances...
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-lightbulb me-2"></i> Recommandations</h6>
                            <p class="mb-0" id="emp-recommandations">
                                Chargement des recommandations...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
