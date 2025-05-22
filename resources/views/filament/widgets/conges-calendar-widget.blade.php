<x-filament::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Calendrier des congés</h2>
                <div class="flex space-x-2">
                    <button wire:click="previousMonth" class="p-2 text-gray-500 hover:text-primary-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button wire:click="currentMonth" class="px-3 py-1 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-md">
                        Aujourd'hui
                    </button>
                    <button wire:click="nextMonth" class="p-2 text-gray-500 hover:text-primary-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="text-center font-medium text-lg">
                {{ ucfirst(Carbon\Carbon::parse($currentMonth)->locale('fr')->monthName) }} {{ Carbon\Carbon::parse($currentMonth)->year }}
            </div>
            
            <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                <!-- Jours de la semaine -->
                @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 text-center text-xs font-medium">
                        {{ $day }}
                    </div>
                @endforeach
                
                <!-- Jours du mois précédent -->
                @for ($i = 0; $i < $daysFromPreviousMonth; $i++)
                    <div class="bg-white dark:bg-gray-800 p-2 text-center text-gray-400 dark:text-gray-500 text-sm min-h-[80px]">
                        {{ $previousMonthStart + $i }}
                    </div>
                @endfor
                
                <!-- Jours du mois courant -->
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = Carbon\Carbon::parse($currentMonthStart)->addDays($day - 1);
                        $dateString = $date->format('Y-m-d');
                        $isToday = $dateString === $today;
                        $hasConges = isset($conges[$dateString]) && count($conges[$dateString]) > 0;
                    @endphp
                    
                    <div class="bg-white dark:bg-gray-800 p-2 text-sm min-h-[80px] {{ $isToday ? 'ring-2 ring-primary-500' : '' }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="{{ $isToday ? 'font-bold text-primary-600' : '' }}">{{ $day }}</span>
                            @if($hasConges)
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-500 rounded-full">
                                    {{ count($conges[$dateString]) }}
                                </span>
                            @endif
                        </div>
                        
                        @if($hasConges)
                            <div class="space-y-1 mt-1">
                                @foreach($conges[$dateString]->take(2) as $conge)
                                    <div class="text-xs p-1 rounded bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-300 truncate" title="{{ $conge->employeur->name }} - {{ $conge->typeConge->nom }}">
                                        {{ Str::limit($conge->employeur->name, 10) }}
                                    </div>
                                @endforeach
                                
                                @if(count($conges[$dateString]) > 2)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        +{{ count($conges[$dateString]) - 2 }} autres
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor
                
                <!-- Jours du mois suivant -->
                @for ($i = 1; $i <= $daysFromNextMonth; $i++)
                    <div class="bg-white dark:bg-gray-800 p-2 text-center text-gray-400 dark:text-gray-500 text-sm min-h-[80px]">
                        {{ $i }}
                    </div>
                @endfor
            </div>
            
            <div class="flex items-center justify-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center">
                    <span class="inline-block w-3 h-3 mr-1 bg-primary-500 rounded-full"></span>
                    <span>Congés approuvés</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-block w-3 h-3 mr-1 ring-2 ring-primary-500 rounded-full"></span>
                    <span>Aujourd'hui</span>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament::widget>
