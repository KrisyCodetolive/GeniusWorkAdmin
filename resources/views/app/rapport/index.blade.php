@extends('layouts.app')

@section('title', 'Rapports et Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rapports et Analytics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Rapports de Présence -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">Rapports de Présence</h5>
                                </div>
                                <div class="card-body">
                                    <p>Générez des rapports détaillés sur les présences, absences et retards des employés.</p>
                                    <form action="{{ route('rapports.presences') }}" method="GET">
                                        <div class="form-group">
                                            <label for="date_debut">Date de début</label>
                                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="date_fin">Date de fin</label>
                                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="departement_id">Département</label>
                                            <select class="form-control" id="departement_id" name="departement_id">
                                                <option value="">Tous les départements</option>
                                                @foreach($departements as $departement)
                                                    <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="format">Format</label>
                                            <select class="form-control" id="format" name="format">
                                                <option value="html">Afficher à l'écran</option>
                                                <option value="pdf">Exporter en PDF</option>
                                                <option value="excel">Exporter en Excel</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Générer le rapport</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Rapports de Congés -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">Rapports de Congés</h5>
                                </div>
                                <div class="card-body">
                                    <p>Suivez les congés utilisés et restants, et analysez les tendances de congés.</p>
                                    <form action="{{ route('rapports.conges') }}" method="GET">
                                        <div class="form-group">
                                            <label for="date_debut">Date de début</label>
                                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ \Carbon\Carbon::now()->startOfYear()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="date_fin">Date de fin</label>
                                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="departement_id">Département</label>
                                            <select class="form-control" id="departement_id" name="departement_id">
                                                <option value="">Tous les départements</option>
                                                @foreach($departements as $departement)
                                                    <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="format">Format</label>
                                            <select class="form-control" id="format" name="format">
                                                <option value="html">Afficher à l'écran</option>
                                                <option value="pdf">Exporter en PDF</option>
                                                <option value="excel">Exporter en Excel</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block">Générer le rapport</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Rapports d'Heures Supplémentaires -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">Rapports d'Heures Supplémentaires</h5>
                                </div>
                                <div class="card-body">
                                    <p>Suivez les heures supplémentaires par employé et par département.</p>
                                    <form action="{{ route('rapports.supplementaires') }}" method="GET">
                                        <div class="form-group">
                                            <label for="date_debut">Date de début</label>
                                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="date_fin">Date de fin</label>
                                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="departement_id">Département</label>
                                            <select class="form-control" id="departement_id" name="departement_id">
                                                <option value="">Tous les départements</option>
                                                @foreach($departements as $departement)
                                                    <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="format">Format</label>
                                            <select class="form-control" id="format" name="format">
                                                <option value="html">Afficher à l'écran</option>
                                                <option value="pdf">Exporter en PDF</option>
                                                <option value="excel">Exporter en Excel</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-warning btn-block">Générer le rapport</button>
                                    </form>
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
