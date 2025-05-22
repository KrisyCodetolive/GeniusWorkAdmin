<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Planning de') }} {{ $employe->getNomComplet() }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.horaires.planning.edit', $employe) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-2"></i>{{ __('Modifier') }}
                </a>
                <form action="{{ route('admin.horaires.planning.reset', $employe) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser le planning de cet employé ?')">
                        <i class="fas fa-trash mr-2"></i>{{ __('Réinitialiser') }}
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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Informations de l\'employé') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-gray-600">{{ __('Nom complet') }}</p>
                            <p class="font-semibold">{{ $employe->getNomComplet() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Matricule') }}</p>
                            <p class="font-semibold">{{ $employe->matricule }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Département') }}</p>
                            <p class="font-semibold">{{ $employe->departement ? $employe->departement->nom : '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Heures hebdomadaires') }}</p>
                            <p class="font-semibold">
                                @php
                                    $heuresHebdo = app(App\Services\HoraireService::class)->calculerHeuresHebdomadaires($employe);
                                @endphp
                                {{ floor($heuresHebdo / 60) }}h{{ $heuresHebdo % 60 }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Nombre de plages') }}</p>
                            <p class="font-semibold">{{ $employe->plagesHoraires()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Planning hebdomadaire') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                        @php
                            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                            $joursTravail = $employe->joursTravail()->with('plagesHoraires')->get()->keyBy('jour');
                        @endphp

                        @foreach ($jours as $index => $jour)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-center mb-2">{{ $jour }}</h4>
                                
                                @if (isset($joursTravail[$index + 1]))
                                    @php
                                        $jourTravail = $joursTravail[$index + 1];
                                        $plages = $jourTravail->plagesHoraires;
                                    @endphp
                                    
                                    @if ($plages->count() > 0)
                                        <div class="space-y-2">
                                            @foreach ($plages as $plage)
                                                <div class="p-2 rounded-md text-sm" style="background-color: {{ $plage->couleur ?? '#3b82f6' }}; color: white;">
                                                    <div class="font-semibold">{{ $plage->nom }}</div>
                                                    <div>{{ $plage->getPlageFormatee() }}</div>
                                                    @if ($plage->est_pause)
                                                        <div class="mt-1 px-1 py-0.5 bg-white bg-opacity-20 rounded text-xs">
                                                            Pause
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <div class="mt-2 text-center text-sm">
                                            @php
                                                $dureeJour = app(App\Services\HoraireService::class)->calculerDureeJour($jourTravail);
                                            @endphp
                                            <span class="font-semibold">Total: {{ floor($dureeJour / 60) }}h{{ $dureeJour % 60 }}</span>
                                        </div>
                                    @else
                                        <p class="text-center text-gray-500 italic text-sm">{{ __('Aucune plage') }}</p>
                                    @endif
                                @else
                                    <p class="text-center text-gray-500 italic text-sm">{{ __('Jour non travaillé') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Détail des plages horaires') }}</h3>
                    
                    @if ($employe->plagesHoraires()->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Jour') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Type') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Horaire') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Durée') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Pause') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($joursTravail as $jourTravail)
                                        @foreach ($jourTravail->plagesHoraires as $plage)
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="py-3 px-4">{{ $jours[$jourTravail->jour - 1] }}</td>
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
                                                <td class="py-3 px-4">{{ $plage->getDureeFormatee() }}</td>
                                                <td class="py-3 px-4">
                                                    @if ($plage->est_pause)
                                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Oui</span>
                                                    @else
                                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Non</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('Aucune plage horaire définie pour cet employé.') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.horaires.planning.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Retour à la liste') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
