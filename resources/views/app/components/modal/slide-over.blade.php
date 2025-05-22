@props(['id', 'title' => null, 'closeButton' => true, 'position' => 'right'])

@php
    $positionClasses = [
        'right' => 'right-0',
        'left' => 'left-0',
    ];
    
    $position = $positionClasses[$position] ?? $positionClasses['right'];
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-on:open-slide-over.window="$event.detail == '{{ $id }}' ? open = true : null"
    x-on:close-slide-over.window="$event.detail == '{{ $id }}' ? open = false : null"
    x-on:keydown.escape.window="open = false"
    x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
    x-transition:enter-start="{{ $position === 'right-0' ? 'translate-x-full' : '-translate-x-full' }}"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="{{ $position === 'right-0' ? 'translate-x-full' : '-translate-x-full' }}"
    class="fixed inset-0 overflow-hidden z-50"
    style="display: none;"
>
    <div class="absolute inset-0 overflow-hidden">
        <div 
            x-show="open"
            x-transition:enter="ease-in-out duration-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-500"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            x-on:click="open = false"
        ></div>
        
        <div class="fixed inset-y-0 {{ $position }} max-w-full flex">
            <div class="relative w-screen max-w-md">
                <div class="h-full flex flex-col bg-white shadow-xl overflow-y-auto">
                    @if($title || $closeButton)
                        <div class="px-4 py-6 sm:px-6 border-b border-gray-200">
                            <div class="flex items-start justify-between">
                                @if($title)
                                    <h2 class="text-lg font-medium text-gray-900">{{ $title }}</h2>
                                @endif
                                
                                @if($closeButton)
                                    <div class="ml-3 h-7 flex items-center">
                                        <button
                                            type="button"
                                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            x-on:click="open = false"
                                        >
                                            <span class="sr-only">Fermer</span>
                                            <i data-lucide="x" class="h-6 w-6"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex-1 px-4 py-6 sm:px-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.openSlideOver = function(id) {
        window.dispatchEvent(new CustomEvent('open-slide-over', { detail: id }));
    }
    
    window.closeSlideOver = function(id) {
        window.dispatchEvent(new CustomEvent('close-slide-over', { detail: id }));
    }
</script>
@endpush
