@extends('layouts.app')

@section('title', 'Détails de la demande de congé')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i> Détails de la demande de congé
                    </h5>
                    <span class="badge 
                        @if($conge->statut == 'en_attente') bg-warning
                        @elseif($conge->statut == 'approuve') bg-success
                        @elseif($conge->statut == 'rejete') bg-danger
                        @else bg-secondary
                        @endif
                        fs-6">
                        @if($conge->statut == 'en_attente') En attente
                        @elseif($conge->statut == 'approuve') Approuvé
                        @elseif($conge->statut == 'rejete') Rejeté
                        @else Annulé
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Informations générales</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Type de congé</th>
                                    <td>{{ $conge->typeConge->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Date de début</th>
                                    <td>{{ $conge->date_debut->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Date de fin</th>
                                    <td>{{ $conge->date_fin->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Durée</th>
                                    <td>{{ $conge->duree_jours }} jour(s) ouvrable(s)</td>
                                </tr>
                                <tr>
                                    <th>Congé payé</th>
                                    <td>{{ $conge->est_paye ? 'Oui' : 'Non' }}</td>
                                </tr>
                                <tr>
                                    <th>Date de demande</th>
                                    <td>{{ $conge->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Validation</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Statut</th>
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
                                </tr>
                                @if($conge->validateur)
                                <tr>
                                    <th>Validé par</th>
                                    <td>{{ $conge->validateur->name }}</td>
                                </tr>
                                <tr>
                                    <th>Date de validation</th>
                                    <td>{{ $conge->date_validation->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($conge->commentaire_validation)
                                <tr>
                                    <th>Commentaire</th>
                                    <td>{{ $conge->commentaire_validation }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($conge->motif)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Motif de la demande</h6>
                                </div>
                                <div class="card-body">
                                    {{ $conge->motif }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($conge->justificatif)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Justificatif</h6>
                                </div>
                                <div class="card-body">
                                    <a href="{{ Storage::url($conge->justificatif) }}" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-file-download me-1"></i> Télécharger le justificatif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('conge.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                        </a>
                        
                        @if($conge->statut == 'en_attente' || $conge->statut == 'approuve')
                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#annulerModal">
                            <i class="fas fa-times me-1"></i> Annuler cette demande
                        </button>
                        
                        <!-- Modal d'annulation -->
                        <div class="modal fade" id="annulerModal" tabindex="-1" aria-hidden="true">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
