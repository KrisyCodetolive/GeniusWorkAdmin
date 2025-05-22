@extends('layouts.app')

@section('title', 'Tableau de bord des abonnements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord des abonnements
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

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Abonnements actifs</h6>
                                            <h2 class="mb-0">{{ $statistiques['total_actifs'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Abonnements expirés</h6>
                                            <h2 class="mb-0">{{ $statistiques['total_expires'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-times-circle fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Expirant bientôt</h6>
                                            <h2 class="mb-0">{{ $statistiques['expirant_bientot'] }}</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Revenus mensuels</h6>
                                            <h2 class="mb-0">{{ number_format($statistiques['revenus_mensuels'], 2) }} €</h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-euro-sign fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <ul class="nav nav-tabs" id="abonnementTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="actifs-tab" data-toggle="tab" href="#actifs" role="tab" aria-controls="actifs" aria-selected="true">
                                        <i class="fas fa-check-circle mr-1"></i> Abonnements actifs ({{ $abonnementsActifs->count() }})
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="expirant-tab" data-toggle="tab" href="#expirant" role="tab" aria-controls="expirant" aria-selected="false">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Expirant bientôt ({{ $abonnementsExpirantBientot->count() }})
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="expires-tab" data-toggle="tab" href="#expires" role="tab" aria-controls="expires" aria-selected="false">
                                        <i class="fas fa-times-circle mr-1"></i> Abonnements expirés ({{ $abonnementsExpires->count() }})
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content" id="abonnementTabsContent">
                                <div class="tab-pane fade show active" id="actifs" role="tabpanel" aria-labelledby="actifs-tab">
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Entreprise</th>
                                                    <th>Plan</th>
                                                    <th>Période</th>
                                                    <th>Date de début</th>
                                                    <th>Date de fin</th>
                                                    <th>Jours restants</th>
                                                    <th>Montant</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($abonnementsActifs as $abonnement)
                                                    <tr>
                                                        <td>{{ $abonnement->entreprise->nom }}</td>
                                                        <td>{{ $abonnement->planAbonnement->nom }}</td>
                                                        <td>{{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : ($abonnement->type_periode === 'annuel' ? 'Annuelle' : 'Essai') }}</td>
                                                        <td>{{ $abonnement->date_debut->format('d/m/Y') }}</td>
                                                        <td>{{ $abonnement->date_fin->format('d/m/Y') }}</td>
                                                        <td>{{ $abonnement->getDureeRestante() }}</td>
                                                        <td>{{ $abonnement->getMontantFormate() }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('abonnements.show', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('abonnements.edit', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="{{ route('abonnements.renouveler.form', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-sync"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Aucun abonnement actif trouvé</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="expirant" role="tabpanel" aria-labelledby="expirant-tab">
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Entreprise</th>
                                                    <th>Plan</th>
                                                    <th>Période</th>
                                                    <th>Date de début</th>
                                                    <th>Date de fin</th>
                                                    <th>Jours restants</th>
                                                    <th>Montant</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($abonnementsExpirantBientot as $abonnement)
                                                    <tr class="table-warning">
                                                        <td>{{ $abonnement->entreprise->nom }}</td>
                                                        <td>{{ $abonnement->planAbonnement->nom }}</td>
                                                        <td>{{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : ($abonnement->type_periode === 'annuel' ? 'Annuelle' : 'Essai') }}</td>
                                                        <td>{{ $abonnement->date_debut->format('d/m/Y') }}</td>
                                                        <td>{{ $abonnement->date_fin->format('d/m/Y') }}</td>
                                                        <td><strong class="text-danger">{{ $abonnement->getDureeRestante() }}</strong></td>
                                                        <td>{{ $abonnement->getMontantFormate() }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('abonnements.show', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('abonnements.renouveler.form', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-sync"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Aucun abonnement expirant bientôt trouvé</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="expires" role="tabpanel" aria-labelledby="expires-tab">
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Entreprise</th>
                                                    <th>Plan</th>
                                                    <th>Période</th>
                                                    <th>Date de début</th>
                                                    <th>Date de fin</th>
                                                    <th>Expiré depuis</th>
                                                    <th>Montant</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($abonnementsExpires as $abonnement)
                                                    <tr class="table-danger">
                                                        <td>{{ $abonnement->entreprise->nom }}</td>
                                                        <td>{{ $abonnement->planAbonnement->nom }}</td>
                                                        <td>{{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : ($abonnement->type_periode === 'annuel' ? 'Annuelle' : 'Essai') }}</td>
                                                        <td>{{ $abonnement->date_debut->format('d/m/Y') }}</td>
                                                        <td>{{ $abonnement->date_fin->format('d/m/Y') }}</td>
                                                        <td>{{ now()->diffInDays($abonnement->date_fin) }} jours</td>
                                                        <td>{{ $abonnement->getMontantFormate() }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="{{ route('abonnements.show', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('abonnements.renouveler.form', [$abonnement->entreprise, $abonnement]) }}" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-sync"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Aucun abonnement expiré trouvé</td>
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
            </div>
        </div>
    </div>
</div>
@endsection
