@props(['cols' => '1', 'gap' => '6', 'responsive' => true])

@php
    $colsMap = [
        '1' => 'grid-cols-1',
        '2' => 'grid-cols-1 md:grid-cols-2',
        '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        '5' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
        '6' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
    ];
    
    $gapMap = [
        '0' => 'gap-0',
        '1' => 'gap-1',
        '2' => 'gap-2',
        '3' => 'gap-3',
        '4' => 'gap-4',
        '5' => 'gap-5',
        '6' => 'gap-6',
        '8' => 'gap-8',
        '10' => 'gap-10',
        '12' => 'gap-12',
    ];
    
    $gridClass = 'grid';
    
    if ($responsive && isset($colsMap[$cols])) {
        $gridClass .= ' ' . $colsMap[$cols];
    } else {
        $gridClass .= ' grid-cols-' . $cols;
    }
    
    if (isset($gapMap[$gap])) {
        $gridClass .= ' ' . $gapMap[$gap];
    } else {
        $gridClass .= ' gap-' . $gap;
    }
@endphp

<div {{ $attributes->merge(['class' => $gridClass]) }}>
    {{ $slot }}
</div>
