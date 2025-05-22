@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h6>Liste des utilisateurs</h6>
                    </div>
                </div>
                
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Filtres -->
                    <div class="p-4">
                        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="entreprise_id" class="form-label">Entreprise</label>
                                <select name="entreprise_id" id="entreprise_id" class="form-select">
                                    <option value="">Toutes les entreprises</option>
                                    @foreach($entreprises as $entreprise)
                                        <option value="{{ $entreprise->id }}" {{ request('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                            {{ $entreprise->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select name="role" id="role" class="form-select">
                                    <option value="">Tous les rôles</option>
                                    @foreach($roles as $roleOption)
                                        <option value="{{ $roleOption }}" {{ request('role') == $roleOption ? 'selected' : '' }}>
                                            {{ ucfirst($roleOption) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Nom, email ou téléphone" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tableau des utilisateurs -->
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Utilisateur</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Entreprise</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rôle</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Statut</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <img src="{{ $user->profile_photo_url ?? asset('assets/img/default-avatar.png') }}" class="avatar avatar-sm me-3" alt="{{ $user->name }}">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                @if($user->phone)
                                                    <p class="text-xs text-secondary mb-0">{{ $user->phone }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->entreprise ? $user->entreprise->nom : 'Aucune entreprise' }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ ucfirst($user->role) }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }}">
                                            {{ $user->email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        @if(Auth::user()->id !== $user->id && (Auth::user()->isSuperAdmin() || Auth::user()->isSupport()))
                                            <a href="{{ route('impersonate', $user->id) }}" class="btn btn-sm btn-info" 
                                               onclick="return confirm('Voulez-vous vraiment vous connecter en tant que {{ $user->name }} ?')">
                                                Se connecter en tant que
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
