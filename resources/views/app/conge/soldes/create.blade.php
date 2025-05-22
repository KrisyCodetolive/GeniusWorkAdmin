@extends('layouts.app')

@section('title', 'Créer un solde de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i> Créer un nouveau solde de congé
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('conge.soldes.store') }}" method="POST">
                        @csrf
                        
                        <!-- Entreprise -->
                        <div class="mb-3">
                            <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                            <select class="form-select @error('entreprise_id') is-invalid @enderror" 
                                    id="entreprise_id" name="entreprise_id" required>
                                <option value="">Sélectionnez une entreprise</option>
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
                        
                        <!-- Type de congé -->
                        <div class="mb-3">
                            <label for="type_conge_id" class="form-label">Type de congé <span class="text-danger">*</span></label>
                            <select class="form-select @error('type_conge_id') is-invalid @enderror" 
                                    id="type_conge_id" name="type_conge_id" required>
                                <option value="">Sélectionnez un type de congé</option>
                                @foreach($typesConge as $typeConge)
                                    <option value="{{ $typeConge->id }}" {{ old('type_conge_id') == $typeConge->id ? 'selected' : '' }}>
                                        {{ $typeConge->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_conge_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Utilisateur -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Employé <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required>
                                <option value="">Sélectionnez un employé</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->nom }} {{ $user->prenom }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <!-- Année -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="annee" class="form-label">Année <span class="text-danger">*</span></label>
                                    <select class="form-select @error('annee') is-invalid @enderror" id="annee" name="annee" required>
                                        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                            <option value="{{ $i }}" {{ old('annee', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('annee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Solde initial -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="solde_initial" class="form-label">Solde initial (jours) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('solde_initial') is-invalid @enderror" 
                                           id="solde_initial" name="solde_initial" value="{{ old('solde_initial', 0) }}" 
                                           step="0.5" min="0" required>
                                    @error('solde_initial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Date d'expiration -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_expiration" class="form-label">Date d'expiration</label>
                                    <input type="date" class="form-control @error('date_expiration') is-invalid @enderror" 
                                           id="date_expiration" name="date_expiration" value="{{ old('date_expiration') }}">
                                    <div class="form-text">Laissez vide si pas de date d'expiration</div>
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Solde utilisé -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="solde_utilise" class="form-label">Solde déjà utilisé (jours)</label>
                                    <input type="number" class="form-control @error('solde_utilise') is-invalid @enderror" 
                                           id="solde_utilise" name="solde_utilise" value="{{ old('solde_utilise', 0) }}" 
                                           step="0.5" min="0">
                                    <div class="form-text">Remplir uniquement si des congés ont déjà été pris</div>
                                    @error('solde_utilise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('conge.soldes.index') }}" class="btn btn-secondary">
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
        // Filtrer les utilisateurs en fonction de l'entreprise sélectionnée
        const entrepriseSelect = document.getElementById('entreprise_id');
        const userSelect = document.getElementById('user_id');
        const typeCongeSelect = document.getElementById('type_conge_id');
        
        entrepriseSelect.addEventListener('change', function() {
            const entrepriseId = this.value;
            
            // Réinitialiser les options de l'utilisateur
            userSelect.innerHTML = '<option value="">Sélectionnez un employé</option>';
            
            if (entrepriseId) {
                // Charger les utilisateurs de l'entreprise
                fetch(`/api/entreprises/${entrepriseId}/users`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = `${user.nom} ${user.prenom} (${user.email})`;
                            userSelect.appendChild(option);
                        });
                    });
                
                // Charger les types de congé de l'entreprise
                typeCongeSelect.innerHTML = '<option value="">Sélectionnez un type de congé</option>';
                fetch(`/api/entreprises/${entrepriseId}/types-conge`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.id;
                            option.textContent = type.nom;
                            typeCongeSelect.appendChild(option);
                        });
                    });
            }
        });
        
        // Calculer le solde restant
        const soldeInitialInput = document.getElementById('solde_initial');
        const soldeUtiliseInput = document.getElementById('solde_utilise');
        
        function updateSoldeRestant() {
            const soldeInitial = parseFloat(soldeInitialInput.value) || 0;
            const soldeUtilise = parseFloat(soldeUtiliseInput.value) || 0;
            const soldeRestant = soldeInitial - soldeUtilise;
            
            // Afficher le solde restant (optionnel, peut être ajouté au DOM)
            console.log('Solde restant:', soldeRestant);
        }
        
        soldeInitialInput.addEventListener('input', updateSoldeRestant);
        soldeUtiliseInput.addEventListener('input', updateSoldeRestant);
    });
</script>
@endsection
