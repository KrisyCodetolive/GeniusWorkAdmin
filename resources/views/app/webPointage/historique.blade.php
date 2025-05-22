@extends('layouts.app')

@section('title', 'Historique des présences')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Historique des présences</h1>
    
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filtres</h2>
        
        <form action="{{ route('webPointage.historique') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input type="date" id="date_debut" name="date_debut" value="{{ request('date_debut') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div>
                    <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input type="date" id="date_fin" name="date_fin" value="{{ request('date_fin') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type de présence</label>
                    <select id="type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Tous</option>
                        <option value="entree" {{ request('type') == 'entree' ? 'selected' : '' }}>Entrée</option>
                        <option value="sortie" {{ request('type') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                        <option value="pause_debut" {{ request('type') == 'pause_debut' ? 'selected' : '' }}>Début de pause</option>
                        <option value="pause_fin" {{ request('type') == 'pause_fin' ? 'selected' : '' }}>Fin de pause</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Filtrer
                </button>
            </div>
        </form>
    </div>
    
    <!-- Statistiques -->
    @if($totalHeures)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Total des heures</p>
                <p class="text-xl font-bold">{{ $totalHeures }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Jours travaillés</p>
                <p class="text-xl font-bold">{{ $joursTravailles }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Temps de pause</p>
                <p class="text-xl font-bold">{{ $heuresPause }}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Moyenne par jour</p>
                <p class="text-xl font-bold">{{ $moyenneParJour }}</p>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Tableau des présences -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date et heure
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Site
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Statut
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Localisation
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($presences as $presence)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $presence->date_heure->format('d/m/Y') }}</div>
                        <div class="text-sm text-gray-500">{{ $presence->date_heure->format('H:i:s') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($presence->type == 'entree') bg-green-100 text-green-800 
                            @elseif($presence->type == 'sortie') bg-red-100 text-red-800 
                            @elseif($presence->type == 'pause_debut') bg-yellow-100 text-yellow-800 
                            @elseif($presence->type == 'pause_fin') bg-blue-100 text-blue-800 
                            @endif">
                            @if($presence->type == 'entree') Entrée 
                            @elseif($presence->type == 'sortie') Sortie 
                            @elseif($presence->type == 'pause_debut') Début de pause 
                            @elseif($presence->type == 'pause_fin') Fin de pause 
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $presence->site->nom ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($presence->statut == 'valide') bg-green-100 text-green-800 
                            @elseif($presence->statut == 'enregistre') bg-blue-100 text-blue-800 
                            @elseif($presence->statut == 'annule') bg-red-100 text-red-800 
                            @endif">
                            {{ ucfirst($presence->statut) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($presence->hasLocation())
                        <a href="https://www.google.com/maps?q={{ $presence->latitude }},{{ $presence->longitude }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                            Voir sur la carte
                        </a>
                        @if($presence->distance_site)
                        <div class="text-xs text-gray-500 mt-1">
                            {{ round($presence->distance_site) }} m du site
                        </div>
                        @endif
                        @else
                        <span class="text-gray-500">Non disponible</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                        Aucune présence trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $presences->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
