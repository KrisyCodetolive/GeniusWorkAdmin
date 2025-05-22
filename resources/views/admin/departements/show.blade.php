@extends('layouts.app')

@section('title', 'Détails du département ' . $departement->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.departements.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $departement->nom }}</h1>
        @if($departement->statut === 'actif')
            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
        @else
            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Informations générales -->
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-700">Informations générales</h2>
                </div>
                <div class="p-6">
                    <dl class="divide-y divide-gray-200">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Code</dt>
                            <dd class="text-sm text-gray-900">{{ $departement->code }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Filiale</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('admin.filiales.show', $departement->filiale) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $departement->filiale->nom }}
                                </a>
                            </dd>
                        </div>
                        @if($departement->departementParent)
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Département parent</dt>
                                <dd class="text-sm text-gray-900">
                                    <a href="{{ route('admin.departements.show', $departement->departementParent) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $departement->departementParent->nom }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Responsable</dt>
                            <dd class="text-sm text-gray-900">
                                @if($responsable)
                                    {{ $responsable->nom }} {{ $responsable->prenom }}
                                @else
                                    <span class="text-gray-400">Non assigné</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Nombre d'employés</dt>
                            <dd class="text-sm text-gray-900">{{ $employeurs->total() }}</dd>
                        </div>
                        @if(isset($departement->configuration['limite_employes']))
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Limite d'employés</dt>
                                <dd class="text-sm text-gray-900">{{ $departement->configuration['limite_employes'] }}</dd>
                            </div>
                        @endif
                        @if(isset($departement->configuration['budget']))
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Budget</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($departement->configuration['budget'], 2, ',', ' ') }} €</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <a href="{{ route('admin.departements.edit', $departement) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Modifier
                    </a>
                </div>
            </div>

            @if($departement->description)
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-700">Description</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-700">{{ $departement->description }}</p>
                    </div>
                </div>
            @endif

            @if(isset($departement->configuration['objectifs']))
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-700">Objectifs</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-700">{{ $departement->configuration['objectifs'] }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="w-full md:w-2/3">
            <!-- Sous-départements -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Sous-départements</h2>
                    <a href="{{ route('admin.departements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded-lg">
                        Ajouter
                    </a>
                </div>
                @if($sousDepartements->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employés</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sousDepartements as $sousDept)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $sousDept->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sousDept->nom }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($sousDept->responsable)
                                                {{ $sousDept->responsable->nom }} {{ $sousDept->responsable->prenom }}
                                            @else
                                                <span class="text-gray-400">Non assigné</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $sousDept->employeurs_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.departements.show', $sousDept) }}" class="text-indigo-600 hover:text-indigo-900" title="Voir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </a>
                                                <a href="{{ route('admin.departements.edit', $sousDept) }}" class="text-blue-600 hover:text-blue-900" title="Modifier">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        <p>Aucun sous-département n'a été créé.</p>
                    </div>
                @endif
            </div>

            <!-- Employés -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-700">Employés</h2>
                </div>
                @if($employeurs->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($employeurs as $employeur)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $employeur->nom }} {{ $employeur->prenom }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employeur->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employeur->telephone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employeur->poste }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($employeur->statut === 'actif')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $employeurs->links() }}
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        <p>Aucun employé n'est assigné à ce département.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
