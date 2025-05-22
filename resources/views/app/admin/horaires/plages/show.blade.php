<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Détails de la plage horaire') }}: {{ $plageHoraire->nom }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.horaires.plages.edit', $plageHoraire) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-2"></i>{{ __('Modifier') }}
                </a>
                <form action="{{ route('admin.horaires.plages.destroy', $plageHoraire) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette plage horaire ?')">
                        <i class="fas fa-trash mr-2"></i>{{ __('Supprimer') }}
                    </button>
                </form>
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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Informations générales') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-600">{{ __('Nom') }}</p>
                            <p class="font-semibold">
                                @if ($plageHoraire->couleur)
                                    <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: {{ $plageHoraire->couleur }}"></span>
                                @endif
                                {{ $plageHoraire->nom }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Type') }}</p>
                            <p class="font-semibold">
                                @if ($plageHoraire->type == 'standard')
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Standard</span>
                                @else
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">Spécial</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Heure de début') }}</p>
                            <p class="font-semibold">{{ $plageHoraire->getHeureDebutFormatee() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Heure de fin') }}</p>
                            <p class="font-semibold">{{ $plageHoraire->getHeureFinFormatee() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Durée') }}</p>
                            <p class="font-semibold">{{ $plageHoraire->getDureeFormatee() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Est une pause') }}</p>
                            <p class="font-semibold">
                                @if ($plageHoraire->est_pause)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">Oui</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded">Non</span>
                                @endif
                            </p>
                        </div>

                        @if ($plageHoraire->est_pause && $plageHoraire->duree_max_minutes)
                            <div>
                                <p class="text-gray-600">{{ __('Durée maximale') }}</p>
                                <p class="font-semibold">
                                    {{ floor($plageHoraire->duree_max_minutes / 60) }}h{{ $plageHoraire->duree_max_minutes % 60 }}m
                                </p>
                            </div>
                        @endif
                    </div>

                    @if ($plageHoraire->description)
                        <div class="mt-6">
                            <p class="text-gray-600">{{ __('Description') }}</p>
                            <p class="mt-1">{{ $plageHoraire->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Employés utilisant cette plage horaire') }}</h3>
                    
                    @if ($employes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Matricule') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Département') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employes as $employe)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">{{ $employe->getNomComplet() }}</td>
                                            <td class="py-3 px-4">{{ $employe->matricule }}</td>
                                            <td class="py-3 px-4">{{ $employe->departement ? $employe->departement->nom : '-' }}</td>
                                            <td class="py-3 px-4">
                                                <a href="{{ route('admin.horaires.planning.show', $employe) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-calendar-alt mr-1"></i>{{ __('Planning') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('Aucun employé n\'utilise cette plage horaire.') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.horaires.plages.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour à la liste') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
