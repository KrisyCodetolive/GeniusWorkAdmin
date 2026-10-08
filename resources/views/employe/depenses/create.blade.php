@extends('layouts.portail-employe')

@section('title', 'Nouvelle dépense')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('employe.depenses.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-4">
        <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Mes dépenses
    </a>

    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Nouvelle demande</h1>
        <p class="text-sm text-gray-500 mt-1">
            Enregistrée en brouillon, la demande part ensuite à la comptabilité
            @if ((float) $parametres->seuil_validation_ceo > 0)
                puis au CEO au-delà de {{ number_format((float) $parametres->seuil_validation_ceo, 0, ',', ' ') }} {{ $parametres->devise }}
            @else
                puis au CEO
            @endif.
        </p>
    </div>

    <form action="{{ route('employe.depenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section : la dépense --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                <i data-lucide="file-text" class="h-4 w-4 mr-2 text-indigo-500"></i> La dépense
            </h2>

            <div class="space-y-4">
                <div>
                    <label for="objet" class="block text-sm font-medium text-gray-700 mb-1.5">Objet <span class="text-red-500">*</span></label>
                    <input type="text" id="objet" name="objet" value="{{ old('objet') }}" required maxlength="255"
                           placeholder="Ex. Achat de fournitures de bureau"
                           class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="categorie_depense_id" class="block text-sm font-medium text-gray-700 mb-1.5">Catégorie <span class="text-red-500">*</span></label>
                        <select id="categorie_depense_id" name="categorie_depense_id" required
                                class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sélectionnez…</option>
                            @foreach ($categories as $categorie)
                                <option value="{{ $categorie->id }}" @selected(old('categorie_depense_id') === $categorie->id)>{{ $categorie->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_besoin" class="block text-sm font-medium text-gray-700 mb-1.5">Date de besoin</label>
                        <input type="date" id="date_besoin" name="date_besoin" value="{{ old('date_besoin') }}"
                               class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Contexte, justification…"
                              class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section : montant et bénéficiaire --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                <i data-lucide="banknote" class="h-4 w-4 mr-2 text-indigo-500"></i> Montant et bénéficiaire
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="montant" class="block text-sm font-medium text-gray-700 mb-1.5">Montant <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" id="montant" name="montant" value="{{ old('montant') }}" required min="1" step="any"
                               class="w-full rounded-xl border-gray-300 text-sm pr-16 focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="absolute inset-y-0 right-4 flex items-center text-sm text-gray-400">{{ $parametres->devise }}</span>
                    </div>
                </div>
                <div>
                    <label for="beneficiaire" class="block text-sm font-medium text-gray-700 mb-1.5">Bénéficiaire <span class="text-red-500">*</span></label>
                    <input type="text" id="beneficiaire" name="beneficiaire" value="{{ old('beneficiaire') }}" required maxlength="255"
                           placeholder="Fournisseur ou personne à payer"
                           class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Section : pièces --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center">
                    <i data-lucide="paperclip" class="h-4 w-4 mr-2 text-indigo-500"></i> Pièces justificatives
                </h2>
                <button type="button" id="ajouter-piece"
                        class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Ajouter
                </button>
            </div>
            <p class="text-xs text-gray-400 mb-4">Devis, factures… PDF ou image, 10 Mo max par fichier.</p>

            <div id="pieces" class="space-y-3">
                <p id="pieces-vide" class="text-sm text-gray-400 text-center py-4 border border-dashed border-gray-200 rounded-xl">
                    Aucune pièce — cliquez sur « Ajouter ».
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pb-4">
            <a href="{{ route('employe.depenses.index') }}"
               class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit" class="btn-primary inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm">
                <i data-lucide="save" class="h-4 w-4 mr-2"></i> Enregistrer le brouillon
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const conteneur = document.getElementById('pieces');
        const vide = document.getElementById('pieces-vide');
        let index = 0;

        document.getElementById('ajouter-piece').addEventListener('click', function () {
            vide?.remove();

            const ligne = document.createElement('div');
            ligne.className = 'piece-ligne flex flex-col sm:flex-row sm:items-center gap-2 bg-gray-50 rounded-xl p-3';
            ligne.innerHTML = `
                <select name="justificatifs[${index}][type]"
                        class="w-full sm:w-44 shrink-0 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($typesJustificatif as $valeur => $libelle)
                        <option value="{{ $valeur }}">{{ $libelle }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <input type="file" name="justificatifs[${index}][fichier]" accept=".pdf,image/*" required
                           class="flex-1 min-w-0 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-200">
                    <button type="button" class="shrink-0 p-1.5 text-gray-400 hover:text-red-500"
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
