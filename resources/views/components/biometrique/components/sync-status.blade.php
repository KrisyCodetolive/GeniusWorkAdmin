@props(['appareil'])

<div class="bg-white rounded-lg shadow p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Synchronisation automatique</h3>
        <a href="{{ route('biometrique.appareils.sync-config', $appareil->id) }}" class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fas fa-cog mr-1"></i> Configurer
        </a>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">Statut</span>
            @if($appareil->sync_auto_enabled)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i> Activée
                </span>
            @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    <i class="fas fa-times-circle mr-1"></i> Désactivée
                </span>
            @endif
        </div>

        @if($appareil->sync_auto_enabled)
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-500 mb-1">Logs</div>
                    <div class="text-sm font-medium">{{ $appareil->sync_logs_interval }} min</div>
                    <div class="text-xs text-gray-500 mt-1">
                        @if($appareil->derniere_sync_logs)
                            {{ $appareil->derniere_sync_logs->diffForHumans() }}
                        @else
                            Jamais
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-500 mb-1">Utilisateurs</div>
                    <div class="text-sm font-medium">{{ $appareil->sync_users_interval }} min</div>
                    <div class="text-xs text-gray-500 mt-1">
                        @if($appareil->derniere_sync_users)
                            {{ $appareil->derniere_sync_users->diffForHumans() }}
                        @else
                            Jamais
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-500 mb-1">Heure</div>
                    <div class="text-sm font-medium">{{ $appareil->sync_time_interval }} min</div>
                    <div class="text-xs text-gray-500 mt-1">
                        @if($appareil->derniere_sync_time)
                            {{ $appareil->derniere_sync_time->diffForHumans() }}
                        @else
                            Jamais
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center mt-2">
                <button type="button" onclick="runSync('{{ $appareil->id }}', 'all')" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-sync-alt mr-1"></i> Synchroniser maintenant
                </button>
            </div>
        @else
            <div class="text-sm text-gray-500 italic">
                La synchronisation automatique est désactivée pour cet appareil.
                <a href="{{ route('biometrique.appareils.sync-config', $appareil->id) }}" class="text-blue-600 hover:text-blue-800">
                    Cliquez ici pour l'activer.
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function runSync(appareilId, type) {
        const btn = $(event.currentTarget);
        const originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> En cours...').prop('disabled', true);
        
        $.ajax({
            url: `{{ route('biometrique.appareils.run-sync', '') }}/${appareilId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                type: type
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Synchronisation lancée avec succès');
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
    }
</script>
@endpush
