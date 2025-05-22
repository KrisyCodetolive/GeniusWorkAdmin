@extends('layouts.app')

@section('title', 'Configuration de synchronisation automatique')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Configuration de synchronisation automatique - {{ $appareil->nom }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('biometrique.appareils.show', $appareil->id) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Retour
                        </a>
                    </div>
                </div>

                <form action="{{ route('biometrique.appareils.sync-config.update', $appareil->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sync_auto_enabled" name="sync_auto_enabled" value="1" {{ $appareil->sync_auto_enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sync_auto_enabled">Activer la synchronisation automatique</label>
                            </div>
                            <small class="form-text text-muted">
                                Lorsque cette option est activée, l'appareil sera synchronisé automatiquement selon les intervalles définis ci-dessous.
                            </small>
                        </div>

                        <div class="row sync-settings {{ $appareil->sync_auto_enabled ? '' : 'd-none' }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sync_logs_interval">Intervalle de synchronisation des logs (minutes)</label>
                                    <input type="number" class="form-control" id="sync_logs_interval" name="sync_logs_interval" 
                                           value="{{ old('sync_logs_interval', $appareil->sync_logs_interval) }}" min="5" max="10080">
                                    <small class="form-text text-muted">
                                        Intervalle entre chaque synchronisation des logs (min: 5 minutes, max: 7 jours)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sync_users_interval">Intervalle de synchronisation des utilisateurs (minutes)</label>
                                    <input type="number" class="form-control" id="sync_users_interval" name="sync_users_interval" 
                                           value="{{ old('sync_users_interval', $appareil->sync_users_interval) }}" min="5" max="10080">
                                    <small class="form-text text-muted">
                                        Intervalle entre chaque synchronisation des utilisateurs (min: 5 minutes, max: 7 jours)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sync_time_interval">Intervalle de synchronisation de l'heure (minutes)</label>
                                    <input type="number" class="form-control" id="sync_time_interval" name="sync_time_interval" 
                                           value="{{ old('sync_time_interval', $appareil->sync_time_interval) }}" min="5" max="10080">
                                    <small class="form-text text-muted">
                                        Intervalle entre chaque synchronisation de l'heure (min: 5 minutes, max: 7 jours)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group sync-settings {{ $appareil->sync_auto_enabled ? '' : 'd-none' }}">
                            <label for="sync_options">Options avancées (JSON)</label>
                            <textarea class="form-control" id="sync_options" name="sync_options" rows="5">{{ old('sync_options', $appareil->sync_options) }}</textarea>
                            <small class="form-text text-muted">
                                Options avancées au format JSON. Exemple: {"max_logs_per_sync": 1000, "retry_on_failure": true}
                            </small>
                        </div>

                        <div class="card sync-settings {{ $appareil->sync_auto_enabled ? '' : 'd-none' }}">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Dernières synchronisations</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Dernière exécution</th>
                                                <th>Statut</th>
                                                <th>Résultat</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Logs</td>
                                                <td>
                                                    @if ($appareil->derniere_sync_logs)
                                                        {{ $appareil->derniere_sync_logs->diffForHumans() }}
                                                        <br><small class="text-muted">{{ $appareil->derniere_sync_logs->format('d/m/Y H:i:s') }}</small>
                                                    @else
                                                        <span class="text-muted">Jamais</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_logs_statut === 'success')
                                                        <span class="badge badge-success">Succès</span>
                                                    @elseif ($appareil->derniere_sync_logs_statut === 'error')
                                                        <span class="badge badge-danger">Erreur</span>
                                                    @elseif ($appareil->derniere_sync_logs_statut === 'running')
                                                        <span class="badge badge-info">En cours</span>
                                                    @else
                                                        <span class="badge badge-secondary">Non défini</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_logs_resultat)
                                                        <span class="text-muted">{{ $appareil->derniere_sync_logs_resultat }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary run-sync" data-type="logs">
                                                        <i class="fas fa-sync-alt mr-1"></i> Exécuter maintenant
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Utilisateurs</td>
                                                <td>
                                                    @if ($appareil->derniere_sync_users)
                                                        {{ $appareil->derniere_sync_users->diffForHumans() }}
                                                        <br><small class="text-muted">{{ $appareil->derniere_sync_users->format('d/m/Y H:i:s') }}</small>
                                                    @else
                                                        <span class="text-muted">Jamais</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_users_statut === 'success')
                                                        <span class="badge badge-success">Succès</span>
                                                    @elseif ($appareil->derniere_sync_users_statut === 'error')
                                                        <span class="badge badge-danger">Erreur</span>
                                                    @elseif ($appareil->derniere_sync_users_statut === 'running')
                                                        <span class="badge badge-info">En cours</span>
                                                    @else
                                                        <span class="badge badge-secondary">Non défini</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_users_resultat)
                                                        <span class="text-muted">{{ $appareil->derniere_sync_users_resultat }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary run-sync" data-type="users">
                                                        <i class="fas fa-sync-alt mr-1"></i> Exécuter maintenant
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Heure</td>
                                                <td>
                                                    @if ($appareil->derniere_sync_time)
                                                        {{ $appareil->derniere_sync_time->diffForHumans() }}
                                                        <br><small class="text-muted">{{ $appareil->derniere_sync_time->format('d/m/Y H:i:s') }}</small>
                                                    @else
                                                        <span class="text-muted">Jamais</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_time_statut === 'success')
                                                        <span class="badge badge-success">Succès</span>
                                                    @elseif ($appareil->derniere_sync_time_statut === 'error')
                                                        <span class="badge badge-danger">Erreur</span>
                                                    @elseif ($appareil->derniere_sync_time_statut === 'running')
                                                        <span class="badge badge-info">En cours</span>
                                                    @else
                                                        <span class="badge badge-secondary">Non défini</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($appareil->derniere_sync_time_resultat)
                                                        <span class="text-muted">{{ $appareil->derniere_sync_time_resultat }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary run-sync" data-type="time">
                                                        <i class="fas fa-sync-alt mr-1"></i> Exécuter maintenant
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-success run-sync" data-type="all">
                                    <i class="fas fa-sync-alt mr-1"></i> Tout synchroniser maintenant
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Enregistrer les paramètres
                        </button>
                        <a href="{{ route('biometrique.appareils.show', $appareil->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Afficher/masquer les paramètres de synchronisation
        $('#sync_auto_enabled').change(function() {
            if ($(this).is(':checked')) {
                $('.sync-settings').removeClass('d-none');
            } else {
                $('.sync-settings').addClass('d-none');
            }
        });

        // Exécuter une synchronisation
        $('.run-sync').click(function() {
            const btn = $(this);
            const originalHtml = btn.html();
            const type = btn.data('type');
            
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> En cours...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("biometrique.appareils.run-sync", $appareil->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Synchronisation réussie');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message || 'Erreur lors de la synchronisation');
                        btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function() {
                    toastr.error('Erreur lors de la synchronisation');
                    btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Validation du JSON
        $('#sync_options').blur(function() {
            const value = $(this).val();
            if (value) {
                try {
                    JSON.parse(value);
                    $(this).removeClass('is-invalid');
                } catch (e) {
                    $(this).addClass('is-invalid');
                    toastr.error('Format JSON invalide');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
@endpush
