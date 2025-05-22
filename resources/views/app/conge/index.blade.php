@extends('layouts.app')

@section('title', 'Gestion des congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i> Mes demandes de congés
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <a href="{{ route('conge.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> Nouvelle demande
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('conge.calendrier') }}" class="btn btn-info me-2">
                                <i class="fas fa-calendar me-1"></i> Calendrier
                            </a>
                            <a href="{{ route('conge.statistiques') }}" class="btn btn-secondary">
                                <i class="fas fa-chart-pie me-1"></i> Statistiques
                            </a>
                        </div>
                    </div>

                    <!-- Soldes de congés -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Mes soldes de congés pour {{ date('Y') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($soldes as $solde)
                                            <div class="col-md-3 mb-3">
                                                <div class="card h-100 {{ $solde->solde_restant > 0 ? 'border-success' : 'border-danger' }}">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">{{ $solde->typeConge->nom }}</h5>
                                                        <div class="display-6 mb-2 {{ $solde->solde_restant > 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($solde->solde_restant, 1) }} <small>jours</small>
                                                        </div>
                                                        <div class="text-muted small">
                                                            Initial: {{ number_format($solde->solde_initial, 1) }} | 
                                                            Pris: {{ number_format($solde->solde_pris, 1) }}
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

                    <!-- Liste des demandes -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Période</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                    <th>Date demande</th>
                                    <th>Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conges as $conge)
                                    <tr>
                                        <td>{{ $conge->typeConge->nom }}</td>
                                        <td>
                                            Du {{ $conge->date_debut->format('d/m/Y') }} 
                                            au {{ $conge->date_fin->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $conge->duree_jours }} jour(s)</td>
                                        <td>
                                            @if($conge->statut == 'en_attente')
                                                <span class="badge bg-warning">En attente</span>
                                            @elseif($conge->statut == 'approuve')
                                                <span class="badge bg-success">Approuvé</span>
                                            @elseif($conge->statut == 'rejete')
                                                <span class="badge bg-danger">Rejeté</span>
                                            @else
                                                <span class="badge bg-secondary">Annulé</span>
                                            @endif
                                        </td>
                                        <td>{{ $conge->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($conge->validateur)
                                                <small>
                                                    Par {{ $conge->validateur->name }} 
                                                    le {{ $conge->date_validation->format('d/m/Y') }}
                                                </small>
                                            @else
                                                <small class="text-muted">Non validé</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('conge.show', $conge) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($conge->statut == 'en_attente' || $conge->statut == 'approuve')
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#annulerModal{{ $conge->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                
                                                <!-- Modal d'annulation -->
                                                <div class="modal fade" id="annulerModal{{ $conge->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('conge.annuler', $conge) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Annuler la demande de congé</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Êtes-vous sûr de vouloir annuler cette demande de congé ?</p>
                                                                    <div class="mb-3">
                                                                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                                                        <textarea class="form-control" name="commentaire" id="commentaire" rows="3"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                                    <button type="submit" class="btn btn-danger">Annuler la demande</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                Vous n'avez pas encore fait de demande de congé.
                                                <a href="{{ route('conge.create') }}" class="alert-link">Créer une demande</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $conges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Script pour afficher les tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection
