@extends('layouts.app')

@section('title', 'Modifier un solde de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Modifier le solde de congé
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            @if($soldeConge->user->photo_url)
                                <img src="{{ asset($soldeConge->user->photo_url) }}" alt="Photo" class="rounded-circle me-3" style="width: 48px; height: 48px;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-size: 18px;">
                                    {{ substr($soldeConge->user->prenom, 0, 1) }}{{ substr($soldeConge->user->nom, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</h6>
                                <p class="mb-0 text-muted">{{ $soldeConge->typeConge->nom }} - {{ $soldeConge->annee }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('conge.soldes.update', $soldeConge) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Solde initial -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="solde_initial" class="form-label">Solde initial (jours) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('solde_initial') is-invalid @enderror" 
                                           id="solde_initial" name="solde_initial" value="{{ old('solde_initial', $soldeConge->solde_initial) }}" 
                                           step="0.5" min="0" required>
                                    @error('solde_initial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Solde utilisé -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="solde_utilise" class="form-label">Solde utilisé (jours) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('solde_utilise') is-invalid @enderror" 
                                           id="solde_utilise" name="solde_utilise" value="{{ old('solde_utilise', $soldeConge->solde_utilise) }}" 
                                           step="0.5" min="0" required>
                                    @error('solde_utilise')
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
                                           id="date_expiration" name="date_expiration" 
                                           value="{{ old('date_expiration', $soldeConge->date_expiration ? $soldeConge->date_expiration->format('Y-m-d') : '') }}">
                                    <div class="form-text">Laissez vide si pas de date d'expiration</div>
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Année -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="annee" class="form-label">Année <span class="text-danger">*</span></label>
                                    <select class="form-select @error('annee') is-invalid @enderror" id="annee" name="annee" required>
                                        @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                                            <option value="{{ $i }}" {{ old('annee', $soldeConge->annee) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('annee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Solde restant (calculé) -->
                        <div class="mb-3">
                            <label class="form-label">Solde restant (calculé)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="solde_restant" 
                                       value="{{ $soldeConge->solde_restant }} jours" readonly>
                                <span class="input-group-text">
                                    <span id="badge_solde" class="badge {{ $soldeConge->solde_restant > 5 ? 'bg-success' : ($soldeConge->solde_restant > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $soldeConge->solde_restant > 0 ? 'Disponible' : 'Épuisé' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $soldeConge->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Motif de modification -->
                        <div class="mb-3">
                            <label for="motif_modification" class="form-label">Motif de modification <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('motif_modification') is-invalid @enderror" 
                                      id="motif_modification" name="motif_modification" rows="2" required>{{ old('motif_modification') }}</textarea>
                            <div class="form-text">Ce motif sera enregistré dans l'historique des modifications</div>
                            @error('motif_modification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('conge.soldes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Historique des modifications -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i> Historique des modifications
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historique as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $item->user->nom }} {{ $item->user->prenom }}</td>
                                        <td>{{ $item->action }}</td>
                                        <td>{{ $item->details }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">Aucun historique disponible</td>
                                    </tr>
                                @endforelse
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const soldeInitialInput = document.getElementById('solde_initial');
        const soldeUtiliseInput = document.getElementById('solde_utilise');
        const soldeRestantInput = document.getElementById('solde_restant');
        const badgeSolde = document.getElementById('badge_solde');
        
        function updateSoldeRestant() {
            const soldeInitial = parseFloat(soldeInitialInput.value) || 0;
            const soldeUtilise = parseFloat(soldeUtiliseInput.value) || 0;
            const soldeRestant = Math.max(0, (soldeInitial - soldeUtilise)).toFixed(1);
            
            // Mettre à jour l'affichage du solde restant
            soldeRestantInput.value = `${soldeRestant} jours`;
            
            // Mettre à jour le badge
            badgeSolde.className = 'badge';
            if (soldeRestant > 5) {
                badgeSolde.classList.add('bg-success');
                badgeSolde.textContent = 'Disponible';
            } else if (soldeRestant > 0) {
                badgeSolde.classList.add('bg-warning');
                badgeSolde.textContent = 'Disponible';
            } else {
                badgeSolde.classList.add('bg-danger');
                badgeSolde.textContent = 'Épuisé';
            }
        }
        
        soldeInitialInput.addEventListener('input', updateSoldeRestant);
        soldeUtiliseInput.addEventListener('input', updateSoldeRestant);
    });
</script>
@endsection
