@extends('layouts.portail-employe')

@section('title', 'Mes dépenses')

@section('content')
@php
    $badgeClasses = [
        'gray' => 'bg-gray-100 text-gray-600',
        'warning' => 'bg-amber-100 text-amber-700',
        'info' => 'bg-blue-100 text-blue-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'danger' => 'bg-red-100 text-red-700',
    ];
    $statutActif = request('statut');
@endphp

<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Mes dépenses</h1>
        <p class="text-sm text-gray-500 mt-1">Créez, suivez et justifiez vos sorties d'argent.</p>
    </div>
    <a href="{{ route('employe.depenses.create') }}"
       class="btn-primary shrink-0 inline-flex items-center px-4 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm">
        <i data-lucide="plus" class="h-4 w-4 sm:mr-2"></i>
        <span class="hidden sm:inline">Nouvelle demande</span>
    </a>
</div>

{{-- Filtres par statut --}}
<div class="flex gap-2 overflow-x-auto pb-1 mb-6 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap">
    <a href="{{ route('employe.depenses.index') }}"
       class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium {{ $statutActif ? 'bg-white text-gray-600 border border-gray-200' : 'bg-indigo-600 text-white' }}">
        Toutes
    </a>
    @foreach ($statuts as $valeur => $libelle)
        <a href="{{ route('employe.depenses.index', ['statut' => $valeur]) }}"
           class="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium {{ $statutActif === $valeur ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
            {{ $libelle }}
        </a>
    @endforeach
</div>

@if ($demandes->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 py-16 text-center">
        <div class="mx-auto h-14 w-14 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
            <i data-lucide="inbox" class="h-7 w-7 text-indigo-400"></i>
        </div>
        <p class="font-medium text-gray-700">Aucune demande{{ $statutActif ? ' avec ce statut' : '' }}</p>
        <p class="text-sm text-gray-400 mt-1 mb-6">Vos demandes de dépense apparaîtront ici.</p>
        <a href="{{ route('employe.depenses.create') }}"
           class="btn-primary inline-flex items-center px-5 py-2.5 rounded-xl text-white text-sm font-medium">
            <i data-lucide="plus" class="h-4 w-4 mr-2"></i> Créer ma première demande
        </a>
    </div>
@else
    {{-- Mobile : cartes --}}
    <div class="space-y-3 md:hidden">
        @foreach ($demandes as $demande)
            @php $couleur = \App\Models\DemandeDepense::COULEURS_STATUT[$demande->statut] ?? 'gray'; @endphp
            <a href="{{ route('employe.depenses.show', $demande) }}"
               class="block bg-white rounded-2xl border border-gray-200 p-4 active:bg-indigo-50/50 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 text-sm">{{ $demande->reference }}</p>
                        <p class="text-sm text-gray-600 truncate mt-0.5">{{ $demande->objet }}</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClasses[$couleur] }}">
                        {{ $statuts[$demande->statut] ?? $demande->statut }}
                    </span>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <span class="font-semibold text-gray-900">
                        {{ number_format((float) $demande->montant, 0, ',', ' ') }} <span class="text-xs text-gray-400 font-normal">{{ $demande->devise }}</span>
                    </span>
                    <span class="text-xs text-gray-400">{{ $demande->created_at->format('d/m/Y') }}</span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Desktop : tableau --}}
    <div class="hidden md:block bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left">
                    <th class="px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Référence</th>
                    <th class="px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Objet</th>
                    <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Montant</th>
                    <th class="px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Créée le</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($demandes as $demande)
                    @php $couleur = \App\Models\DemandeDepense::COULEURS_STATUT[$demande->statut] ?? 'gray'; @endphp
                    <tr class="hover:bg-indigo-50/40 transition-colors cursor-pointer"
                        onclick="window.location='{{ route('employe.depenses.show', $demande) }}'">
                        <td class="px-6 py-4 font-semibold text-gray-900 text-sm whitespace-nowrap">{{ $demande->reference }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $demande->objet }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900 whitespace-nowrap">
                            {{ number_format((float) $demande->montant, 0, ',', ' ') }} {{ $demande->devise }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClasses[$couleur] }}">
                                {{ $statuts[$demande->statut] ?? $demande->statut }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <i data-lucide="chevron-right" class="h-4 w-4 text-gray-300 inline"></i>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $demandes->links() }}</div>
@endif
@endsection
