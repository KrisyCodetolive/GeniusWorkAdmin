@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>WebPointage - Tableau de bord
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Générez un QR code pour un site spécifique que les employés pourront scanner pour pointer leur présence.
                    </div>

                    <h5 class="mb-3">Sélectionnez un site pour générer un QR code</h5>
                    
                    <form action="{{ route('webPointage.qrcode.generate') }}" method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="site_id" class="form-label">Site</label>
                                <select name="site_id" id="site_id" class="form-select" required>
                                    <option value="">Sélectionnez un site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-qrcode me-2"></i>Générer QR Code
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Historique des pointages
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p>Consultez l'historique des pointages effectués via WebPointage.</p>
                                    <a href="{{ route('webPointage.historique') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-clock me-2"></i>Voir l'historique
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>Paramètres
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p>Configurez les paramètres du système de pointage.</p>
                                    <a href="{{ route('filament.admin.resources.presences.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-cogs me-2"></i>Gérer les présences
                                    </a>
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
