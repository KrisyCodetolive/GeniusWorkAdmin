@extends('layouts.app')

@section('title', 'Détails du solde de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i> Détails du solde de congé
                    </h5>
                    <div>
                        @can('update', $soldeConge)
                        <a href="{{ route('conge.soldes.edit', $soldeConge) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informations générales</h6>
                            
                            <!-- Utilisateur -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    @if($soldeConge->user->photo_url)
                                        <img src="{{ asset($soldeConge->user->photo_url) }}" alt="Photo" class="rounded-circle me-3" style="width: 64px; height: 64px;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px; font-size: 24px;">
                                            {{ substr($soldeConge->user->prenom, 0, 1) }}{{ substr($soldeConge->user->nom, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h5 class="mb-1">{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</h5>
                                        <p class="mb-0 text-muted">{{ $soldeConge->user->email }}</p>
                                        <p class="mb-0 text-muted">{{ $soldeConge->user->fonction ?? 'Fonction non spécifiée' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Entreprise:</strong>
                                <p>{{ $soldeConge->entreprise->nom }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Type de congé:</strong>
                                <p>{{ $soldeConge->typeConge->nom }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Année:</strong>
                                <p>{{ $soldeConge->annee }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Date d'expiration:</strong>
                                <p>{{ $soldeConge->date_expiration ? $soldeConge->date_expiration->format('d/m/Y') : 'Non définie' }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Soldes</h6>
                            
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6 class="text-muted mb-1">Solde initial</h6>
                                            <h4>{{ $soldeConge->solde_initial }}</h4>
                                            <small>jours</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted mb-1">Solde utilisé</h6>
                                            <h4>{{ $soldeConge->solde_utilise }}</h4>
                                            <small>jours</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted mb-1">Solde restant</h6>
                                            <h4 class="{{ $soldeConge->solde_restant > 5 ? 'text-success' : ($soldeConge->solde_restant > 0 ? 'text-warning' : 'text-danger') }}">
                                                {{ $soldeConge->solde_restant }}
                                            </h4>
                                            <small>jours</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Progression:</strong>
                                <div class="progress mt-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ ($soldeConge->solde_restant / $soldeConge->solde_initial) * 100 }}%;" 
                                         aria-valuenow="{{ $soldeConge->solde_restant }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="{{ $soldeConge->solde_initial }}">
                                        {{ $soldeConge->solde_restant }} / {{ $soldeConge->solde_initial }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Notes:</strong>
                                <p>{{ $soldeConge->notes ?: 'Aucune note' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Dernière mise à jour:</strong>
                                <p>{{ $soldeConge->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historique des congés -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Historique des congés</h6>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Période</th>
                                            <th>Durée</th>
                                            <th>Motif</th>
                                            <th>Statut</th>
                                            <th>Date de demande</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($conges as $conge)
                                            <tr>
                                                <td>
                                                    Du {{ $conge->date_debut->format('d/m/Y') }} au {{ $conge->date_fin->format('d/m/Y') }}
                                                </td>
                                                <td>{{ $conge->duree }} jours</td>
                                                <td>{{ $conge->motif }}</td>
                                                <td>
                                                    @if($conge->statut == 'approuve')
                                                        <span class="badge bg-success">Approuvé</span>
                                                    @elseif($conge->statut == 'en_attente')
                                                        <span class="badge bg-warning">En attente</span>
                                                    @elseif($conge->statut == 'refuse')
                                                        <span class="badge bg-danger">Refusé</span>
                                                    @elseif($conge->statut == 'annule')
                                                        <span class="badge bg-secondary">Annulé</span>
                                                    @endif
                                                </td>
                                                <td>{{ $conge->created_at->format('d/m/Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3">Aucun congé pris pour ce type</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historique des ajustements -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Historique des ajustements</h6>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Utilisateur</th>
                                            <th>Action</th>
                                            <th>Valeur</th>
                                            <th>Motif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ajustements as $ajustement)
                                            <tr>
                                                <td>{{ $ajustement->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $ajustement->user->nom }} {{ $ajustement->user->prenom }}</td>
                                                <td>{{ $ajustement->action }}</td>
                                                <td>{{ $ajustement->valeur }} jours</td>
                                                <td>{{ $ajustement->motif }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3">Aucun ajustement effectué</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('conge.soldes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                        </a>
                        
                        <div>
                            @can('update', $soldeConge)
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ajustementModal">
                                <i class="fas fa-plus-minus me-1"></i> Ajuster le solde
                            </button>
                            @endcan
                            
                            @can('delete', $soldeConge)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-1"></i> Supprimer
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'ajustement -->
<div class="modal fade" id="ajustementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajuster le solde de congé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('conge.soldes.ajuster', $soldeConge) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Ajustement pour: <strong>{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</strong></p>
                    <p>Type de congé: <strong>{{ $soldeConge->typeConge->nom }}</strong></p>
                    <p>Solde actuel: <strong>{{ $soldeConge->solde_restant }} jours</strong></p>
                    
                    <div class="mb-3">
                        <label for="operation" class="form-label">Opération</label>
                        <select class="form-select" id="operation" name="operation" required>
                            <option value="ajouter">Ajouter au solde</option>
                            <option value="soustraire">Soustraire du solde</option>
                            <option value="definir">Définir une nouvelle valeur</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valeur" class="form-label">Valeur (jours)</label>
                        <input type="number" class="form-control" id="valeur" 
                               name="valeur" step="0.5" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea class="form-control" id="motif" name="motif" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer le solde de congé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce solde de congé pour <strong>{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</strong> ?</p>
                <p class="text-danger">Cette action est irréversible et supprimera définitivement ce solde de congé.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('conge.soldes.destroy', $soldeConge) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
