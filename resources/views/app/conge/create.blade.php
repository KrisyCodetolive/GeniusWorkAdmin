@extends('layouts.app')

@section('title', 'Nouvelle demande de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i> Nouvelle demande de congé
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('conge.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Soldes disponibles -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">Mes soldes disponibles</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @forelse($soldes as $solde)
                                                <div class="col-md-3 mb-3">
                                                    <div class="card h-100 {{ $solde->solde_restant > 0 ? 'border-success' : 'border-danger' }}">
                                                        <div class="card-body text-center">
                                                            <h6 class="card-title">{{ $solde->typeConge->nom }}</h6>
                                                            <div class="h4 mb-0 {{ $solde->solde_restant > 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ number_format($solde->solde_restant, 1) }} <small>jours</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        Aucun solde de congés n'est disponible pour cette année.
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Type de congé -->
                        <div class="mb-3">
                            <label for="type_conge_id" class="form-label">Type de congé <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_conge_id') is-invalid @enderror" 
                                    id="type_conge_id" name="type_conge_id" required>
                                <option value="">Sélectionnez un type de congé</option>
                                @foreach($typesConge as $typeConge)
                                    <option value="{{ $typeConge->id }}" 
                                            data-justificatif="{{ $typeConge->necessite_justificatif ? 'true' : 'false' }}"
                                            data-delai="{{ $typeConge->delai_demande_prealable }}"
                                            data-deductible="{{ $typeConge->deductible_solde ? 'true' : 'false' }}"
                                            {{ old('type_conge_id') == $typeConge->id ? 'selected' : '' }}>
                                        {{ $typeConge->nom }} 
                                        {{ $typeConge->est_paye ? '(Payé)' : '(Non payé)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_conge_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="typeCongeInfo" class="form-text mt-2 d-none alert alert-info">
                                <!-- Informations sur le type de congé sélectionné -->
                            </div>
                        </div>
                        
                        <!-- Période de congé -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_debut') is-invalid @enderror" 
                                           id="date_debut" name="date_debut" 
                                           value="{{ old('date_debut') ?? date('Y-m-d') }}" required>
                                    @error('date_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_fin') is-invalid @enderror" 
                                           id="date_fin" name="date_fin" 
                                           value="{{ old('date_fin') ?? date('Y-m-d') }}" required>
                                    @error('date_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estimation du nombre de jours -->
                        <div class="mb-3">
                            <div class="alert alert-secondary" id="estimation_jours">
                                Nombre de jours ouvrables estimé : <span id="nb_jours">0</span>
                            </div>
                        </div>
                        
                        <!-- Motif -->
                        <div class="mb-3">
                            <label for="motif" class="form-label">Motif</label>
                            <textarea class="form-control @error('motif') is-invalid @enderror" 
                                      id="motif" name="motif" rows="3">{{ old('motif') }}</textarea>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Justificatif (conditionnel) -->
                        <div class="mb-3" id="justificatif_container" style="display: none;">
                            <label for="justificatif" class="form-label">Justificatif <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('justificatif') is-invalid @enderror" 
                                   id="justificatif" name="justificatif">
                            <div class="form-text">Formats acceptés : PDF, JPG, JPEG, PNG (max 2 Mo)</div>
                            @error('justificatif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('conge.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Soumettre la demande
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
        const typeCongeSelect = document.getElementById('type_conge_id');
        const justificatifContainer = document.getElementById('justificatif_container');
        const justificatifInput = document.getElementById('justificatif');
        const typeCongeInfo = document.getElementById('typeCongeInfo');
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');
        const nbJours = document.getElementById('nb_jours');
        
        // Fonction pour calculer les jours ouvrables
        function calculerJoursOuvrables() {
            if (dateDebut.value && dateFin.value) {
                const debut = new Date(dateDebut.value);
                const fin = new Date(dateFin.value);
                
                // Vérifier que la date de fin est après la date de début
                if (fin < debut) {
                    nbJours.textContent = "0";
                    return;
                }
                
                let count = 0;
                const currentDate = new Date(debut);
                
                // Parcourir tous les jours entre les deux dates
                while (currentDate <= fin) {
                    // Si ce n'est pas un weekend (0 = dimanche, 6 = samedi)
                    const dayOfWeek = currentDate.getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        count++;
                    }
                    
                    // Passer au jour suivant
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                nbJours.textContent = count;
            }
        }
        
        // Fonction pour mettre à jour les informations du type de congé
        function updateTypeCongeInfo() {
            if (typeCongeSelect.value) {
                const selectedOption = typeCongeSelect.options[typeCongeSelect.selectedIndex];
                const necessisteJustificatif = selectedOption.dataset.justificatif === 'true';
                const delai = parseInt(selectedOption.dataset.delai || 0);
                const deductible = selectedOption.dataset.deductible === 'true';
                
                // Afficher ou masquer le champ de justificatif
                justificatifContainer.style.display = necessisteJustificatif ? 'block' : 'none';
                justificatifInput.required = necessisteJustificatif;
                
                // Afficher les informations sur le type de congé
                let infoText = '';
                
                if (delai > 0) {
                    infoText += `<p><i class="fas fa-info-circle"></i> Ce type de congé nécessite une demande préalable de ${delai} jour(s).</p>`;
                }
                
                if (necessisteJustificatif) {
                    infoText += `<p><i class="fas fa-file-alt"></i> Un justificatif est requis pour ce type de congé.</p>`;
                }
                
                if (deductible) {
                    infoText += `<p><i class="fas fa-calculator"></i> Ce congé sera déduit de votre solde disponible.</p>`;
                }
                
                if (infoText) {
                    typeCongeInfo.innerHTML = infoText;
                    typeCongeInfo.classList.remove('d-none');
                } else {
                    typeCongeInfo.classList.add('d-none');
                }
            } else {
                justificatifContainer.style.display = 'none';
                justificatifInput.required = false;
                typeCongeInfo.classList.add('d-none');
            }
        }
        
        // Événements
        typeCongeSelect.addEventListener('change', updateTypeCongeInfo);
        dateDebut.addEventListener('change', calculerJoursOuvrables);
        dateFin.addEventListener('change', calculerJoursOuvrables);
        
        // Initialisation
        updateTypeCongeInfo();
        calculerJoursOuvrables();
    });
</script>
@endsection
