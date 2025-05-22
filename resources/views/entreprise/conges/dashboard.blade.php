@extends('layouts.app')

@section('title', 'Tableau de bord des congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord des congés
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Demandes en attente</h6>
                                            <h2 class="mb-0">{{ $stats['demandes_en_attente'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-clock fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('conge.validation') }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-check-circle me-1"></i> Valider les demandes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Congés ce mois-ci</h6>
                                            <h2 class="mb-0">{{ $stats['conges_mois_actuel'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('conge.calendrier') }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-calendar me-1"></i> Voir le calendrier
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Jours de congés ce mois</h6>
                                            <h2 class="mb-0">{{ $stats['jours_conges_mois'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-chart-bar fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('conge.soldes.index') }}" class="btn btn-sm btn-light">
                                            <i class="fas fa-calculator me-1"></i> Gérer les soldes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Demandes en attente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Demandes en attente de validation</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($demandesEnAttente) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employé</th>
                                                        <th>Type</th>
                                                        <th>Période</th>
                                                        <th>Durée</th>
                                                        <th>Date demande</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($demandesEnAttente as $demande)
                                                        <tr>
                                                            <td>{{ $demande->employeur->user->name }}</td>
                                                            <td>{{ $demande->typeConge->nom }}</td>
                                                            <td>
                                                                {{ $demande->date_debut->format('d/m/Y') }} au 
                                                                {{ $demande->date_fin->format('d/m/Y') }}
                                                            </td>
                                                            <td>{{ $demande->duree_jours }} jour(s)</td>
                                                            <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                                                            <td>
                                                                <a href="{{ route('conge.show', $demande) }}" class="btn btn-sm btn-info me-1">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-success me-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#approuverModal{{ $demande->id }}">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#rejeterModal{{ $demande->id }}">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                                
                                                                <!-- Modal d'approbation -->
                                                                <div class="modal fade" id="approuverModal{{ $demande->id }}" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <form action="{{ route('conge.approuver', $demande) }}" method="POST">
                                                                                @csrf
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Approuver la demande de congé</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Vous êtes sur le point d'approuver la demande de congé de <strong>{{ $demande->employeur->user->name }}</strong>.</p>
                                                                                    <div class="mb-3">
                                                                                        <label for="commentaire{{ $demande->id }}" class="form-label">Commentaire (optionnel)</label>
                                                                                        <textarea class="form-control" name="commentaire" id="commentaire{{ $demande->id }}" rows="3"></textarea>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                                    <button type="submit" class="btn btn-success">Approuver</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Modal de rejet -->
                                                                <div class="modal fade" id="rejeterModal{{ $demande->id }}" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <form action="{{ route('conge.rejeter', $demande) }}" method="POST">
                                                                                @csrf
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Rejeter la demande de congé</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <p>Vous êtes sur le point de rejeter la demande de congé de <strong>{{ $demande->employeur->user->name }}</strong>.</p>
                                                                                    <div class="mb-3">
                                                                                        <label for="commentaire_rejet{{ $demande->id }}" class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                                                                                        <textarea class="form-control" name="commentaire" id="commentaire_rejet{{ $demande->id }}" rows="3" required></textarea>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                                    <button type="submit" class="btn btn-danger">Rejeter</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            Aucune demande en attente de validation.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Congés du mois en cours -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Congés approuvés pour le mois en cours</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($congesMoisActuel) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employé</th>
                                                        <th>Type</th>
                                                        <th>Période</th>
                                                        <th>Durée</th>
                                                        <th>Validé par</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($congesMoisActuel as $conge)
                                                        <tr>
                                                            <td>{{ $conge->employeur->user->name }}</td>
                                                            <td>{{ $conge->typeConge->nom }}</td>
                                                            <td>
                                                                {{ $conge->date_debut->format('d/m/Y') }} au 
                                                                {{ $conge->date_fin->format('d/m/Y') }}
                                                            </td>
                                                            <td>{{ $conge->duree_jours }} jour(s)</td>
                                                            <td>{{ $conge->validateur->name ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            Aucun congé approuvé pour le mois en cours.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
