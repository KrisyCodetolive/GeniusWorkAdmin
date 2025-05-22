<div class="flex flex-col">
    <div class="text-sm font-medium text-gray-900">
        {{ $type }}
    </div>
    @if(isset($date_debut))
    <div class="text-xs text-gray-500">
        Début: {{ $date_debut }}
    </div>
    @endif
    @if(isset($date_fin))
    <div class="text-xs text-gray-500">
        Fin: {{ $date_fin }}
    </div>
    @endif
    @if(isset($duree))
    <div class="text-xs text-gray-500">
        Durée: {{ $duree }}
    </div>
    @endif
</div>
