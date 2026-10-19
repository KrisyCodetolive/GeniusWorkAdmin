@extends('layouts.portail-employe')

@section('title', 'Mes demandes')

@section('content')
@php
    use App\Models\DemandeDepense;

    $badge = [
        'gray' => 'bg-slate-100 text-slate-700',
        'warning' => 'bg-amber-50 text-amber-800',
        'info' => 'bg-sky-50 text-sky-800',
        'success' => 'bg-emerald-50 text-emerald-800',
        'danger' => 'bg-red-50 text-red-800',
    ];
@endphp

<a href="{{ route('employe.depenses.create') }}"
   class="btn-primary w-full inline-flex justify-center items-center gap-2 px-6 py-4 rounded-2xl text-white text-lg font-semibold shadow-sm mb-6">
    <i data-lucide="plus" class="h-6 w-6"></i> Nouvelle demande
</a>

@if ($demandes->isEmpty())
    <div class="card py-12 px-6 text-center">
        <div class="mx-auto h-14 w-14 rounded-2xl bg-[#eef2fa] flex items-center justify-center mb-4">
            <i data-lucide="inbox" class="h-7 w-7 text-[#3a5faa]"></i>
        </div>
        <p class="font-semibold text-slate-800 text-lg">Aucune demande en cours</p>
        <p class="text-slate-600 mt-1">Votre prochaine demande apparaîtra ici avec son avancement.</p>
    </div>
@else
    <h1 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">En cours ({{ $demandes->count() }})</h1>

    <div class="space-y-4">
        @foreach ($demandes as $demande)
            @php
                $couleur = DemandeDepense::COULEURS_STATUT[$demande->statut] ?? 'gray';
                $action = $demande->actionRequise();
            @endphp
            <a href="{{ route('employe.depenses.show', $demande) }}"
               class="card block p-4 sm:p-5 hover:border-[#3a5faa] transition-colors {{ $action ? 'border-[#b9c7e6]' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 break-words">{{ $demande->objet }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $demande->reference }} · {{ $demande->created_at->format('d/m/Y') }} · {{ $demande->nomDemandeur() }}</p>
                    </div>
                    <p class="shrink-0 text-right text-lg font-bold text-slate-900 leading-tight">
                        {{ number_format((float) $demande->montant, 0, ',', ' ') }}
                        <span class="block text-xs font-medium text-slate-500">{{ $demande->devise }}</span>
                    </p>
                </div>

                @if ($demande->etapeSuivi() !== null)
                    <div class="mt-4">@include('employe.depenses._suivi', ['demande' => $demande, 'compact' => true])</div>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge[$couleur] }}">
                        {{ $statuts[$demande->statut] ?? $demande->statut }}
                    </span>
                    @if ($action)
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-[#3a5faa]">
                            {{ $action }} <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </span>
                    @else
                        <span class="text-xs text-slate-500">{{ $demande->messageSuivi() }}</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
