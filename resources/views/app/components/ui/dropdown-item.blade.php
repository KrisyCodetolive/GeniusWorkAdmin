@props(['href' => '#', 'icon' => null, 'type' => 'link', 'form' => null, 'method' => 'POST'])

@if($type === 'link')
    <a 
        href="{{ $href }}" 
        {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900']) }}
    >
        @if($icon)
            <i data-lucide="{{ $icon }}" class="w-5 h-5 mr-2 -ml-1 inline-block align-text-bottom"></i>
        @endif
        {{ $slot }}
    </a>
@elseif($type === 'button')
    <button 
        type="button" 
        {{ $attributes->merge(['class' => 'block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900']) }}
    >
        @if($icon)
            <i data-lucide="{{ $icon }}" class="w-5 h-5 mr-2 -ml-1 inline-block align-text-bottom"></i>
        @endif
        {{ $slot }}
    </button>
@elseif($type === 'form' && $form)
    <form action="{{ $href }}" method="{{ $method }}" id="{{ $form }}">
        @csrf
        @if($method !== 'GET' && $method !== 'POST')
            @method($method)
        @endif
        <button 
            type="submit" 
            {{ $attributes->merge(['class' => 'block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900']) }}
        >
            @if($icon)
                <i data-lucide="{{ $icon }}" class="w-5 h-5 mr-2 -ml-1 inline-block align-text-bottom"></i>
            @endif
            {{ $slot }}
        </button>
    </form>
@endif
