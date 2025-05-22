@extends('layouts.app')

@section('title', 'Détails de l\'abonnement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-credit-card mr-2"></i> Détails de l'abonnement - {{ $entreprise->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('abonnements.index', $entreprise) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Plan :</div>
                                        <div class="col-md-8">{{ $abonnement->planAbonnement->nom }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Description :</div>
                                        <div class="col-md-8">{{ $abonnement->planAbonnement->description }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Type de période :</div>
                                        <div class="col-md-8">
                                            {{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : ($abonnement->type_periode === 'annuel' ? 'Annuelle' : 'Essai') }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Statut :</div>
                                        <div class="col-md-8">
                                            @if ($abonnement->statut === 'actif')
                                                <span class="badge badge-success">Actif</span>
                                            @elseif ($abonnement->statut === 'inactif')
                                                <span class="badge badge-secondary">Inactif</span>
                                            @endif
                                            @if ($abonnement->isExpire())
                                                <span class="badge badge-danger">Expiré</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Date de début :</div>
                                        <div class="col-md-8">{{ $abonnement->date_debut->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Date de fin :</div>
                                        <div class="col-md-8">{{ $abonnement->date_fin->format('d/m/Y H:i') }}</div>
                                    </div>
                                    @if ($abonnement->isActif())
                                        <div class="row mb-2">
                                            <div class="col-md-4 font-weight-bold">Jours restants :</div>
                                            <div class="col-md-8">{{ $abonnement->getDureeRestante() }} jours</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Informations de paiement</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Montant :</div>
                                        <div class="col-md-8">{{ $abonnement->getMontantFormate() }}</div>
                                    </div>
                                    @if ($abonnement->reduction_code_promo > 0)
                                        <div class="row mb-2">
                                            <div class="col-md-4 font-weight-bold">Code promo :</div>
                                            <div class="col-md-8">
                                                {{ $abonnement->codePromo ? $abonnement->codePromo->code : 'N/A' }}
                                                (- {{ number_format($abonnement->reduction_code_promo, 2) }} {{ $abonnement->planAbonnement->devise }})
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Mode de paiement :</div>
                                        <div class="col-md-8">{{ $abonnement->mode_paiement ?? 'N/A' }}</div>
                                    </div>
                                    @if ($abonnement->reference_paiement)
                                        <div class="row mb-2">
                                            <div class="col-md-4 font-weight-bold">Référence :</div>
                                            <div class="col-md-8">{{ $abonnement->reference_paiement }}</div>
                                        </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Renouvellement auto :</div>
                                        <div class="col-md-8">
                                            @if ($abonnement->renouvellement_automatique)
                                                <span class="badge badge-success">Activé</span>
                                            @else
                                                <span class="badge badge-secondary">Désactivé</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">Facturation auto :</div>
                                        <div class="col-md-8">
                                            @if ($abonnement->facture_automatique)
                                                <span class="badge badge-success">Activée</span>
                                            @else
                                                <span class="badge badge-secondary">Désactivée</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($abonnement->notes)
                                        <div class="row mb-2">
                                            <div class="col-md-4 font-weight-bold">Notes :</div>
                                            <div class="col-md-8">{{ $abonnement->notes }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Fonctionnalités incluses</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($abonnement->planAbonnement->fonctionnalites as $fonctionnalite)
                                    <div class="col-md-4 mb-2">
                                        <i class="fas fa-check-circle text-success mr-2"></i> {{ $fonctionnalite }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    @if ($abonnement->facturations->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">Historique des facturations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Référence</th>
                                                <th>Montant</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($abonnement->facturations as $facturation)
                                                <tr>
                                                    <td>{{ $facturation->date_facturation->format('d/m/Y') }}</td>
                                                    <td>{{ $facturation->reference }}</td>
                                                    <td>{{ number_format($facturation->montant, 2) }} {{ $facturation->devise }}</td>
                                                    <td>
                                                        @if ($facturation->statut === 'payée')
                                                            <span class="badge badge-success">Payée</span>
                                                        @elseif ($facturation->statut === 'en_attente')
                                                            <span class="badge badge-warning">En attente</span>
                                                        @elseif ($facturation->statut === 'annulée')
                                                            <span class="badge badge-danger">Annulée</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file-pdf"></i> Télécharger
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="btn-group">
                                @can('update', [$abonnement, $entreprise])
                                    <a href="{{ route('abonnements.edit', [$entreprise, $abonnement]) }}" class="btn btn-primary mr-2">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    
                                    @if ($abonnement->statut === 'actif')
                                        <form action="{{ route('abonnements.desactiver', [$entreprise, $abonnement]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-warning mr-2" onclick="return confirm('Êtes-vous sûr de vouloir désactiver cet abonnement ?')">
                                                <i class="fas fa-power-off"></i> Désactiver
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('abonnements.activer', [$entreprise, $abonnement]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success mr-2" onclick="return confirm('Êtes-vous sûr de vouloir activer cet abonnement ?')">
                                                <i class="fas fa-power-off"></i> Activer
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('abonnements.renouveler.form', [$entreprise, $abonnement]) }}" class="btn btn-info mr-2">
                                        <i class="fas fa-sync"></i> Renouveler
                                    </a>
                                    
                                    <a href="{{ route('abonnements.changer-plan.form', [$entreprise, $abonnement]) }}" class="btn btn-dark">
                                        <i class="fas fa-exchange-alt"></i> Changer de plan
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
