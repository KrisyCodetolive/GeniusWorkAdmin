@props(['label', 'name', 'id' => null, 'accept' => null, 'required' => false, 'error' => null, 'previewId' => null])

<div class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
                <label for="{{ $id ?? $name }}" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                    <span>Télécharger un fichier</span>
                    <input 
                        id="{{ $id ?? $name }}" 
                        name="{{ $name }}" 
                        type="file" 
                        class="sr-only"
                        @if($accept) accept="{{ $accept }}" @endif
                        @if($required) required @endif
                        @if($previewId) onchange="previewImage(this, '{{ $previewId }}')" @endif
                    />
                </label>
                <p class="pl-1">ou glisser-déposer</p>
            </div>
            <p class="text-xs text-gray-500">
                PNG, JPG, GIF jusqu'à 2MB
            </p>
        </div>
    </div>
    
    @if($previewId)
    <div id="{{ $previewId }}_container" class="mt-2 hidden">
        <img id="{{ $previewId }}" src="#" alt="Aperçu" class="mt-2 h-20 w-auto object-cover rounded-md"/>
    </div>
    @endif
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>

@if($previewId)
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewContainer = document.getElementById(previewId + '_container');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endif
