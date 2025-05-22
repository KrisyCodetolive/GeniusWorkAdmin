@extends('layouts.app')

@section('title', 'Détails du type de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i> Détails du type de congé: {{ $typeConge->nom }}
                    </h5>
                    <div>
                        @can('update', $typeConge)
                        <a href="{{ route('conge.types.edit', $typeConge) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informations générales</h6>
                            
                            <div class="mb-3">
                                <strong>Entreprise:</strong>
                                <p>{{ $typeConge->entreprise->nom }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Nom:</strong>
                                <p>{{ $typeConge->nom }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p>{{ $typeConge->description ?: 'Aucune description' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Statut:</strong>
                                <p>
                                    @if($typeConge->statut == 'actif')
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Configuration</h6>
                            
                            <div class="mb-3">
                                <strong>Durée maximale annuelle:</strong>
                                <p>{{ $typeConge->duree_max_annuelle ? $typeConge->duree_max_annuelle . ' jours' : 'Non limité' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Délai de demande préalable:</strong>
                                <p>{{ $typeConge->delai_demande_prealable ? $typeConge->delai_demande_prealable . ' jours' : 'Aucun' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Options:</strong>
                                <ul class="list-unstyled">
                                    <li>
                                        <i class="fas {{ $typeConge->est_paye ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                        Congé payé
                                    </li>
                                    <li>
                                        <i class="fas {{ $typeConge->deductible_solde ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                        Déductible du solde
                                    </li>
                                    <li>
                                        <i class="fas {{ $typeConge->necessite_justificatif ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                                        Nécessite un justificatif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Configuration avancée</h6>
                            
                            <div class="mb-3">
                                <strong>Conditions d'éligibilité:</strong>
                                <pre class="bg-light p-3 rounded mt-2">{{ json_encode($typeConge->conditions_eligibilite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Configuration:</strong>
                                <pre class="bg-light p-3 rounded mt-2">{{ json_encode($typeConge->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">Statistiques d'utilisation</h6>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['total_conges'] }}</h3>
                                            <p class="text-muted mb-0">Demandes de congé</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['total_jours'] }}</h3>
                                            <p class="text-muted mb-0">Jours demandés</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3">
                                        <div class="card-body text-center">
                                            <h3 class="mb-0">{{ $stats['utilisateurs_uniques'] }}</h3>
                                            <p class="text-muted mb-0">Utilisateurs uniques</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($stats['total_conges'] > 0)
                            <div class="mt-3">
                                <h6>Répartition par statut</h6>
                                <div class="progress" style="height: 25px;">
                                    @foreach($stats['par_statut'] as $statut => $pourcentage)
                                        <div class="progress-bar bg-{{ $statut == 'approuve' ? 'success' : ($statut == 'en_attente' ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $pourcentage }}%;" 
                                             aria-valuenow="{{ $pourcentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"
                                             title="{{ ucfirst(str_replace('_', ' ', $statut)) }}: {{ $pourcentage }}%">
                                            {{ $pourcentage > 5 ? ucfirst(str_replace('_', ' ', $statut)) . ' ' . $pourcentage . '%' : '' }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('conge.types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                        </a>
                        
                        <div>
                            @can('update', $typeConge)
                            <form action="{{ route('conge.types.toggle-statut', $typeConge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn {{ $typeConge->statut == 'actif' ? 'btn-secondary' : 'btn-success' }}">
                                    <i class="fas {{ $typeConge->statut == 'actif' ? 'fa-toggle-off' : 'fa-toggle-on' }} me-1"></i>
                                    {{ $typeConge->statut == 'actif' ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            @endcan
                            
                            @can('delete', $typeConge)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-1"></i> Supprimer
                            </button>
                            
                            <!-- Modal de suppression -->
                            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Supprimer le type de congé</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Êtes-vous sûr de vouloir supprimer le type de congé <strong>{{ $typeConge->nom }}</strong> ?</p>
                                            <p class="text-danger">Cette action est irréversible et supprimera définitivement ce type de congé.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <form action="{{ route('conge.types.destroy', $typeConge) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
