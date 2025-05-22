@extends('layouts.app')

@section('title', 'Statistiques de la filiale ' . $filiale->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Statistiques de la filiale {{ $filiale->nom }}</h1>
        <a href="{{ route('admin.filiales.show', $filiale) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Informations générales</h2>
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
                <h3 class="text-lg font-medium text-purple-700 mb-2">Date de création</h3>
                <p class="text-2xl font-bold text-purple-800">{{ $filiale->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Statistiques détaillées</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-indigo-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-indigo-700 mb-2">Nombre de départements</h3>
                <p class="text-2xl font-bold text-indigo-800">{{ $statistiques['nombreDepartements'] }}</p>
            </div>
            <div class="bg-pink-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-pink-700 mb-2">Nombre d'employés</h3>
                <p class="text-2xl font-bold text-pink-800">{{ $statistiques['nombreEmployes'] }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-yellow-700 mb-2">Budget total</h3>
                <p class="text-2xl font-bold text-yellow-800">{{ number_format($statistiques['budgetTotal'], 2, ',', ' ') }} €</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-red-700 mb-2">Taux d'occupation</h3>
                <p class="text-2xl font-bold text-red-800">{{ number_format($statistiques['tauxOccupationMoyen'], 1, ',', ' ') }}%</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Départements</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nom</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Niveau</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employés</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Budget</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($departements as $departement)
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $departement->code }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $departement->nom }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $departement->niveau }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $departement->nombre_employes }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ number_format($departement->budget, 2, ',', ' ') }} €</td>
                            <td class="py-3 px-4 text-sm text-gray-500">
                                <a href="{{ route('admin.departements.show', $departement) }}" class="text-blue-600 hover:text-blue-900 mr-2">Voir</a>
                                <a href="{{ route('admin.departements.statistiques', $departement) }}" class="text-green-600 hover:text-green-900">Statistiques</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-3 px-4 text-sm text-gray-500 text-center">Aucun département trouvé</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
