@props(['tabs' => [], 'activeTab' => null])

<div x-data="{ activeTab: @js($activeTab ?? array_key_first($tabs)) }" class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach($tabs as $key => $tab)
                <button 
                    @click="activeTab = '{{ $key }}'" 
                    :class="{ 'border-blue-500 text-blue-600': activeTab === '{{ $key }}', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $key }}' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                >
                    {{ $tab['label'] }}
                    @if(isset($tab['count']))
                        <span 
                            :class="{ 'bg-blue-100 text-blue-600': activeTab === '{{ $key }}', 'bg-gray-100 text-gray-900': activeTab !== '{{ $key }}' }"
                            class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium"
                        >
                            {{ $tab['count'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>
    
    <div class="mt-4">
        @foreach($tabs as $key => $tab)
            <div x-show="activeTab === '{{ $key }}'" x-cloak>
                @if(isset($tab['content']))
                    {!! $tab['content'] !!}
                @endif
                
                @if(isset(${"tab_" . $key}))
                    {{ ${"tab_" . $key} }}
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
