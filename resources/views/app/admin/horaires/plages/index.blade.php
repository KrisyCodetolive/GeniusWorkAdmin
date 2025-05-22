<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestion des plages horaires') }}
            </h2>
            <a href="{{ route('admin.horaires.plages.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>{{ __('Nouvelle plage horaire') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Plages horaires standard') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de début') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de fin') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Durée') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Employés') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plagesStandard as $plage)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                @if ($plage->couleur)
                                                    <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: {{ $plage->couleur }}"></span>
                                                @endif
                                                {{ $plage->nom }}
                                            </td>
                                            <td class="py-3 px-4">{{ $plage->getHeureDebutFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getHeureFinFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getDureeFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getEmployesCount() }}</td>
                                            <td class="py-3 px-4 flex space-x-2">
                                                <a href="{{ route('admin.horaires.plages.show', $plage) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.horaires.plages.edit', $plage) }}" class="text-yellow-500 hover:text-yellow-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.horaires.plages.destroy', $plage) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette plage horaire ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 px-4 text-center">{{ __('Aucune plage horaire standard trouvée') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Plages horaires spéciales') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de début') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de fin') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Durée') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Employés') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plagesSpeciales as $plage)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                @if ($plage->couleur)
                                                    <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: {{ $plage->couleur }}"></span>
                                                @endif
                                                {{ $plage->nom }}
                                            </td>
                                            <td class="py-3 px-4">{{ $plage->getHeureDebutFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getHeureFinFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getDureeFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getEmployesCount() }}</td>
                                            <td class="py-3 px-4 flex space-x-2">
                                                <a href="{{ route('admin.horaires.plages.show', $plage) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.horaires.plages.edit', $plage) }}" class="text-yellow-500 hover:text-yellow-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.horaires.plages.destroy', $plage) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette plage horaire ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 px-4 text-center">{{ __('Aucune plage horaire spéciale trouvée') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Plages de pause') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left">{{ __('Nom') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de début') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Heure de fin') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Durée') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Durée max.') }}</th>
                                        <th class="py-3 px-4 text-left">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plagesPause as $plage)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4">
                                                @if ($plage->couleur)
                                                    <span class="inline-block w-4 h-4 mr-2 rounded-full" style="background-color: {{ $plage->couleur }}"></span>
                                                @endif
                                                {{ $plage->nom }}
                                            </td>
                                            <td class="py-3 px-4">{{ $plage->getHeureDebutFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getHeureFinFormatee() }}</td>
                                            <td class="py-3 px-4">{{ $plage->getDureeFormatee() }}</td>
                                            <td class="py-3 px-4">
                                                @if ($plage->duree_max_minutes)
                                                    {{ floor($plage->duree_max_minutes / 60) }}h{{ $plage->duree_max_minutes % 60 }}m
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 flex space-x-2">
                                                <a href="{{ route('admin.horaires.plages.show', $plage) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.horaires.plages.edit', $plage) }}" class="text-yellow-500 hover:text-yellow-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.horaires.plages.destroy', $plage) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette plage horaire ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 px-4 text-center">{{ __('Aucune plage de pause trouvée') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <a href="{{ route('admin.horaires.plages.fusion.form') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-object-group mr-2"></i>{{ __('Fusionner des plages horaires') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
