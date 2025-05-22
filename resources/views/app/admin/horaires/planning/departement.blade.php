<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Planning du département') }}: {{ $departement->nom }}
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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Informations du département') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-gray-600">{{ __('Nom du département') }}</p>
                            <p class="font-semibold">{{ $departement->nom }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Nombre d\'employés') }}</p>
                            <p class="font-semibold">{{ $departement->employes()->count() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Responsable') }}</p>
                            <p class="font-semibold">
                                @if ($departement->responsable)
                                    {{ $departement->responsable->getNomComplet() }}
                                @else
                                    {{ __('Non défini') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">{{ __('Actions groupées') }}</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-3">{{ __('Appliquer un modèle à tous les employés') }}</h4>
                            <form action="{{ route('admin.horaires.planning.applyTemplate') }}" method="POST">
                                @csrf
                                <input type="hidden" name="departement_id" value="{{ $departement->id }}">
                                
                                <div class="mb-3">
                                    <x-label for="template_id" :value="__('Modèle de planning')" />
                                    <select id="template_id" name="template_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">{{ __('Sélectionner un modèle') }}</option>
                                        <option value="standard_35h">{{ __('Standard 35h (Lun-Ven, 9h-17h)') }}</option>
                                        <option value="standard_39h">{{ __('Standard 39h (Lun-Ven, 8h30-17h30)') }}</option>
                                        <option value="service_client">{{ __('Service client (Lun-Sam, horaires variables)') }}</option>
                                        <option value="production">{{ __('Production (2x8, équipes alternées)') }}</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center justify-end">
                                    <x-button>
                                        {{ __('Appliquer à tous') }}
                                    </x-button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-3">{{ __('Copier le planning d\'un employé') }}</h4>
                            <form action="{{ route('admin.horaires.planning.copyToDepartment') }}" method="POST">
                                @csrf
                                <input type="hidden" name="departement_id" value="{{ $departement->id }}">
                                
                                <div class="mb-3">
                                    <x-label for="source_employe_id" :value="__('Employé source')" />
                                    <select id="source_employe_id" name="source_employe_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">{{ __('Sélectionner un employé') }}</option>
                                        @foreach ($departement->employes as $employe)
                                            <option value="{{ $employe->id }}">
                                                {{ $employe->getNomComplet() }} ({{ $employe->matricule }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="flex items-center justify-end">
                                    <x-button>
                                        {{ __('Copier à tous') }}
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Employés du département') }}</h3>
                    
                    @if ($employes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Matricule') }}</th>
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
                        <p class="text-gray-500 italic">{{ __('Aucun employé dans ce département.') }}</p>
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
