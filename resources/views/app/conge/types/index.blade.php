@extends('layouts.app')

@section('title', 'Types de congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2"></i> Types de congés
                    </h5>
                    @can('manage', App\Models\TypeConge::class)
                    <a href="{{ route('conge.types.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-1"></i> Nouveau type
                    </a>
                    @endcan
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Durée max annuelle</th>
                                    <th>Payé</th>
                                    <th>Déductible</th>
                                    <th>Justificatif</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($typesConge as $typeConge)
                                    <tr>
                                        <td>{{ $typeConge->nom }}</td>
                                        <td>{{ Str::limit($typeConge->description, 50) }}</td>
                                        <td>{{ $typeConge->duree_max_annuelle ?? 'Non limité' }}</td>
                                        <td>
                                            @if($typeConge->est_paye)
                                                <span class="badge bg-success">Oui</span>
                                            @else
                                                <span class="badge bg-danger">Non</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($typeConge->deductible_solde)
                                                <span class="badge bg-success">Oui</span>
                                            @else
                                                <span class="badge bg-danger">Non</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($typeConge->necessite_justificatif)
                                                <span class="badge bg-success">Requis</span>
                                            @else
                                                <span class="badge bg-secondary">Non requis</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($typeConge->statut == 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('conge.types.show', $typeConge) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @can('update', $typeConge)
                                                <a href="{{ route('conge.types.edit', $typeConge) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('conge.types.toggle-statut', $typeConge) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $typeConge->statut == 'actif' ? 'btn-secondary' : 'btn-success' }}" 
                                                            title="{{ $typeConge->statut == 'actif' ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas {{ $typeConge->statut == 'actif' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                                
                                                @can('delete', $typeConge)
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $typeConge->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de suppression -->
                                                <div class="modal fade" id="deleteModal{{ $typeConge->id }}" tabindex="-1" aria-hidden="true">
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
                                                                <form action="{{ route('conge.types.destroy', $typeConge) }}" method="POST" class="d-inline">
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                Aucun type de congé n'a été défini.
                                                @can('manage', App\Models\TypeConge::class)
                                                <a href="{{ route('conge.types.create') }}" class="alert-link">Créer un type de congé</a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $typesConge->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
