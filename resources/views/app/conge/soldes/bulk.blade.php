@extends('layouts.app')

@section('title', 'Gestion en masse des soldes de congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users-cog me-2"></i> Gestion en masse des soldes de congés
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('conge.soldes.bulk.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
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
                            </div>
                            
                            <div class="col-md-4">
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
                            </div>
                            
                            <div class="col-md-4">
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
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="operation" class="form-label">Opération <span class="text-danger">*</span></label>
                                    <select class="form-select @error('operation') is-invalid @enderror" id="operation" name="operation" required>
                                        <option value="creer" {{ old('operation') == 'creer' ? 'selected' : '' }}>Créer de nouveaux soldes</option>
                                        <option value="ajouter" {{ old('operation') == 'ajouter' ? 'selected' : '' }}>Ajouter aux soldes existants</option>
                                        <option value="soustraire" {{ old('operation') == 'soustraire' ? 'selected' : '' }}>Soustraire des soldes existants</option>
                                        <option value="definir" {{ old('operation') == 'definir' ? 'selected' : '' }}>Définir une nouvelle valeur</option>
                                    </select>
                                    @error('operation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="valeur" class="form-label">Valeur (jours) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('valeur') is-invalid @enderror" 
                                           id="valeur" name="valeur" value="{{ old('valeur') }}" 
                                           step="0.5" min="0" required>
                                    @error('valeur')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
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
                        </div>
                        
                        <div class="mb-3">
                            <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('motif') is-invalid @enderror" 
                                      id="motif" name="motif" rows="2" required>{{ old('motif') }}</textarea>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ignorer_existants" 
                                       name="ignorer_existants" value="1" {{ old('ignorer_existants') ? 'checked' : '' }}>
                                <label class="form-check-label" for="ignorer_existants">
                                    Ignorer les soldes existants (pour création uniquement)
                                </label>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Sélection des employés</h5>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selection_type" id="selection_tous" 
                                       value="tous" {{ old('selection_type', 'tous') == 'tous' ? 'checked' : '' }}>
                                <label class="form-check-label" for="selection_tous">
                                    Tous les employés de l'entreprise
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selection_type" id="selection_departement" 
                                       value="departement" {{ old('selection_type') == 'departement' ? 'checked' : '' }}>
                                <label class="form-check-label" for="selection_departement">
                                    Par département
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selection_type" id="selection_individuel" 
                                       value="individuel" {{ old('selection_type') == 'individuel' ? 'checked' : '' }}>
                                <label class="form-check-label" for="selection_individuel">
                                    Sélection individuelle
                                </label>
                            </div>
                        </div>
                        
                        <div id="departement_selection" class="mb-3" style="display: none;">
                            <label for="departement_ids" class="form-label">Départements</label>
                            <select class="form-select" id="departement_ids" name="departement_ids[]" multiple>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" {{ in_array($departement->id, old('departement_ids', [])) ? 'selected' : '' }}>
                                        {{ $departement->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="user_selection" class="mb-3" style="display: none;">
                            <label for="user_ids" class="form-label">Employés</label>
                            <select class="form-select" id="user_ids" name="user_ids[]" multiple>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                        {{ $user->nom }} {{ $user->prenom }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-info" id="preview_info">
                            <i class="fas fa-info-circle me-2"></i> Sélectionnez une entreprise pour voir le nombre d'employés concernés.
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('conge.soldes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <div>
                                <button type="button" id="preview_button" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i> Aperçu
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Appliquer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'aperçu -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aperçu des modifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="preview_content">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p>Chargement de l'aperçu...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectionType = document.querySelectorAll('input[name="selection_type"]');
        const departementSelection = document.getElementById('departement_selection');
        const userSelection = document.getElementById('user_selection');
        const entrepriseSelect = document.getElementById('entreprise_id');
        const typeCongeSelect = document.getElementById('type_conge_id');
        const departementSelect = document.getElementById('departement_ids');
        const userSelect = document.getElementById('user_ids');
        const previewButton = document.getElementById('preview_button');
        const previewInfo = document.getElementById('preview_info');
        
        // Gestion de l'affichage des sélections
        function updateSelectionDisplay() {
            const selectedType = document.querySelector('input[name="selection_type"]:checked').value;
            
            departementSelection.style.display = selectedType === 'departement' ? 'block' : 'none';
            userSelection.style.display = selectedType === 'individuel' ? 'block' : 'none';
        }
        
        selectionType.forEach(radio => {
            radio.addEventListener('change', updateSelectionDisplay);
        });
        
        // Initialisation
        updateSelectionDisplay();
        
        // Filtrer les types de congé en fonction de l'entreprise sélectionnée
        entrepriseSelect.addEventListener('change', function() {
            const entrepriseId = this.value;
            
            // Réinitialiser les options
            typeCongeSelect.innerHTML = '<option value="">Sélectionnez un type de congé</option>';
            departementSelect.innerHTML = '';
            userSelect.innerHTML = '';
            
            if (entrepriseId) {
                // Charger les types de congé de l'entreprise
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
                
                // Charger les départements de l'entreprise
                fetch(`/api/entreprises/${entrepriseId}/departements`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.id;
                            option.textContent = dept.nom;
                            departementSelect.appendChild(option);
                        });
                    });
                
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
                
                // Mettre à jour l'info de prévisualisation
                fetch(`/api/entreprises/${entrepriseId}/users/count`)
                    .then(response => response.json())
                    .then(data => {
                        previewInfo.innerHTML = `<i class="fas fa-info-circle me-2"></i> Cette opération concernera <strong>${data.count}</strong> employés.`;
                    });
            } else {
                previewInfo.innerHTML = `<i class="fas fa-info-circle me-2"></i> Sélectionnez une entreprise pour voir le nombre d'employés concernés.`;
            }
        });
        
        // Aperçu des modifications
        previewButton.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
            
            const formData = new FormData(document.querySelector('form'));
            formData.append('preview', 'true');
            
            fetch('{{ route("conge.soldes.bulk.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('preview_content').innerHTML = data.html;
            })
            .catch(error => {
                document.getElementById('preview_content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> Une erreur est survenue lors de la génération de l'aperçu.
                    </div>
                `;
            });
        });
    });
</script>
@endsection
