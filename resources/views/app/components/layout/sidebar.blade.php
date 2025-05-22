@props(['title' => 'Navigation', 'mobile' => true])

<div x-data="{ open: false }" class="flex-shrink-0">
    <!-- Mobile menu button -->
    @if($mobile)
        <div class="lg:hidden">
            <button 
                type="button" 
                x-on:click="open = !open" 
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
            >
                <span class="sr-only">Ouvrir le menu</span>
                <i x-show="!open" data-lucide="menu" class="block h-6 w-6"></i>
                <i x-show="open" data-lucide="x" class="block h-6 w-6"></i>
            </button>
        </div>
    @endif
    
    <!-- Sidebar for desktop -->
    <div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 lg:border-r lg:border-gray-200 lg:bg-white lg:pt-5 lg:pb-4">
        <div class="flex items-center flex-shrink-0 px-6">
            <img class="h-8 w-auto" src="{{ asset('images/logo.png') }}" alt="GENIUS WORK">
            <span class="ml-2 text-xl font-bold text-gray-900">GENIUS WORK</span>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-6 px-3 space-y-1">
            {{ $slot }}
        </nav>
        
        @if(isset($footer))
            <div class="mt-auto px-3 py-4">
                {{ $footer }}
            </div>
        @endif
    </div>
    
    <!-- Mobile menu -->
    @if($mobile)
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-100" 
            x-transition:enter-start="transform opacity-0 scale-95" 
            x-transition:enter-end="transform opacity-100 scale-100" 
            x-transition:leave="transition ease-in duration-75" 
            x-transition:leave-start="transform opacity-100 scale-100" 
            x-transition:leave-end="transform opacity-0 scale-95"
            class="lg:hidden fixed inset-0 z-40 flex"
        >
            <!-- Overlay -->
            <div 
                x-show="open" 
                x-on:click="open = false" 
                x-transition:enter="transition-opacity ease-linear duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition-opacity ease-linear duration-300" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-600 bg-opacity-75"
            ></div>
            
            <!-- Menu panel -->
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                <div x-on:click="open = false" class="absolute top-0 right-0 -mr-12 pt-2">
                    <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Fermer le menu</span>
                        <i data-lucide="x" class="h-6 w-6 text-white"></i>
                    </button>
                </div>
                
                <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                    <div class="flex-shrink-0 flex items-center px-4">
                        <img class="h-8 w-auto" src="{{ asset('images/logo.png') }}" alt="GENIUS WORK">
                        <span class="ml-2 text-xl font-bold text-gray-900">GENIUS WORK</span>
                    </div>
                    
                    <nav class="mt-5 px-2 space-y-1">
                        {{ $slot }}
                    </nav>
                </div>
                
                @if(isset($footer))
                    <div class="px-3 py-4 border-t border-gray-200">
                        {{ $footer }}
                    </div>
                @endif
            </div>
            
            <div class="flex-shrink-0 w-14">
                <!-- Force sidebar to shrink to fit close icon -->
            </div>
        </div>
    @endif
</div>
