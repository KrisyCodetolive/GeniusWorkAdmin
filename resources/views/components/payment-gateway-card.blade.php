@props([
    'id' => '',
    'name' => 'payment_gateway',
    'value' => '',
    'title' => '',
    'description' => '',
    'icon' => '',
    'iconColor' => 'from-indigo-500 to-blue-500',
    'methods' => [],
    'selected' => false
])

<div class="mb-6 border border-gray-200 rounded-lg overflow-hidden {{ $selected ? 'border-indigo-500 ring-2 ring-indigo-200' : '' }}">
    <div class="p-4 bg-gray-50 border-b border-gray-200">
        <label 
            for="{{ $id }}"
            class="flex items-center cursor-pointer"
        >
            <input 
                type="radio" 
                id="{{ $id }}"
                name="{{ $name }}" 
                value="{{ $value }}" 
                {{ $selected ? 'checked' : '' }}
                class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" 
                x-model="paymentGateway"
                @click="paymentMethod = ''"
            >
            <div class="ml-3 flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br {{ $iconColor }} rounded-md flex items-center justify-center text-white mr-3">
                    {!! $icon !!}
                </div>
                <div>
                    <span class="block font-medium text-gray-800">{{ $title }}</span>
                    <span class="text-sm text-gray-500">{{ $description }}</span>
                </div>
            </div>
        </label>
    </div>
    
    <div class="p-4" x-show="paymentGateway === '{{ $value }}'">
        <div class="text-sm font-medium text-gray-700 mb-3">Choisissez votre méthode de paiement :</div>
        <div class="space-y-3">
            @foreach($methods as $methodKey => $methodValue)
                <x-payment-card 
                    id="{{ $value }}_{{ $methodKey }}"
                    name="payment_method"
                    value="{{ $methodKey }}"
                    title="{{ $methodValue['title'] }}"
                    description="{{ $methodValue['description'] }}"
                    icon="{{ $methodValue['icon'] }}"
                    iconColor="{{ $methodValue['color'] ?? 'from-indigo-500 to-blue-500' }}"
                    :selected="$selected && $methodKey === old('payment_method')"
                />
            @endforeach
        </div>
    </div>
</div>
