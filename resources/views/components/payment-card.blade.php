@props([
    'id' => '',
    'name' => 'payment_method',
    'value' => '',
    'title' => '',
    'description' => '',
    'icon' => '',
    'iconColor' => 'from-indigo-500 to-blue-500',
    'selected' => false
])

<label 
    for="{{ $id }}"
    class="relative flex p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-200 transition-colors {{ $selected ? 'border-indigo-500 ring-2 ring-indigo-200' : '' }}"
>
    <input 
        type="radio" 
        id="{{ $id }}"
        name="{{ $name }}" 
        value="{{ $value }}" 
        {{ $selected ? 'checked' : '' }}
        class="sr-only" 
        x-model="paymentMethod"
    >
    <div class="flex items-center">
        <div class="w-12 h-12 bg-gradient-to-br {{ $iconColor }} rounded-md flex items-center justify-center text-white mr-4">
            {!! $icon !!}
        </div>
        <div>
            <span class="block font-medium text-gray-800">{{ $title }}</span>
            <span class="text-sm text-gray-500">{{ $description }}</span>
        </div>
    </div>
    <span class="absolute right-4 top-1/2 transform -translate-y-1/2 {{ $selected ? 'text-indigo-500' : '' }}">
        <svg x-show="paymentMethod === '{{ $value }}'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </span>
</label>
