@props(['id', 'title' => 'Confirmation', 'confirmText' => 'Confirmer', 'cancelText' => 'Annuler', 'confirmType' => 'danger', 'formAction' => null, 'formMethod' => 'POST'])

<div
    x-data="{ open: false }"
    x-show="open"
    x-on:open-confirmation-dialog.window="$event.detail == '{{ $id }}' ? open = true : null"
    x-on:close-confirmation-dialog.window="$event.detail == '{{ $id }}' ? open = false : null"
    x-on:keydown.escape.window="open = false"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div
            x-show="open"
            x-on:click="open = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
            aria-hidden="true"
        ></div>

        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full px-4 pt-5 pb-4 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:max-w-lg sm:p-6"
        >
            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <div class="text-sm text-gray-500">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
            
            @if($formAction)
                <form id="{{ $id }}_form" action="{{ $formAction }}" method="{{ $formMethod }}" class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    @csrf
                    @if($formMethod === 'DELETE' || strtoupper($formMethod) === 'DELETE')
                        @method('DELETE')
                    @elseif($formMethod !== 'POST' && strtoupper($formMethod) !== 'POST')
                        @method($formMethod)
                    @endif
                    
                    <button 
                        type="submit" 
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm
                        {{ $confirmType === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }}"
                    >
                        {{ $confirmText }}
                    </button>
                    <button 
                        type="button" 
                        @click="open = false" 
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        {{ $cancelText }}
                    </button>
                </form>
            @else
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        x-on:click="$dispatch('confirm-action', '{{ $id }}'); open = false"
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm
                        {{ $confirmType === 'danger' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }}"
                    >
                        {{ $confirmText }}
                    </button>
                    <button 
                        type="button" 
                        @click="open = false" 
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        {{ $cancelText }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.openConfirmationDialog = function(dialogId) {
        window.dispatchEvent(new CustomEvent('open-confirmation-dialog', { detail: dialogId }));
    }
    
    window.closeConfirmationDialog = function(dialogId) {
        window.dispatchEvent(new CustomEvent('close-confirmation-dialog', { detail: dialogId }));
    }
</script>
@endpush
