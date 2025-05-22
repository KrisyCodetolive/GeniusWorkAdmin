@extends('layouts.app')

@section('title', 'Gestion des abonnements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card mr-2"></i> Gestion des abonnements - {{ $entreprise->nom }}
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($abonnementActif)
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="alert-heading">Abonnement actif</h5>
                                    <p>
                                        <strong>Plan :</strong> {{ $abonnementActif->planAbonnement->nom }}<br>
                                        <strong>Période :</strong> {{ $abonnementActif->type_periode === 'mensuel' ? 'Mensuelle' : 'Annuelle' }}<br>
                                        <strong>Date de début :</strong> {{ $abonnementActif->date_debut->format('d/m/Y') }}<br>
                                        <strong>Date de fin :</strong> {{ $abonnementActif->date_fin->format('d/m/Y') }}<br>
                                        <strong>Jours restants :</strong> {{ $abonnementActif->getDureeRestante() }} jours<br>
                                        <strong>Montant :</strong> {{ $abonnementActif->getMontantFormate() }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="{{ route('abonnements.show', [$entreprise, $abonnementActif]) }}" class="btn btn-info mb-2">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                    <a href="{{ route('abonnements.renouveler.form', [$entreprise, $abonnementActif]) }}" class="btn btn-success mb-2">
                                        <i class="fas fa-sync"></i> Renouveler
                                    </a>
                                    <a href="{{ route('abonnements.changer-plan.form', [$entreprise, $abonnementActif]) }}" class="btn btn-warning mb-2">
                                        <i class="fas fa-exchange-alt"></i> Changer de plan
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Aucun abonnement actif</h5>
                            <p>Cette entreprise n'a pas d'abonnement actif actuellement.</p>
                            @can('create', [App\Models\Abonnement::class, $entreprise])
                                <a href="{{ route('abonnements.create', $entreprise) }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Souscrire à un abonnement
                                </a>
                            @endcan
                        </div>
                    @endif

                    <h5 class="mt-4 mb-3">Historique des abonnements</h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Plan</th>
                                    <th>Période</th>
                                    <th>Date de début</th>
                                    <th>Date de fin</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($abonnements as $abonnement)
                                    <tr>
                                        <td>{{ $abonnement->planAbonnement->nom }}</td>
                                        <td>{{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : ($abonnement->type_periode === 'annuel' ? 'Annuelle' : 'Essai') }}</td>
                                        <td>{{ $abonnement->date_debut->format('d/m/Y') }}</td>
                                        <td>{{ $abonnement->date_fin->format('d/m/Y') }}</td>
                                        <td>{{ $abonnement->getMontantFormate() }}</td>
                                        <td>
                                            @if ($abonnement->statut === 'actif')
                                                <span class="badge badge-success">Actif</span>
                                            @elseif ($abonnement->statut === 'inactif')
                                                <span class="badge badge-secondary">Inactif</span>
                                            @endif
                                            @if ($abonnement->isExpire())
                                                <span class="badge badge-danger">Expiré</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('abonnements.show', [$entreprise, $abonnement]) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update', [$abonnement, $entreprise])
                                                    <a href="{{ route('abonnements.edit', [$entreprise, $abonnement]) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if ($abonnement->statut === 'actif')
                                                        <form action="{{ route('abonnements.desactiver', [$entreprise, $abonnement]) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cet abonnement ?')">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('abonnements.activer', [$entreprise, $abonnement]) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir activer cet abonnement ?')">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun abonnement trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('create', [App\Models\Abonnement::class, $entreprise])
                        <div class="mt-3">
                            <a href="{{ route('abonnements.create', $entreprise) }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Nouvel abonnement
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
