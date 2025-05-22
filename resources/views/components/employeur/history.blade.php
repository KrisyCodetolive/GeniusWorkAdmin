@props(['historique'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-history mr-2"></i>Historique
        </h2>
    </div>
    <div class="p-6">
        @if(count($historique) > 0)
            <div class="relative">
                <div class="absolute h-full w-0.5 bg-gray-200 left-2.5"></div>
                <ul class="space-y-4">
                    @foreach($historique as $item)
                        <li class="relative pl-8">
                            <div class="absolute left-0 top-1 h-5 w-5 rounded-full bg-blue-500 flex items-center justify-center">
                                <i class="fas fa-circle text-white text-xs"></i>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="flex justify-between mb-1">
                                    <span class="font-semibold">{{ $item->action }}</span>
                                    <span class="text-sm text-gray-500">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $item->description }}</p>
                                @if($item->user)
                                    <p class="text-xs text-gray-500 mt-1">Par: {{ $item->user->name }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="text-gray-600 text-center">Aucun historique disponible pour cet employeur.</p>
        @endif
    </div>
</div>
