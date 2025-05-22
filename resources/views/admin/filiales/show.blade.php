@extends('layouts.app')

@section('title', 'Détails de la filiale ' . $filiale->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.filiales.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $filiale->nom }}</h1>
        @if($filiale->estSiegeSocial())
            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Siège social</span>
        @endif
        @if($filiale->statut === 'actif')
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
                            <dd class="text-sm text-gray-900">{{ $filiale->code }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                            <dd class="text-sm text-gray-900">{{ $filiale->getAdresseComplete() ?: 'Non spécifiée' }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                            <dd class="text-sm text-gray-900">{{ $filiale->telephone ?: 'Non spécifié' }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $filiale->email ?: 'Non spécifié' }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Site web</dt>
                            <dd class="text-sm text-gray-900">
                                @if($filiale->site_web)
                                    <a href="{{ $filiale->site_web }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $filiale->site_web }}</a>
                                @else
                                    Non spécifié
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Nombre d'employés</dt>
                            <dd class="text-sm text-gray-900">{{ $filiale->getNombreEmployesActifs() }}</dd>
                        </div>
                        @if(isset($filiale->configuration['limite_employes']))
                            <div class="py-3 flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Limite d'employés</dt>
                                <dd class="text-sm text-gray-900">{{ $filiale->configuration['limite_employes'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <a href="{{ route('admin.filiales.edit', $filiale) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        Modifier
                    </a>
                </div>
            </div>

            @if($filiale->description)
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-700">Description</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-700">{{ $filiale->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Horaires -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-700">Horaires</h2>
                </div>
                <div class="p-6">
                    @if(isset($filiale->configuration['horaires']))
                        <dl class="divide-y divide-gray-200">
                            @if(isset($filiale->configuration['horaires']['debut']) && isset($filiale->configuration['horaires']['fin']))
                                <div class="py-3 flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Heures de travail</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $filiale->configuration['horaires']['debut'] }} - {{ $filiale->configuration['horaires']['fin'] }}
                                    </dd>
                                </div>
                            @endif
                            
                            @if(isset($filiale->configuration['horaires']['jours_travail']) && count($filiale->configuration['horaires']['jours_travail']) > 0)
                                <div class="py-3">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Jours de travail</dt>
                                    <dd class="text-sm text-gray-900">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($filiale->configuration['horaires']['jours_travail'] as $jour)
                                                <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">{{ ucfirst($jour) }}</span>
                                            @endforeach
                                        </div>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-sm text-gray-500">Aucun horaire défini</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="w-full md:w-2/3">
            <!-- Responsables -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Responsables</h2>
                    <button type="button" onclick="document.getElementById('modal-assigner-responsable').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded-lg">
                        Assigner
                    </button>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($responsables as $responsable)
                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">{{ $responsable->nom }} {{ $responsable->prenom }}</h3>
                                <p class="text-xs text-gray-500">
                                    @if($responsable->pivot->role === \App\Models\Filiale::ROLE_RESPONSABLE_PRINCIPAL)
                                        Responsable principal
                                    @else
                                        Responsable adjoint
                                    @endif
                                    depuis le {{ \Carbon\Carbon::parse($responsable->pivot->date_debut)->format('d/m/Y') }}
                                </p>
                            </div>
                            <form action="{{ route('admin.filiales.terminer-mandat', ['filiale' => $filiale->id, 'employeur' => $responsable->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir terminer le mandat de ce responsable?');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    Terminer le mandat
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">
                            <p>Aucun responsable assigné à cette filiale.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Départements -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Départements</h2>
                    <a href="{{ route('admin.departements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded-lg">
                        Ajouter
                    </a>
                </div>
                @if($departements->count() > 0)
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
                                @foreach($departements as $departement)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $departement->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $departement->nom }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($departement->responsable)
                                                {{ $departement->responsable->nom }} {{ $departement->responsable->prenom }}
                                            @else
                                                <span class="text-gray-400">Non assigné</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $departement->employeurs_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.departements.show', $departement) }}" class="text-indigo-600 hover:text-indigo-900" title="Voir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </a>
                                                <a href="{{ route('admin.departements.edit', $departement) }}" class="text-blue-600 hover:text-blue-900" title="Modifier">
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
                        <p>Aucun département n'a été créé pour cette filiale.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Assigner Responsable -->
<div id="modal-assigner-responsable" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-700">Assigner un responsable</h3>
            <button type="button" onclick="document.getElementById('modal-assigner-responsable').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.filiales.assigner-responsable', $filiale) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label for="employeur_id" class="block text-sm font-medium text-gray-700 mb-1">Employé <span class="text-red-600">*</span></label>
                <select name="employeur_id" id="employeur_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Sélectionner un employé</option>
                    @foreach($filiale->entreprise->employeurs()->where('statut', 'actif')->orderBy('nom')->get() as $employeur)
                        <option value="{{ $employeur->id }}">{{ $employeur->nom }} {{ $employeur->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rôle <span class="text-red-600">*</span></label>
                <select name="role" id="role" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="{{ \App\Models\Filiale::ROLE_RESPONSABLE_PRINCIPAL }}">Responsable principal</option>
                    <option value="{{ \App\Models\Filiale::ROLE_RESPONSABLE_ADJOINT }}">Responsable adjoint</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="document.getElementById('modal-assigner-responsable').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-2">
                    Annuler
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Assigner
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
