@extends('layouts.portail-employe')

@section('title', 'Mes dépenses')

@section('content')
@php
    use App\Models\DemandeDepense;

    $badge = [
        'gray' => ['bg-slate-100 text-slate-700', 'bg-slate-400'],
        'warning' => ['bg-amber-50 text-amber-800', 'bg-amber-500'],
        'info' => ['bg-sky-50 text-sky-800', 'bg-sky-500'],
        'success' => ['bg-emerald-50 text-emerald-800', 'bg-emerald-500'],
        'danger' => ['bg-red-50 text-red-800', 'bg-red-500'],
    ];
    $statutActif = request('statut');

    // Ce que l'employé doit faire lui-même : affiché sur la ligne, sans bloc à part.
    $action = [
        DemandeDepense::STATUT_BROUILLON => 'À soumettre',
        DemandeDepense::STATUT_APPROUVEE => 'À signer',
        DemandeDepense::STATUT_PAYEE => 'Reçus à joindre',
    ];
@endphp

<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Mes demandes</h1>
        <p class="text-slate-600 mt-0.5">Suivez vos demandes d'argent auprès de l'administration.</p>
    </div>
    <a href="{{ route('employe.depenses.create') }}"
       class="btn-primary shrink-0 inline-flex items-center gap-2 px-4 sm:px-5 py-3 rounded-xl text-white font-medium shadow-sm">
        <i data-lucide="plus" class="h-5 w-5"></i>
        <span class="hidden sm:inline">Nouvelle demande</span>
    </a>
</div>

{{-- Filtre : menu simple sur mobile, pastilles sur grand écran --}}
<form method="GET" class="sm:hidden mb-4">
    <select name="statut" onchange="this.form.submit()" class="w-full rounded-xl">
        <option value="">Toutes les demandes</option>
        @foreach ($statuts as $valeur => $libelle)
            <option value="{{ $valeur }}" @selected($statutActif === $valeur)>{{ $libelle }}</option>
        @endforeach
    </select>
</form>
<div class="hidden sm:flex flex-wrap gap-2 mb-5">
    <a href="{{ route('employe.depenses.index') }}"
       class="px-3.5 py-1.5 rounded-full text-sm font-medium {{ $statutActif ? 'bg-white text-slate-700 border border-slate-200 hover:border-[#3a5faa]' : 'bg-[#3a5faa] text-white' }}">Toutes</a>
    @foreach ($statuts as $valeur => $libelle)
        <a href="{{ route('employe.depenses.index', ['statut' => $valeur]) }}"
           class="px-3.5 py-1.5 rounded-full text-sm font-medium {{ $statutActif === $valeur ? 'bg-[#3a5faa] text-white' : 'bg-white text-slate-700 border border-slate-200 hover:border-[#3a5faa]' }}">{{ $libelle }}</a>
    @endforeach
</div>

@if ($demandes->isEmpty())
    <div class="card py-14 px-6 text-center">
        <div class="mx-auto h-14 w-14 rounded-2xl bg-[#eef2fa] flex items-center justify-center mb-4">
            <i data-lucide="inbox" class="h-7 w-7 text-[#3a5faa]"></i>
        </div>
        <p class="font-semibold text-slate-800 text-lg">Aucune demande{{ $statutActif ? ' avec ce statut' : '' }}</p>
        <p class="text-slate-600 mt-1 mb-6">Faites une demande d'argent, elle apparaîtra ici avec son suivi.</p>
        <a href="{{ route('employe.depenses.create') }}"
           class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-medium">
            <i data-lucide="plus" class="h-5 w-5"></i> Nouvelle demande
        </a>
    </div>
@else
    <div class="card divide-y divide-slate-100 overflow-hidden">
        @foreach ($demandes as $demande)
            @php [$fond, $point] = $badge[DemandeDepense::COULEURS_STATUT[$demande->statut] ?? 'gray']; @endphp
            <a href="{{ route('employe.depenses.show', $demande) }}"
               class="flex items-center gap-4 px-4 sm:px-6 py-4 hover:bg-[#eef2fa]/60 transition-colors">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900 truncate">{{ $demande->objet }}</p>
                    <p class="text-sm text-slate-500 mt-0.5 truncate">{{ $demande->reference }} · {{ $demande->created_at->format('d/m/Y') }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2 sm:hidden">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $fond }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $point }}"></span>{{ $statuts[$demande->statut] ?? $demande->statut }}
                        </span>
                        @isset($action[$demande->statut])
                            <span class="text-xs font-semibold text-[#3a5faa]">{{ $action[$demande->statut] }}</span>
                        @endisset
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-2 shrink-0">
                    @isset($action[$demande->statut])
                        <span class="text-xs font-semibold text-[#3a5faa] bg-[#eef2fa] px-2.5 py-1 rounded-full">{{ $action[$demande->statut] }}</span>
                    @endisset
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $fond }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $point }}"></span>{{ $statuts[$demande->statut] ?? $demande->statut }}
                    </span>
                </div>

                <p class="shrink-0 text-right font-bold text-slate-900 sm:w-28">
                    {{ number_format((float) $demande->montant, 0, ',', ' ') }}
                    <span class="block text-xs font-medium text-slate-500">{{ $demande->devise }}</span>
                </p>
                <i data-lucide="chevron-right" class="h-5 w-5 text-slate-300 shrink-0 hidden sm:block"></i>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $demandes->links() }}</div>
@endif
@endsection
