<div class="flex flex-col">
    <div class="text-sm font-medium text-gray-900">
        {{ $montant }} {{ $devise ?? 'FCFA' }}
    </div>
    @if(isset($type))
    <div class="text-xs text-gray-500">
        {{ $type }}
    </div>
    @endif
    @if(isset($frequence))
    <div class="text-xs text-gray-500">
        {{ $frequence }}
    </div>
    @endif
</div>
