@if(session()->has('impersonate_origin_id'))
<div class="impersonation-banner">
    <div class="container-fluid">
        <div class="alert alert-warning mb-0 d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-user-secret me-2"></i>
                <strong>Mode Impersonation :</strong> Vous êtes connecté en tant que <strong>{{ Auth::user()->name }}</strong>
                @if(Auth::user()->entreprise)
                    de l'entreprise <strong>{{ Auth::user()->entreprise->nom }}</strong>
                @endif
            </div>
            <a href="{{ route('impersonate.stop') }}" class="btn btn-sm btn-danger">
                <i class="fas fa-sign-out-alt me-1"></i> Revenir à mon compte
            </a>
        </div>
    </div>
</div>

<style>
    .impersonation-banner {
        position: sticky;
        top: 0;
        z-index: 1040;
    }
</style>
@endif
