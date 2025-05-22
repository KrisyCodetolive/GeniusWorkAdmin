@extends('layouts.app')

@section('title', 'Gestion des soldes de congés')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i> Gestion des soldes de congés
                    </h5>
                    <div>
                        @can('create', App\Models\SoldeConge::class)
                        <a href="{{ route('conge.soldes.create') }}" class="btn btn-light me-2">
                            <i class="fas fa-plus-circle me-1"></i> Nouveau solde
                        </a>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>
                        @endcan
                    </div>
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

                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <form action="{{ route('conge.soldes.index') }}" method="GET" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="entreprise_id" class="form-label">Entreprise</label>
                                            <select class="form-select" id="entreprise_id" name="entreprise_id">
                                                <option value="">Toutes les entreprises</option>
                                                @foreach($entreprises as $entreprise)
                                                    <option value="{{ $entreprise->id }}" {{ request('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                                        {{ $entreprise->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="type_conge_id" class="form-label">Type de congé</label>
                                            <select class="form-select" id="type_conge_id" name="type_conge_id">
                                                <option value="">Tous les types</option>
                                                @foreach($typesConge as $typeConge)
                                                    <option value="{{ $typeConge->id }}" {{ request('type_conge_id') == $typeConge->id ? 'selected' : '' }}>
                                                        {{ $typeConge->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="annee" class="form-label">Année</label>
                                            <select class="form-select" id="annee" name="annee">
                                                <option value="">Toutes les années</option>
                                                @foreach($annees as $annee)
                                                    <option value="{{ $annee }}" {{ request('annee') == $annee ? 'selected' : '' }}>
                                                        {{ $annee }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="search" class="form-label">Recherche</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="search" name="search" 
                                                       placeholder="Nom, email..." value="{{ request('search') }}">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                                <a href="{{ route('conge.soldes.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employé</th>
                                    <th>Type de congé</th>
                                    <th>Année</th>
                                    <th>Solde initial</th>
                                    <th>Solde utilisé</th>
                                    <th>Solde restant</th>
                                    <th>Dernière mise à jour</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($soldesConge as $soldeConge)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($soldeConge->user->photo_url)
                                                    <img src="{{ asset($soldeConge->user->photo_url) }}" alt="Photo" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                        {{ substr($soldeConge->user->prenom, 0, 1) }}{{ substr($soldeConge->user->nom, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div>{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</div>
                                                    <small class="text-muted">{{ $soldeConge->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $soldeConge->typeConge->nom }}</td>
                                        <td>{{ $soldeConge->annee }}</td>
                                        <td>{{ $soldeConge->solde_initial }} jours</td>
                                        <td>{{ $soldeConge->solde_utilise }} jours</td>
                                        <td>
                                            <span class="badge {{ $soldeConge->solde_restant > 5 ? 'bg-success' : ($soldeConge->solde_restant > 0 ? 'bg-warning' : 'bg-danger') }} fs-6">
                                                {{ $soldeConge->solde_restant }} jours
                                            </span>
                                        </td>
                                        <td>{{ $soldeConge->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $soldeConge)
                                                <a href="{{ route('conge.soldes.show', $soldeConge) }}" class="btn btn-sm btn-info" title="Détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('update', $soldeConge)
                                                <a href="{{ route('conge.soldes.edit', $soldeConge) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#ajustementModal{{ $soldeConge->id }}"
                                                        title="Ajuster le solde">
                                                    <i class="fas fa-plus-minus"></i>
                                                </button>
                                                
                                                <!-- Modal d'ajustement -->
                                                <div class="modal fade" id="ajustementModal{{ $soldeConge->id }}" tabindex="-1" aria-hidden="true">
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
                                                                        <label for="operation{{ $soldeConge->id }}" class="form-label">Opération</label>
                                                                        <select class="form-select" id="operation{{ $soldeConge->id }}" name="operation" required>
                                                                            <option value="ajouter">Ajouter au solde</option>
                                                                            <option value="soustraire">Soustraire du solde</option>
                                                                            <option value="definir">Définir une nouvelle valeur</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="valeur{{ $soldeConge->id }}" class="form-label">Valeur (jours)</label>
                                                                        <input type="number" class="form-control" id="valeur{{ $soldeConge->id }}" 
                                                                               name="valeur" step="0.5" min="0" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="motif{{ $soldeConge->id }}" class="form-label">Motif</label>
                                                                        <textarea class="form-control" id="motif{{ $soldeConge->id }}" name="motif" rows="2" required></textarea>
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
                                                @endcan
                                                
                                                @can('delete', $soldeConge)
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $soldeConge->id }}"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de suppression -->
                                                <div class="modal fade" id="deleteModal{{ $soldeConge->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Supprimer le solde de congé</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Êtes-vous sûr de vouloir supprimer ce solde de congé pour <strong>{{ $soldeConge->user->nom }} {{ $soldeConge->user->prenom }}</strong> ?</p>
                                                                <p class="text-danger">Cette action est irréversible.</p>
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
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                Aucun solde de congé trouvé.
                                                @can('create', App\Models\SoldeConge::class)
                                                <a href="{{ route('conge.soldes.create') }}" class="alert-link">Créer un solde de congé</a>
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
                        {{ $soldesConge->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer des soldes de congés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('conge.soldes.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="entreprise_id_import" class="form-label">Entreprise <span class="text-danger">*</span></label>
                        <select class="form-select" id="entreprise_id_import" name="entreprise_id" required>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}">{{ $entreprise->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type_conge_id_import" class="form-label">Type de congé <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_conge_id_import" name="type_conge_id" required>
                            @foreach($typesConge as $typeConge)
                                <option value="{{ $typeConge->id }}">{{ $typeConge->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="annee_import" class="form-label">Année <span class="text-danger">*</span></label>
                        <select class="form-select" id="annee_import" name="annee" required>
                            @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fichier_import" class="form-label">Fichier CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="fichier_import" name="fichier" accept=".csv" required>
                        <div class="form-text">
                            Format attendu: email,solde_initial
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ecraser_existant" name="ecraser_existant" value="1">
                            <label class="form-check-label" for="ecraser_existant">
                                Écraser les soldes existants
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Téléchargez un <a href="{{ route('conge.soldes.template') }}" class="alert-link">modèle de fichier CSV</a> pour l'import.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
