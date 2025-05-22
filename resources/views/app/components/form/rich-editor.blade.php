@props(['label', 'name', 'id' => null, 'value' => null, 'required' => false, 'error' => null, 'placeholder' => null, 'toolbar' => 'basic'])

<div x-data="{ 
    content: @js($value),
    editor: null,
    init() {
        // Wait for the DOM to be fully loaded
        this.$nextTick(() => {
            // Initialize CKEditor
            ClassicEditor
                .create(this.$refs.editor, {
                    toolbar: @js($toolbar === 'basic' 
                        ? ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'] 
                        : ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|', 'link', 'bulletedList', 'numberedList', 'todoList', '|', 'fontColor', 'fontBackgroundColor', '|', 'alignment', '|', 'outdent', 'indent', '|', 'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', '|', 'undo', 'redo']),
                    placeholder: @js($placeholder),
                    language: 'fr'
                })
                .then(editor => {
                    this.editor = editor;
                    
                    // Set initial content
                    editor.setData(this.content || '');
                    
                    // Update hidden input when content changes
                    editor.model.document.on('change:data', () => {
                        this.content = editor.getData();
                        this.$refs.input.value = this.content;
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    }
}" class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="rounded-lg border border-gray-300 overflow-hidden {{ $error ? 'border-red-500' : '' }}">
        <div x-ref="editor"></div>
    </div>
    
    <input type="hidden" x-ref="input" name="{{ $name }}" id="{{ $id ?? $name }}" />
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/translations/fr.js"></script>
    <style>
        .ck-editor__editable_inline {
            min-height: 150px;
            max-height: 400px;
        }
    </style>
@endpush
