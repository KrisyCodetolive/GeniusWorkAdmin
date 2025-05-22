<div class="flex items-center">
    <div class="flex-shrink-0 h-10 w-10">
        @if($photo)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ $photo }}" alt="{{ $nom }}">
        @else
            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
            </div>
        @endif
    </div>
    <div class="ml-4">
        <div class="text-sm font-medium text-gray-900">
            {{ $nom }}
        </div>
        <div class="text-sm text-gray-500">
            {{ $email }}
        </div>
    </div>
</div>
