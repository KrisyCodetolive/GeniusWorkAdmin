@php
    $colorClasses = [
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'pink' => 'bg-pink-100 text-pink-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ][$color] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClasses }}">
    {{ $text }}
</span>
