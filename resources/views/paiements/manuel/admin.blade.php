@extends('layouts.app')

@section('title', 'Administration des paiements manuels')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Administration des paiements manuels</h1>
                
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <h2 class="text-xl font-medium text-gray-700">Liste des paiements en attente</h2>
                        <div class="mt-3 sm:mt-0">
                            <form action="{{ route('paiements.manuel.admin') }}" method="GET" class="flex items-center space-x-2">
                                <select name="statut" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block sm:text-sm border-gray-300 rounded-md">
                                    <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                    <option value="valide" {{ request('statut') === 'valide' ? 'selected' : '' }}>Validés</option>
                                    <option value="rejete" {{ request('statut') === 'rejete' ? 'selected' : '' }}>Rejetés</option>
                                    <option value="tous" {{ request('statut') === 'tous' ? 'selected' : '' }}>Tous</option>
                                </select>
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Filtrer
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    @if($paiements->isEmpty())
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600">Aucun paiement manuel {{ request('statut') === 'tous' ? '' : request('statut', 'en attente') }} trouvé.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paiements as $paiement)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $paiement->reference }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $paiement->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $paiement->user->name }}
                                                <div class="text-xs text-gray-400">{{ $paiement->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($paiement->montant, 0, ',', ' ') }} {{ $paiement->devise }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($paiement->methode) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($paiement->statut === 'en_attente')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        En attente
                                                    </span>
                                                @elseif($paiement->statut === 'valide')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Validé
                                                    </span>
                                                @elseif($paiement->statut === 'rejete')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Rejeté
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('paiements.manuel.admin.details', $paiement->id) }}" class="text-blue-600 hover:text-blue-900">
                                                        Détails
                                                    </a>
                                                    
                                                    @if($paiement->statut === 'en_attente')
                                                        <form action="{{ route('paiements.manuel.admin.valider', $paiement->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                                Valider
                                                            </button>
                                                        </form>
                                                        
                                                        <form action="{{ route('paiements.manuel.admin.rejeter', $paiement->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                                Rejeter
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $paiements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
