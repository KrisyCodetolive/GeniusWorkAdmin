@extends('layouts.entreprise')

@section('title', 'Tableau de bord entreprise')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Employés</p>
                                <h5 class="font-weight-bolder">
                                    {{ $entreprise->employeurs()->count() }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-success text-sm font-weight-bolder">Actifs</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fas fa-users text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Présences</p>
                                <h5 class="font-weight-bolder">
                                    {{ App\Models\Presence::whereHas('employeur', function($query) use ($entreprise) {
                                        $query->where('entreprise_id', $entreprise->id);
                                    })->whereDate('created_at', \Carbon\Carbon::today())->count() }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-success text-sm font-weight-bolder">Aujourd'hui</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="fas fa-check-circle text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Congés</p>
                                <h5 class="font-weight-bolder">
                                    {{ App\Models\Conge::whereHas('employeur', function($query) use ($entreprise) {
                                        $query->where('entreprise_id', $entreprise->id);
                                    })->where('statut', 'en_attente')->count() }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-warning text-sm font-weight-bolder">En attente</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="fas fa-calendar-alt text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Départements</p>
                                <h5 class="font-weight-bolder">
                                    {{ $entreprise->departements()->count() }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-info text-sm font-weight-bolder">Total</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                <i class="fas fa-building text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Présences récentes</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employé</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date/Heure</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $presences = App\Models\Presence::whereHas('employeur', function($query) use ($entreprise) {
                                        $query->where('entreprise_id', $entreprise->id);
                                    })->orderBy('created_at', 'desc')->take(5)->get();
                                @endphp
                                
                                @foreach($presences as $presence)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                @if($presence->employeur->user->profile_photo_path)
                                                    <img src="{{ Storage::url($presence->employeur->user->profile_photo_path) }}" class="avatar avatar-sm me-3" alt="user1">
                                                @else
                                                    <div class="avatar avatar-sm me-3 bg-gradient-primary">{{ substr($presence->employeur->user->name, 0, 1) }}</div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $presence->employeur->user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $presence->employeur->fonction }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $presence->type }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $presence->date_heure->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($presence->statut == 'validee')
                                            <span class="badge badge-sm bg-gradient-success">Validée</span>
                                        @elseif($presence->statut == 'en_attente')
                                            <span class="badge badge-sm bg-gradient-warning">En attente</span>
                                        @elseif($presence->statut == 'rejetee')
                                            <span class="badge badge-sm bg-gradient-danger">Rejetée</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Congés en attente</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Employé</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Période</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $conges = App\Models\Conge::whereHas('employeur', function($query) use ($entreprise) {
                                        $query->where('entreprise_id', $entreprise->id);
                                    })->where('statut', 'en_attente')->orderBy('created_at', 'desc')->take(5)->get();
                                @endphp
                                
                                @foreach($conges as $conge)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                @if($conge->employeur->user->profile_photo_path)
                                                    <img src="{{ Storage::url($conge->employeur->user->profile_photo_path) }}" class="avatar avatar-sm me-3" alt="user1">
                                                @else
                                                    <div class="avatar avatar-sm me-3 bg-gradient-primary">{{ substr($conge->employeur->user->name, 0, 1) }}</div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $conge->employeur->user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $conge->typeConge->nom }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $conge->date_debut->format('d/m/Y') }} - {{ $conge->date_fin->format('d/m/Y') }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $conge->duree_jours }} jours</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <form action="{{ route('entreprise.conges.valider', $conge->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Valider</button>
                                        </form>
                                        <form action="{{ route('entreprise.conges.rejeter', $conge->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Rejeter</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
