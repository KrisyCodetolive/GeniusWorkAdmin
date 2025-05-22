@extends('layouts.app')

@section('title', 'Renouveler l\'abonnement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-sync mr-2"></i> Renouveler l'abonnement - {{ $entreprise->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('abonnements.show', [$entreprise, $abonnement]) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5 class="alert-heading">Informations sur l'abonnement actuel</h5>
                        <p>
                            <strong>Plan :</strong> {{ $abonnement->planAbonnement->nom }}<br>
                            <strong>Période :</strong> {{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : 'Annuelle' }}<br>
                            <strong>Date de début :</strong> {{ $abonnement->date_debut->format('d/m/Y') }}<br>
                            <strong>Date de fin :</strong> {{ $abonnement->date_fin->format('d/m/Y') }}<br>
                            <strong>Montant :</strong> {{ $abonnement->getMontantFormate() }}
                        </p>
                    </div>

                    <form action="{{ route('abonnements.renouveler', [$entreprise, $abonnement]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Informations de renouvellement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="date_debut">Date de début du renouvellement</label>
                                            <input type="date" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut" value="{{ old('date_debut', $abonnement->date_fin->format('Y-m-d')) }}">
                                            <small class="form-text text-muted">Par défaut, le renouvellement commence à la date de fin de l'abonnement actuel.</small>
                                            @error('date_debut')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Type de période</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_mensuel" value="mensuel" {{ old('type_periode', $abonnement->type_periode) === 'mensuel' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_mensuel">
                                                    Mensuel ({{ $abonnement->planAbonnement->getPrixMensuelFormate() }})
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_annuel" value="annuel" {{ old('type_periode', $abonnement->type_periode) === 'annuel' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_annuel">
                                                    Annuel ({{ $abonnement->planAbonnement->getPrixAnnuelFormate() }})
                                                </label>
                                            </div>
                                            @error('type_periode')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="code_promo">Code promo (optionnel)</label>
                                            <input type="text" class="form-control @error('code_promo') is-invalid @enderror" id="code_promo" name="code_promo" value="{{ old('code_promo') }}">
                                            @error('code_promo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Informations de paiement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="mode_paiement">Mode de paiement</label>
                                            <select class="form-control @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement">
                                                <option value="carte" {{ old('mode_paiement', $abonnement->mode_paiement) === 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                                                <option value="virement" {{ old('mode_paiement', $abonnement->mode_paiement) === 'virement' ? 'selected' : '' }}>Virement bancaire</option>
                                                <option value="cheque" {{ old('mode_paiement', $abonnement->mode_paiement) === 'cheque' ? 'selected' : '' }}>Chèque</option>
                                                <option value="especes" {{ old('mode_paiement', $abonnement->mode_paiement) === 'especes' ? 'selected' : '' }}>Espèces</option>
                                            </select>
                                            @error('mode_paiement')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reference_paiement">Référence de paiement (optionnel)</label>
                                            <input type="text" class="form-control @error('reference_paiement') is-invalid @enderror" id="reference_paiement" name="reference_paiement" value="{{ old('reference_paiement') }}">
                                            @error('reference_paiement')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="notes">Notes (optionnel)</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Important</h5>
                            <p>
                                Le renouvellement de l'abonnement prolongera l'accès aux fonctionnalités du plan {{ $abonnement->planAbonnement->nom }}.
                                Si vous souhaitez changer de plan, veuillez utiliser l'option "Changer de plan" à la place.
                            </p>
                            <p>
                                Le montant sera calculé en fonction du type de période choisi et des éventuelles réductions applicables.
                            </p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sync mr-2"></i> Renouveler l'abonnement
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
