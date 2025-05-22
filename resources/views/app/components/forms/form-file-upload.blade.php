@props(['name', 'label', 'required' => false, 'accept' => 'image/*'])

<div class="form-group mb-4" x-data="{ fileName: '', previewUrl: '' }">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
        {{ $label }}
        @if($required)
            <span class="text-red-500 ml-1">*</span>
        @endif
    </label>
    
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div 
                class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition-all duration-200"
                x-on:click="$refs.fileInput.click()"
            >
                <template x-if="previewUrl">
                    <img :src="previewUrl" class="h-full w-full object-cover rounded-lg" />
                </template>
                
                <template x-if="!previewUrl">
                    <div class="text-center p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs text-gray-500 mt-1">Photo</p>
                    </div>
                </template>
            </div>
        </div>
        
        <div class="flex-grow">
            <label 
                for="{{ $name }}" 
                class="group relative flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer transition-all duration-200"
            >
                <span>Choisir un fichier</span>
                <input 
                    type="file" 
                    id="{{ $name }}" 
                    name="{{ $name }}" 
                    accept="{{ $accept }}" 
                    x-ref="fileInput"
                    x-on:change="
                        fileName = $event.target.files[0]?.name || '';
                        const file = $event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => previewUrl = e.target.result;
                            reader.readAsDataURL(file);
                        } else {
                            previewUrl = '';
                        }
                    "
                    @if($required) required @endif
                    class="sr-only"
                    {{ $attributes }}
                >
            </label>
            
            <p class="mt-1 text-xs text-gray-500" x-text="fileName || 'Aucun fichier choisi'"></p>
        </div>
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
    @enderror
</div>
