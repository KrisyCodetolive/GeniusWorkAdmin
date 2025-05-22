@extends('layouts.app')

@section('title', 'Créer un abonnement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle mr-2"></i> Créer un abonnement - {{ $entreprise->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('abonnements.index', $entreprise) }}" class="btn btn-light">
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

                    <form action="{{ route('abonnements.store', $entreprise) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5>Sélectionnez un plan d'abonnement</h5>
                                <div class="row mt-3">
                                    @foreach ($plansAbonnement as $plan)
                                        <div class="col-md-4 mb-4">
                                            <div class="card h-100 {{ $plan->priorite === 2 ? 'border-primary' : '' }}">
                                                @if ($plan->priorite === 2)
                                                    <div class="card-header bg-primary text-white text-center">
                                                        <span class="badge badge-light">RECOMMANDÉ</span>
                                                    </div>
                                                @else
                                                    <div class="card-header {{ $plan->priorite === 3 ? 'bg-success text-white' : 'bg-light' }} text-center">
                                                        {{ $plan->nom }}
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h5 class="card-title text-center {{ $plan->priorite === 2 ? 'text-primary' : '' }}">
                                                        {{ $plan->priorite === 2 ? $plan->nom : '' }}
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
                                                        <input class="form-check-input" type="radio" name="plan_abonnement_id" id="plan_{{ $plan->id }}" value="{{ $plan->id }}" {{ old('plan_abonnement_id') == $plan->id ? 'checked' : ($plan->priorite === 2 ? 'checked' : '') }}>
                                                        <label class="form-check-label" for="plan_{{ $plan->id }}">
                                                            Sélectionner ce plan
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
                                        <h5 class="mb-0">Informations de l'abonnement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="date_debut">Date de début</label>
                                            <input type="date" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut" value="{{ old('date_debut', now()->format('Y-m-d')) }}">
                                            @error('date_debut')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Type de période</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_mensuel" value="mensuel" {{ old('type_periode') === 'mensuel' ? 'checked' : 'checked' }}>
                                                <label class="form-check-label" for="type_periode_mensuel">
                                                    Mensuel
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_annuel" value="annuel" {{ old('type_periode') === 'annuel' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_annuel">
                                                    Annuel
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type_periode" id="type_periode_essai" value="essai" {{ old('type_periode') === 'essai' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_periode_essai">
                                                    Période d'essai
                                                </label>
                                            </div>
                                            @error('type_periode')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group" id="duree_essai_group" style="display: none;">
                                            <label for="duree_essai">Durée de l'essai (jours)</label>
                                            <input type="number" class="form-control @error('duree_essai') is-invalid @enderror" id="duree_essai" name="duree_essai" value="{{ old('duree_essai', 14) }}" min="1" max="30">
                                            @error('duree_essai')
                                                <div class="invalid-feedback">{{ $message }}</div>
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
                                                <option value="carte" {{ old('mode_paiement') === 'carte' ? 'selected' : 'selected' }}>Carte bancaire</option>
                                                <option value="virement" {{ old('mode_paiement') === 'virement' ? 'selected' : '' }}>Virement bancaire</option>
                                                <option value="cheque" {{ old('mode_paiement') === 'cheque' ? 'selected' : '' }}>Chèque</option>
                                                <option value="especes" {{ old('mode_paiement') === 'especes' ? 'selected' : '' }}>Espèces</option>
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
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="renouvellement_automatique" name="renouvellement_automatique" value="1" {{ old('renouvellement_automatique') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="renouvellement_automatique">
                                                    Activer le renouvellement automatique
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="facture_automatique" name="facture_automatique" value="1" {{ old('facture_automatique', '1') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="facture_automatique">
                                                    Générer automatiquement les factures
                                                </label>
                                            </div>
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
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save mr-2"></i> Créer l'abonnement
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typePeriodeEssai = document.getElementById('type_periode_essai');
        const dureeEssaiGroup = document.getElementById('duree_essai_group');
        
        function toggleDureeEssai() {
            dureeEssaiGroup.style.display = typePeriodeEssai.checked ? 'block' : 'none';
        }
        
        // Initial check
        toggleDureeEssai();
        
        // Add event listener
        typePeriodeEssai.addEventListener('change', toggleDureeEssai);
        document.getElementById('type_periode_mensuel').addEventListener('change', toggleDureeEssai);
        document.getElementById('type_periode_annuel').addEventListener('change', toggleDureeEssai);
    });
</script>
@endpush
@endsection
