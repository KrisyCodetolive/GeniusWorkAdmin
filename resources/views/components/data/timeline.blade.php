@props(['items' => [], 'dateFormat' => 'd/m/Y'])

<div {{ $attributes->merge(['class' => 'flow-root']) }}>
    <ul role="list" class="-mb-8">
        @foreach($items as $index => $item)
            <li>
                <div class="relative pb-8">
                    @if($index < count($items) - 1)
                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    @endif
                    
                    <div class="relative flex items-start space-x-3">
                        <div class="relative">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center bg-{{ $item['color'] ?? 'blue' }}-100 ring-8 ring-white">
                                @if(isset($item['icon']))
                                    <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 text-{{ $item['color'] ?? 'blue' }}-600"></i>
                                @else
                                    <span class="text-{{ $item['color'] ?? 'blue' }}-600 text-sm font-medium">
                                        {{ $item['initials'] ?? substr($item['title'] ?? '', 0, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="min-w-0 flex-1">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item['title'] ?? '' }}
                                </div>
                                
                                @if(isset($item['date']))
                                    <p class="mt-0.5 text-sm text-gray-500">
                                        @if(is_string($item['date']))
                                            {{ $item['date'] }}
                                        @elseif($item['date'] instanceof \DateTime || $item['date'] instanceof \Carbon\Carbon)
                                            {{ $item['date']->format($dateFormat) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            
                            @if(isset($item['content']))
                                <div class="mt-2 text-sm text-gray-700">
                                    <p>{{ $item['content'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($item['actions']) && is_array($item['actions']) && count($item['actions']) > 0)
                                <div class="mt-2 flex space-x-2">
                                    @foreach($item['actions'] as $action)
                                        <a 
                                            href="{{ $action['url'] ?? '#' }}" 
                                            class="text-sm font-medium text-{{ $action['color'] ?? 'blue' }}-600 hover:text-{{ $action['color'] ?? 'blue' }}-800"
                                            @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                                        >
                                            {{ $action['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
