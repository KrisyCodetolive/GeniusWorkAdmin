@extends('layouts.portail-employe')

@section('title', 'Nouvelle demande')

@section('content')
<div class="max-w-xl mx-auto">
    <a href="{{ route('employe.depenses.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-[#3a5faa] mb-4">
        <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Mes demandes
    </a>

    <h1 class="text-2xl font-bold text-slate-900 mb-1">Nouvelle demande</h1>
    <p class="text-sm text-slate-600 mb-5">Elle part directement à la comptabilité.</p>

    <form action="{{ route('employe.depenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="card p-5 space-y-4">
            <div>
                <label for="montant" class="block text-sm font-medium text-slate-800 mb-1.5">Montant <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="number" id="montant" name="montant" value="{{ old('montant') }}" required min="1" step="any" inputmode="decimal" autofocus
                           class="w-full rounded-xl border-slate-300 text-2xl font-bold pr-20 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                    <span class="absolute inset-y-0 right-4 flex items-center text-sm text-slate-500">{{ $parametres->devise }}</span>
                </div>
            </div>

            <div>
                <label for="objet" class="block text-sm font-medium text-slate-800 mb-1.5">Pourquoi ? <span class="text-red-500">*</span></label>
                <input type="text" id="objet" name="objet" value="{{ old('objet') }}" required maxlength="255"
                       placeholder="Ex. Achat de fournitures de bureau"
                       class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
            </div>

            <div>
                <label for="beneficiaire" class="block text-sm font-medium text-slate-800 mb-1.5">À qui verser l'argent ? <span class="text-red-500">*</span></label>
                <input type="text" id="beneficiaire" name="beneficiaire" value="{{ old('beneficiaire') }}" required maxlength="255"
                       placeholder="Fournisseur ou personne à payer"
                       class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
            </div>

            <div>
                <label for="categorie_depense_id" class="block text-sm font-medium text-slate-800 mb-1.5">Catégorie <span class="text-red-500">*</span></label>
                <select id="categorie_depense_id" name="categorie_depense_id" required
                        class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                    <option value="">Sélectionnez…</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" @selected(old('categorie_depense_id') === $categorie->id)>{{ $categorie->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <details class="card px-5 py-4 group" @if (old('description') || old('date_besoin')) open @endif>
            <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-semibold text-slate-700">
                Ajouter des détails ou des pièces (facultatif)
                <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180"></i>
            </summary>

            <div class="space-y-4 mt-4">
                <div>
                    <label for="date_besoin" class="block text-sm font-medium text-slate-800 mb-1.5">Date de besoin</label>
                    <input type="date" id="date_besoin" name="date_besoin" value="{{ old('date_besoin') }}"
                           class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-800 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Contexte, justification…"
                              class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">{{ old('description') }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-slate-800">Pièces (devis, facture…)</span>
                        <button type="button" id="ajouter-piece" class="inline-flex items-center text-sm font-medium text-[#3a5faa] hover:text-[#2f4d8c]">
                            <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Ajouter
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">PDF ou image, 10 Mo max par fichier.</p>
                    <div id="pieces" class="space-y-3"></div>
                </div>
            </div>
        </details>

        <button type="submit" class="btn-primary w-full inline-flex justify-center items-center gap-2 px-6 py-4 rounded-2xl text-white text-lg font-semibold shadow-sm">
            <i data-lucide="send" class="h-5 w-5"></i> Envoyer la demande
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const conteneur = document.getElementById('pieces');
        let index = 0;

        document.getElementById('ajouter-piece').addEventListener('click', function () {
            const ligne = document.createElement('div');
            ligne.className = 'piece-ligne flex flex-col gap-2 bg-slate-50 rounded-xl p-3';
            ligne.innerHTML = `
                <select name="justificatifs[${index}][type]"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                    @foreach ($typesJustificatif as $valeur => $libelle)
                        <option value="{{ $valeur }}">{{ $libelle }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2 min-w-0">
                    <input type="file" name="justificatifs[${index}][fichier]" accept=".pdf,image/*" required
                           class="flex-1 min-w-0 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef2fa] file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[#3a5faa]">
                    <button type="button" class="shrink-0 p-1.5 text-slate-500 hover:text-red-500" aria-label="Retirer"
                            onclick="this.closest('.piece-ligne').remove()">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </div>`;
            conteneur.appendChild(ligne);
            lucide.createIcons();
            index++;
        });
    });
</script>
@endpush
