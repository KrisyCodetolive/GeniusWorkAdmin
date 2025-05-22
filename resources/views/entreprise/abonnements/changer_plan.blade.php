@extends('layouts.app')

@section('title', 'Changer de plan d\'abonnement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-exchange-alt mr-2"></i> Changer de plan d'abonnement - {{ $entreprise->nom }}
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
                        <h5 class="alert-heading">Plan actuel</h5>
                        <p>
                            <strong>Plan :</strong> {{ $abonnement->planAbonnement->nom }}<br>
                            <strong>Période :</strong> {{ $abonnement->type_periode === 'mensuel' ? 'Mensuelle' : 'Annuelle' }}<br>
                            <strong>Montant :</strong> {{ $abonnement->getMontantFormate() }}<br>
                            <strong>Date de fin :</strong> {{ $abonnement->date_fin->format('d/m/Y') }}
                        </p>
                    </div>

                    <form action="{{ route('abonnements.changer-plan', [$entreprise, $abonnement]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5>Sélectionnez un nouveau plan d'abonnement</h5>
                                <div class="row mt-3">
                                    @foreach ($plansAbonnement as $plan)
                                        <div class="col-md-4 mb-4">
                                            <div class="card h-100 {{ $plan->id === $abonnement->plan_abonnement_id ? 'border-success' : ($plan->priorite === 2 ? 'border-primary' : '') }}">
                                                @if ($plan->id === $abonnement->plan_abonnement_id)
                                                    <div class="card-header bg-success text-white text-center">
                                                        <span class="badge badge-light">PLAN ACTUEL</span>
                                                    </div>
                                                @elseif ($plan->priorite === 2)
                                                    <div class="card-header bg-primary text-white text-center">
                                                        <span class="badge badge-light">RECOMMANDÉ</span>
                                                    </div>
                                                @else
                                                    <div class="card-header {{ $plan->priorite === 3 ? 'bg-info text-white' : 'bg-light' }} text-center">
                                                        {{ $plan->nom }}
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h5 class="card-title text-center {{ $plan->id === $abonnement->plan_abonnement_id ? 'text-success' : ($plan->priorite === 2 ? 'text-primary' : '') }}">
                                                        {{ $plan->id === $abonnement->plan_abonnement_id ? $plan->nom . ' (Actuel)' : $plan->nom }}
                                                    </h5>
                                                    <div class="text-center mb-3">
                                                        <h3>{{ $plan->getPrixMensuelFormate() }}<small>/mois</small></h3>
                                                        <p>ou {{ $plan->getPrixAnnuelFormate() }}/an</p>
                                                        <p class="text-success">Économisez {{ number_format($plan->getEconomieAnnuelle(), 2) }} {{ $plan->devise }} avec l'abonnement annuel</p>
                                                    </div>
                                                    <ul class="list-group list-group-flush mb-3">
                                                        @foreach ($plan->fonctionnalites as $fonctionnalite)
                                                            <li class="list-group-item">
                                                                <i class="fas fa-check-circle text-success mr-2"></i> {{ $fonctionnalite }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    <div class="form-check text-center">
                                                        <input class="form-check-input" type="radio" name="plan_abonnement_id" id="plan_{{ $plan->id }}" value="{{ $plan->id }}" {{ old('plan_abonnement_id') == $plan->id ? 'checked' : ($plan->id === $abonnement->plan_abonnement_id ? 'checked' : '') }}>
                                                        <label class="form-check-label" for="plan_{{ $plan->id }}">
                                                            {{ $plan->id === $abonnement->plan_abonnement_id ? 'Conserver ce plan' : 'Sélectionner ce plan' }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('plan_abonnement_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Options de changement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Type de période</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_mensuel" value="mensuel" {{ old('type_periode', $abonnement->type_periode) === 'mensuel' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_mensuel">
                                                    Mensuel
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_annuel" value="annuel" {{ old('type_periode', $abonnement->type_periode) === 'annuel' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_annuel">
                                                    Annuel
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
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Important</h5>
                            <p>
                                Le changement de plan prendra effet immédiatement. Si vous passez à un plan supérieur, vous bénéficierez immédiatement des fonctionnalités supplémentaires.
                                Si vous passez à un plan inférieur, certaines fonctionnalités pourraient ne plus être disponibles.
                            </p>
                            <p>
                                Le montant sera ajusté en fonction du nouveau plan et de la période choisie.
                            </p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Êtes-vous sûr de vouloir changer de plan d\'abonnement ?')">
                                    <i class="fas fa-exchange-alt mr-2"></i> Changer de plan
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
