@props(['value', 'for' => null, 'required' => false])

<label {{ $for ? 'for="' . $for . '"' : '' }} {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>
