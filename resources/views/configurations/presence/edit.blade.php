@extends('layouts.app')

@section('title', 'Configuration des présences')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Configuration des présences</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Configuration des présences</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-cogs me-1"></i>
                Configuration des présences pour {{ $entreprise->nom }}
            </div>
            <form action="{{ route('configurations.presence.reset', $entreprise) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser la configuration aux valeurs par défaut ?')">
                    <i class="fas fa-undo"></i> Réinitialiser aux valeurs par défaut
                </button>
            </form>
        </div>
        <div class="card-body">
            <form action="{{ route('configurations.presence.update', $entreprise) }}" method="POST">
                @csrf
                @method('PUT')
                
                <ul class="nav nav-tabs" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            Général
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pointages-tab" data-bs-toggle="tab" data-bs-target="#pointages" type="button" role="tab" aria-controls="pointages" aria-selected="false">
                            Pointages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                            Notifications
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-4" id="configTabsContent">
                    <!-- Onglet Général -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="heures_supplementaires_actives" name="heures_supplementaires_actives" value="1" {{ $configuration->heures_supplementaires_actives ? 'checked' : '' }}>
                                    <label class="form-check-label" for="heures_supplementaires_actives">
                                        Activer les heures supplémentaires
                                    </label>
                                </div>
                                <div class="form-text text-muted mb-3">
                                    Lorsque cette option est activée, le système calculera automatiquement les heures supplémentaires en fonction des plages horaires définies.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Pointages -->
                    <div class="tab-pane fade" id="pointages" role="tabpanel" aria-labelledby="pointages-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre_pointages_par_jour" class="form-label">Nombre de pointages par jour</label>
                                    <select class="form-select @error('nombre_pointages_par_jour') is-invalid @enderror" id="nombre_pointages_par_jour" name="nombre_pointages_par_jour">
                                        <option value="2" {{ $configuration->nombre_pointages_par_jour == 2 ? 'selected' : '' }}>2 (Entrée/Sortie)</option>
                                        <option value="4" {{ $configuration->nombre_pointages_par_jour == 4 ? 'selected' : '' }}>4 (Entrée/Pause déjeuner/Reprise/Sortie)</option>
                                        <option value="6" {{ $configuration->nombre_pointages_par_jour == 6 ? 'selected' : '' }}>6 (Entrée/Pause matin/Reprise/Pause déjeuner/Reprise/Sortie)</option>
                                        <option value="8" {{ $configuration->nombre_pointages_par_jour == 8 ? 'selected' : '' }}>8 (Entrée/Pause matin/Reprise/Pause déjeuner/Reprise/Pause après-midi/Reprise/Sortie)</option>
                                    </select>
                                    @error('nombre_pointages_par_jour')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pauses_actives" name="pauses_actives" value="1" {{ $configuration->pauses_actives ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pauses_actives">
                                        Activer les pauses
                                    </label>
                                </div>
                                <div class="form-text text-muted mb-3">
                                    Lorsque cette option est activée, les employés peuvent enregistrer des pauses en dehors des pauses principales.
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="annuler_presence_sans_sortie" name="annuler_presence_sans_sortie" value="1" {{ $configuration->annuler_presence_sans_sortie ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annuler_presence_sans_sortie">
                                        Annuler les présences sans pointage de sortie
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="delai_annulation_heures" class="form-label">Délai d'annulation (en heures)</label>
                                    <input type="number" class="form-control @error('delai_annulation_heures') is-invalid @enderror" id="delai_annulation_heures" name="delai_annulation_heures" value="{{ $configuration->delai_annulation_heures }}" min="1" max="72">
                                    @error('delai_annulation_heures')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Nombre d'heures après lesquelles une présence sans pointage de sortie sera automatiquement annulée.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Notifications -->
                    <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notifications_actives" name="notifications_actives" value="1" {{ $configuration->notifications_actives ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifications_actives">
                                        Activer les notifications
                                    </label>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">Types de notifications</div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="notification_absence" name="notification_absence" value="1" {{ $configuration->notification_absence ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notification_absence">
                                                Notifications d'absence
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="notification_retard" name="notification_retard" value="1" {{ $configuration->notification_retard ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notification_retard">
                                                Notifications de retard
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="notification_conge" name="notification_conge" value="1" {{ $configuration->notification_conge ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notification_conge">
                                                Notifications de congé
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Messages personnalisés</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="message_absence" class="form-label">Message d'absence</label>
                                            <textarea class="form-control" id="message_absence" name="message_absence" rows="2">{{ $configuration->message_absence }}</textarea>
                                            <div class="form-text">
                                                Variables disponibles: {nom}, {prenom}, {date}, {entreprise}
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="message_retard" class="form-label">Message de retard</label>
                                            <textarea class="form-control" id="message_retard" name="message_retard" rows="2">{{ $configuration->message_retard }}</textarea>
                                            <div class="form-text">
                                                Variables disponibles: {nom}, {prenom}, {date}, {minutes_retard}, {entreprise}
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="message_conge" class="form-label">Message de congé</label>
                                            <textarea class="form-control" id="message_conge" name="message_conge" rows="2">{{ $configuration->message_conge }}</textarea>
                                            <div class="form-text">
                                                Variables disponibles: {nom}, {prenom}, {date_debut}, {date_fin}, {type_conge}, {statut}, {entreprise}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Activer/désactiver les options de notification en fonction de l'état du switch principal
        const notificationsActivesSwitch = document.getElementById('notifications_actives');
        const notificationOptions = document.querySelectorAll('#notification_absence, #notification_retard, #notification_conge, #message_absence, #message_retard, #message_conge');
        
        function toggleNotificationOptions() {
            const isEnabled = notificationsActivesSwitch.checked;
            notificationOptions.forEach(option => {
                option.disabled = !isEnabled;
            });
        }
        
        notificationsActivesSwitch.addEventListener('change', toggleNotificationOptions);
        toggleNotificationOptions();
        
        // Activer/désactiver le délai d'annulation en fonction de l'état du switch d'annulation
        const annulerPresenceSwitch = document.getElementById('annuler_presence_sans_sortie');
        const delaiAnnulationInput = document.getElementById('delai_annulation_heures');
        
        function toggleDelaiAnnulation() {
            delaiAnnulationInput.disabled = !annulerPresenceSwitch.checked;
        }
        
        annulerPresenceSwitch.addEventListener('change', toggleDelaiAnnulation);
        toggleDelaiAnnulation();
    });
</script>
@endsection
