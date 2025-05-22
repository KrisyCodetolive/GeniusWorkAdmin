@extends('layouts.app')

@section('title', 'Statistiques du département ' . $departement->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Statistiques du département {{ $departement->nom }}</h1>
        <a href="{{ route('admin.departements.show', $departement) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
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
                <p class="text-2xl font-bold text-blue-800">{{ $departement->code }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-green-700 mb-2">Filiale</h3>
                <p class="text-2xl font-bold text-green-800">{{ $departement->filiale->nom }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-purple-700 mb-2">Niveau hiérarchique</h3>
                <p class="text-2xl font-bold text-purple-800">{{ $departement->niveau }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Statistiques détaillées</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-indigo-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-indigo-700 mb-2">Taux d'occupation</h3>
                <p class="text-2xl font-bold text-indigo-800">{{ number_format($tauxOccupation, 1, ',', ' ') }}%</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ min($tauxOccupation, 100) }}%"></div>
                </div>
            </div>
            <div class="bg-pink-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-pink-700 mb-2">Budget disponible</h3>
                <p class="text-2xl font-bold text-pink-800">{{ number_format($budgetDisponible, 2, ',', ' ') }} €</p>
                @if($departement->budget > 0)
                <div class="text-sm text-gray-500 mt-1">
                    {{ number_format(($budgetDisponible / $departement->budget) * 100, 1, ',', ' ') }}% du budget total
                </div>
                @endif
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-yellow-700 mb-2">Nombre d'employés</h3>
                <p class="text-2xl font-bold text-yellow-800">{{ $nombreEmployes }}</p>
                @if($departement->limite_employes > 0)
                <div class="text-sm text-gray-500 mt-1">
                    {{ number_format(($nombreEmployes / $departement->limite_employes) * 100, 1, ',', ' ') }}% de la capacité
                </div>
                @endif
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-red-700 mb-2">Sous-départements</h3>
                <p class="text-2xl font-bold text-red-800">{{ $nombreSousDepartements }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Employés du département</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nom</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Prénom</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Poste</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date d'embauche</th>
                            <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($employes as $employe)
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $employe->nom }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $employe->prenom }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $employe->email }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $employe->poste }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $employe->date_embauche ? $employe->date_embauche->format('d/m/Y') : 'N/A' }}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">
                                <a href="#" class="text-blue-600 hover:text-blue-900">Détails</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-3 px-4 text-sm text-gray-500 text-center">Aucun employé trouvé</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $employes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
