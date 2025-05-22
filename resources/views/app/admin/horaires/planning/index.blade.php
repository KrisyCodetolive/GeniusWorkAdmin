<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestion des plannings') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.horaires.planning.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-plus-circle mr-2"></i>{{ __('Nouveau planning') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Rechercher un planning') }}</h3>
                    <form action="{{ route('admin.horaires.planning.index') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-label for="departement_id" :value="__('Département')" />
                                <select id="departement_id" name="departement_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">{{ __('Tous les départements') }}</option>
                                    @foreach ($departements as $departement)
                                        <option value="{{ $departement->id }}" {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                                            {{ $departement->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-label for="search" :value="__('Recherche par nom ou matricule')" />
                                <x-input id="search" class="block mt-1 w-full" type="text" name="search" :value="request('search')" placeholder="Nom, prénom ou matricule..." />
                            </div>

                            <div class="flex items-end">
                                <x-button class="w-full justify-center">
                                    <i class="fas fa-search mr-2"></i>{{ __('Rechercher') }}
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Plannings des employés') }}</h3>
                    
                    @if ($employes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Matricule') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Département') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Plages horaires') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heures/semaine') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employes as $employe)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">{{ $employe->getNomComplet() }}</td>
                                            <td class="py-3 px-4">{{ $employe->matricule }}</td>
                                            <td class="py-3 px-4">{{ $employe->departement ? $employe->departement->nom : '-' }}</td>
                                            <td class="py-3 px-4">{{ $employe->plagesHoraires()->count() }}</td>
                                            <td class="py-3 px-4">
                                                @php
                                                    $heuresHebdo = app(App\Services\HoraireService::class)->calculerHeuresHebdomadaires($employe);
                                                @endphp
                                                {{ floor($heuresHebdo / 60) }}h{{ $heuresHebdo % 60 }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.horaires.planning.show', $employe) }}" class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.horaires.planning.edit', $employe) }}" class="text-yellow-500 hover:text-yellow-700">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.horaires.planning.reset', $employe) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser le planning de cet employé ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $employes->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('Aucun employé trouvé.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
