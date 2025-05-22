@props(['label' => null, 'orientation' => 'horizontal', 'spacing' => 'my-6'])

@if($orientation === 'horizontal')
    <div class="{{ $spacing }}">
        @if($label)
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-3 bg-white text-sm text-gray-500">{{ $label }}</span>
                </div>
            </div>
        @else
            <div class="border-t border-gray-300"></div>
        @endif
    </div>
@else
    <div class="flex h-full items-center">
        <div class="border-l border-gray-300 h-full mx-4"></div>
    </div>
@endif
