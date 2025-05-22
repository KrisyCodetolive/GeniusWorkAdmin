@props(['action', 'method' => 'POST', 'enctype' => false])

@php
    $method = strtoupper($method);
    $spoofedMethod = in_array($method, ['PUT', 'PATCH', 'DELETE']) ? $method : false;
@endphp

<form action="{{ $action }}" method="{{ $spoofedMethod ? 'POST' : $method }}" {{ $enctype ? 'enctype="multipart/form-data"' : '' }} {{ $attributes }}>
    @csrf
    
    @if($spoofedMethod)
        @method($spoofedMethod)
    @endif
    
    {{ $slot }}
</form>
