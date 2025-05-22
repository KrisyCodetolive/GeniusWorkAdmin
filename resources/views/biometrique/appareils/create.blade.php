@extends('layouts.app')

@section('title', 'Ajouter un appareil biométrique')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('biometrique.appareils.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Ajouter un appareil biométrique</h1>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <x-biometrique.components.forms.device-form 
                :sites="$sites" 
                action="{{ route('biometrique.appareils.store') }}" 
                method="POST" 
            />
        </div>
    </div>
</div>
@endsection
