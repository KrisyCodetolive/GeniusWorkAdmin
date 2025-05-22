<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tableau de bord des horaires') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Statistiques générales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                                <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">{{ __('Employés') }}</h3>
                                <p class="text-2xl font-semibold text-gray-800">{{ $stats['employes_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                                <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">{{ __('Plages horaires') }}</h3>
                                <p class="text-2xl font-semibold text-gray-800">{{ $stats['plages_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10">
                                <svg class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">{{ __('Heures moyennes') }}</h3>
                                <p class="text-2xl font-semibold text-gray-800">{{ floor($stats['heures_moyennes'] / 60) }}h{{ $stats['heures_moyennes'] % 60 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                                <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">{{ __('Départements') }}</h3>
                                <p class="text-2xl font-semibold text-gray-800">{{ $stats['departements_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Accès rapides -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Accès rapides') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <a href="{{ route('admin.horaires.plages.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                <div class="p-2 rounded-full bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">{{ __('Plages horaires') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Gérer les plages horaires') }}</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.horaires.planning.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <div class="p-2 rounded-full bg-green-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">{{ __('Plannings') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Gérer les plannings') }}</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.horaires.planning.create') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                <div class="p-2 rounded-full bg-yellow-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">{{ __('Nouveau planning') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Créer un planning') }}</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.horaires.plages.create') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                                <div class="p-2 rounded-full bg-purple-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-semibold">{{ __('Nouvelle plage') }}</h4>
                                    <p class="text-sm text-gray-600">{{ __('Créer une plage horaire') }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Employés sans planning -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Employés sans planning') }}</h3>
                        
                        @if ($employesSansPlanning->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Matricule') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Département') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employesSansPlanning as $employe)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="py-3 px-4">{{ $employe->getNomComplet() }}</td>
                                                <td class="py-3 px-4">{{ $employe->matricule }}</td>
                                                <td class="py-3 px-4">{{ $employe->departement ? $employe->departement->nom : '-' }}</td>
                                                <td class="py-3 px-4">
                                                    <a href="{{ route('admin.horaires.planning.edit', $employe) }}" class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-calendar-plus mr-1"></i>{{ __('Créer') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            @if ($employesSansPlanning->count() > 5)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('admin.horaires.planning.index', ['sans_planning' => 1]) }}" class="text-blue-500 hover:text-blue-700">
                                        {{ __('Voir tous les employés sans planning') }} ({{ $stats['employes_sans_planning'] }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <p class="text-gray-500 italic">{{ __('Tous les employés ont un planning défini.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Départements -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Départements') }}</h3>
                        
                        @if ($departements->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-3 px-4 text-left">{{ __('Département') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Employés') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Heures moyennes') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($departements as $departement)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="py-3 px-4">{{ $departement->nom }}</td>
                                                <td class="py-3 px-4">{{ $departement->employes()->count() }}</td>
                                                <td class="py-3 px-4">
                                                    @php
                                                        $heuresMoyennes = app(App\Services\HoraireService::class)->calculerHeuresMoyennesDepartement($departement);
                                                    @endphp
                                                    {{ floor($heuresMoyennes / 60) }}h{{ $heuresMoyennes % 60 }}
                                                </td>
                                                <td class="py-3 px-4">
                                                    <a href="{{ route('admin.horaires.planning.departement', $departement) }}" class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-users mr-1"></i>{{ __('Plannings') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('Aucun département trouvé.') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Plages horaires récentes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Plages horaires récentes') }}</h3>
                        
                        @if ($plagesRecentes->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Type') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Horaire') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Utilisations') }}</th>
                                            <th class="py-3 px-4 text-left">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($plagesRecentes as $plage)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="py-3 px-4">
                                                    @if ($plage->couleur)
                                                        <span class="inline-block w-3 h-3 mr-2 rounded-full" style="background-color: {{ $plage->couleur }}"></span>
                                                    @endif
                                                    {{ $plage->nom }}
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if ($plage->type == 'standard')
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Standard</span>
                                                    @else
                                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Spécial</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">{{ $plage->getPlageFormatee() }}</td>
                                                <td class="py-3 px-4">{{ $plage->getEmployesCount() }}</td>
                                                <td class="py-3 px-4">
                                                    <a href="{{ route('admin.horaires.plages.show', $plage) }}" class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-eye mr-1"></i>{{ __('Voir') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('Aucune plage horaire trouvée.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
