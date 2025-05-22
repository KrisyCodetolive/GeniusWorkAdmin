<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter me-2"></i> Filtres</h6>
    </div>
    <div class="card-body">
        <form id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="date_debut" class="form-label">Période du</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ date('Y-m-d', strtotime('first day of january this year')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="date_fin" class="form-label">au</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ date('Y-m-d', strtotime('last day of december this year')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="departement_id" class="form-label">Département</label>
                        <select class="form-select" id="departement_id" name="departement_id">
                            <option value="">Tous les départements</option>
                            @foreach($departements ?? [] as $departement)
                                <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site</label>
                        <select class="form-select" id="site_id" name="site_id">
                            <option value="">Tous les sites</option>
                            @foreach($sites ?? [] as $site)
                                <option value="{{ $site->id }}">{{ $site->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="type_conge_id" class="form-label">Type de congé</label>
                        <select class="form-select" id="type_conge_id" name="type_conge_id">
                            <option value="">Tous les types</option>
                            @foreach($typesConge ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="approuve">Approuvé</option>
                            <option value="en_attente">En attente</option>
                            <option value="refuse">Refusé</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="employe_id" class="form-label">Employé</label>
                        <select class="form-select" id="employe_id" name="employe_id">
                            <option value="">Tous les employés</option>
                            @foreach($employes ?? [] as $employe)
                                <option value="{{ $employe->id }}">{{ $employe->nom }} {{ $employe->prenom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="periode" class="form-label">Regroupement temporel</label>
                        <select class="form-select" id="periode" name="periode">
                            <option value="mois">Par mois</option>
                            <option value="trimestre">Par trimestre</option>
                            <option value="annee">Par année</option>
                            <option value="semaine">Par semaine</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                        <i class="fas fa-search me-1"></i> Appliquer les filtres
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo me-1"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
