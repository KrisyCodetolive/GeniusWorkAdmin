@extends('layouts.app')

@section('title', 'Créer un type de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i> Créer un nouveau type de congé
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('conge.types.store') }}" method="POST">
                        @csrf
                        
                        <!-- Entreprise -->
                        <div class="mb-3">
                            <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                            <select class="form-select @error('entreprise_id') is-invalid @enderror" 
                                    id="entreprise_id" name="entreprise_id" required>
                                @foreach($entreprises as $entreprise)
                                    <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                        {{ $entreprise->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('entreprise_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Nom -->
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <!-- Durée max annuelle -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duree_max_annuelle" class="form-label">Durée maximale annuelle (jours)</label>
                                    <input type="number" class="form-control @error('duree_max_annuelle') is-invalid @enderror" 
                                           id="duree_max_annuelle" name="duree_max_annuelle" value="{{ old('duree_max_annuelle') }}" 
                                           min="0" step="0.5">
                                    <div class="form-text">Laissez vide si pas de limite</div>
                                    @error('duree_max_annuelle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Délai de demande préalable -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="delai_demande_prealable" class="form-label">Délai de demande préalable (jours)</label>
                                    <input type="number" class="form-control @error('delai_demande_prealable') is-invalid @enderror" 
                                           id="delai_demande_prealable" name="delai_demande_prealable" value="{{ old('delai_demande_prealable', 0) }}" 
                                           min="0" step="1">
                                    <div class="form-text">Nombre de jours minimum avant la date de début</div>
                                    @error('delai_demande_prealable')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Options -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="necessite_justificatif" 
                                               name="necessite_justificatif" value="1" {{ old('necessite_justificatif') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="necessite_justificatif">Nécessite un justificatif</label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="est_paye" 
                                               name="est_paye" value="1" {{ old('est_paye', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="est_paye">Congé payé</label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="deductible_solde" 
                                               name="deductible_solde" value="1" {{ old('deductible_solde', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="deductible_solde">Déductible du solde</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Statut -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="statut" id="statut_actif" 
                                               value="actif" {{ old('statut', 'actif') == 'actif' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="statut_actif">
                                            Actif
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="statut" id="statut_inactif" 
                                               value="inactif" {{ old('statut') == 'inactif' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statut_inactif">
                                            Inactif
                                        </label>
                                    </div>
                                    @error('statut')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conditions d'éligibilité (JSON) -->
                        <div class="mb-3">
                            <label for="conditions_eligibilite" class="form-label">Conditions d'éligibilité (JSON)</label>
                            <textarea class="form-control @error('conditions_eligibilite') is-invalid @enderror" 
                                      id="conditions_eligibilite" name="conditions_eligibilite" rows="3">{{ old('conditions_eligibilite', '{}') }}</textarea>
                            <div class="form-text">Format JSON pour les conditions d'éligibilité avancées</div>
                            @error('conditions_eligibilite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Configuration (JSON) -->
                        <div class="mb-3">
                            <label for="configuration" class="form-label">Configuration (JSON)</label>
                            <textarea class="form-control @error('configuration') is-invalid @enderror" 
                                      id="configuration" name="configuration" rows="3">{{ old('configuration', '{}') }}</textarea>
                            <div class="form-text">Format JSON pour la configuration avancée</div>
                            @error('configuration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('conge.types.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des champs JSON avec un objet vide par défaut
        const conditionsEligibilite = document.getElementById('conditions_eligibilite');
        const configuration = document.getElementById('configuration');
        
        if (conditionsEligibilite.value.trim() === '') {
            conditionsEligibilite.value = '{}';
        }
        
        if (configuration.value.trim() === '') {
            configuration.value = '{}';
        }
        
        // Validation du format JSON avant soumission
        document.querySelector('form').addEventListener('submit', function(event) {
            try {
                JSON.parse(conditionsEligibilite.value);
                JSON.parse(configuration.value);
            } catch (e) {
                event.preventDefault();
                alert('Erreur: Le format JSON n\'est pas valide. Veuillez vérifier les champs JSON.');
            }
        });
    });
</script>
@endsection
