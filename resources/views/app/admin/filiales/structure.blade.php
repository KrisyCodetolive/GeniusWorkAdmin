@extends('layouts.app')

@section('title', 'Structure de la filiale ' . $filiale->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Structure de la filiale {{ $filiale->nom }}</h1>
        <a href="{{ route('admin.filiales.show', $filiale) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Informations de la filiale</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-blue-700 mb-2">Code</h3>
                <p class="text-2xl font-bold text-blue-800">{{ $filiale->code }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-green-700 mb-2">Statut</h3>
                <p class="text-2xl font-bold text-green-800">{{ ucfirst($filiale->statut) }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-purple-700 mb-2">Nombre de départements</h3>
                <p class="text-2xl font-bold text-purple-800">{{ count($structure) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Structure hiérarchique des départements</h2>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <p class="text-gray-600 mb-2">Cette visualisation montre la structure hiérarchique complète des départements de la filiale.</p>
            </div>
            
            <div class="org-chart">
                @foreach($structure as $departement)
                    <div class="org-chart-item level-{{ $departement['niveau'] }}" style="margin-left: {{ ($departement['niveau'] - 1) * 40 }}px;">
                        <div class="org-chart-content bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-indigo-800">{{ $departement['nom'] }}</h3>
                                    <p class="text-sm text-gray-600">Code: {{ $departement['code'] }}</p>
                                    <p class="text-sm text-gray-600">Niveau: {{ $departement['niveau'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-700">{{ $departement['nombre_employes'] }} employés</p>
                                    <p class="text-sm font-medium text-gray-700">{{ number_format($departement['budget'], 2, ',', ' ') }} €</p>
                                </div>
                            </div>
                            <div class="mt-3 flex space-x-2">
                                <a href="{{ route('admin.departements.show', $departement['id']) }}" class="text-xs bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded">
                                    Détails
                                </a>
                                <a href="{{ route('admin.departements.arborescence', $departement['id']) }}" class="text-xs bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded">
                                    Arborescence
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .org-chart {
        position: relative;
    }
    
    .org-chart-item {
        position: relative;
        margin-bottom: 1rem;
    }
    
    .org-chart-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -20px;
        height: 100%;
        border-left: 2px solid #d1d5db;
    }
    
    .org-chart-item::after {
        content: '';
        position: absolute;
        top: 1.5rem;
        left: -20px;
        width: 20px;
        border-top: 2px solid #d1d5db;
    }
    
    .org-chart-item:last-child::before {
        height: 1.5rem;
    }
    
    .level-1::before,
    .level-1::after {
        display: none;
    }
</style>
@endsection
