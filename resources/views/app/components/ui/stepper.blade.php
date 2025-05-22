@props(['steps', 'currentStep' => 1, 'vertical' => false])

@php
    // Ensure steps is an array with required properties
    $stepsArray = collect($steps)->map(function($step, $index) use ($currentStep) {
        if (is_string($step)) {
            return [
                'label' => $step,
                'status' => ($index + 1) < $currentStep ? 'complete' : (($index + 1) === $currentStep ? 'current' : 'upcoming')
            ];
        }
        
        return $step;
    })->toArray();
@endphp

@if($vertical)
    <div {{ $attributes->merge(['class' => 'space-y-4']) }}>
        @foreach($stepsArray as $index => $step)
            @php
                $status = $step['status'] ?? (($index + 1) < $currentStep ? 'complete' : (($index + 1) === $currentStep ? 'current' : 'upcoming'));
                $statusClasses = [
                    'complete' => 'bg-blue-600 text-white',
                    'current' => 'border-2 border-blue-600 text-blue-600 bg-white',
                    'upcoming' => 'border border-gray-300 text-gray-500 bg-white'
                ][$status];
            @endphp
            
            <div class="flex items-start">
                <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full {{ $statusClasses }}">
                    @if($status === 'complete')
                        <i data-lucide="check" class="h-5 w-5"></i>
                    @else
                        <span>{{ $index + 1 }}</span>
                    @endif
                </div>
                
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium {{ $status === 'upcoming' ? 'text-gray-500' : 'text-gray-900' }}">
                        {{ $step['label'] }}
                    </p>
                    
                    @if(isset($step['description']))
                        <p class="text-sm text-gray-500">{{ $step['description'] }}</p>
                    @endif
                </div>
            </div>
            
            @if($index < count($stepsArray) - 1)
                <div class="ml-4 pl-0.5 h-10 border-l-2 {{ $status === 'complete' ? 'border-blue-600' : 'border-gray-300' }}"></div>
            @endif
        @endforeach
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center']) }}>
        @foreach($stepsArray as $index => $step)
            @php
                $status = $step['status'] ?? (($index + 1) < $currentStep ? 'complete' : (($index + 1) === $currentStep ? 'current' : 'upcoming'));
                $statusClasses = [
                    'complete' => 'bg-blue-600 text-white',
                    'current' => 'border-2 border-blue-600 text-blue-600 bg-white',
                    'upcoming' => 'border border-gray-300 text-gray-500 bg-white'
                ][$status];
                
                $labelClasses = [
                    'complete' => 'text-blue-600',
                    'current' => 'text-blue-600',
                    'upcoming' => 'text-gray-500'
                ][$status];
                
                $lineClasses = [
                    'complete' => 'border-blue-600',
                    'current' => 'border-gray-300',
                    'upcoming' => 'border-gray-300'
                ][$status];
            @endphp
            
            <div class="flex items-center">
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full {{ $statusClasses }}">
                        @if($status === 'complete')
                            <i data-lucide="check" class="h-5 w-5"></i>
                        @else
                            <span>{{ $index + 1 }}</span>
                        @endif
                    </div>
                    
                    <p class="mt-1 text-xs {{ $labelClasses }}">{{ $step['label'] }}</p>
                </div>
                
                @if($index < count($stepsArray) - 1)
                    <div class="flex-1 h-0.5 mx-2 border-t-2 {{ $lineClasses }}"></div>
                @endif
            </div>
        @endforeach
    </div>
@endif
