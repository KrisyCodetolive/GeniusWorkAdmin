@extends('layouts.portail-employe')

@section('title', $demande->reference)

@section('content')
@php
    use App\Models\DemandeDepense;
    use App\Models\ValidationDepense;

    $badgeClasses = [
        'gray' => 'bg-slate-100 text-slate-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'info' => 'bg-blue-100 text-blue-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'danger' => 'bg-red-100 text-red-700',
    ];
    $couleur = DemandeDepense::COULEURS_STATUT[$demande->statut] ?? 'gray';
    $payee = in_array($demande->statut, [
        DemandeDepense::STATUT_PAYEE,
        DemandeDepense::STATUT_JUSTIFICATION_SOUMISE,
        DemandeDepense::STATUT_CLOTUREE,
    ]);
    $recus = $demande->justificatifs->where('type', 'recu');
    $enAttente = in_array($demande->statut, [
        DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE,
        DemandeDepense::STATUT_EN_ATTENTE_CEO,
        DemandeDepense::STATUT_JUSTIFICATION_SOUMISE,
    ]);
    $refus = in_array($demande->statut, [DemandeDepense::STATUT_REJETEE, DemandeDepense::STATUT_BROUILLON])
        ? $demande->validations->whereIn('decision', [ValidationDepense::DECISION_REJETE, ValidationDepense::DECISION_RENVOYE])->whereNotNull('commentaire')->last()
        : null;
@endphp

<div class="max-w-xl mx-auto space-y-5">
    <a href="{{ route('employe.depenses.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-[#3a5faa]">
        <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Mes demandes
    </a>

    {{-- Où en est la demande --}}
    <div class="card p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-900 break-words">{{ $demande->objet }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ $demande->reference }} · {{ $demande->created_at->format('d/m/Y') }}</p>
                <p class="text-sm text-slate-600 mt-0.5">Demandeur : <span class="font-medium text-slate-800">{{ $demande->nomDemandeur() }}</span></p>
            </div>
            <p class="shrink-0 text-right text-2xl font-bold text-[#243b6e] leading-tight">
                {{ number_format((float) $demande->montant, 0, ',', ' ') }}
                <span class="block text-xs font-medium text-slate-500">{{ $demande->devise }}</span>
            </p>
        </div>

        <div class="mt-5">@include('employe.depenses._suivi', ['demande' => $demande])</div>

        <div class="mt-5 rounded-xl px-4 py-3 flex items-center gap-3 {{ $badgeClasses[$couleur] }}">
            <i data-lucide="{{ $enAttente ? 'hourglass' : ($demande->statut === DemandeDepense::STATUT_CLOTUREE ? 'check-circle-2' : ($couleur === 'danger' ? 'x-circle' : 'info')) }}" class="h-5 w-5 shrink-0"></i>
            <div class="min-w-0">
                <p class="font-semibold leading-tight">{{ DemandeDepense::STATUTS[$demande->statut] ?? $demande->statut }}</p>
                <p class="text-sm opacity-90">{{ $demande->messageSuivi() }}</p>
            </div>
        </div>

        @if ($refus)
            <p class="mt-3 text-sm text-slate-700 bg-slate-50 rounded-lg px-3 py-2 italic">« {{ $refus->commentaire }} » — {{ $refus->user?->name }}</p>
        @endif
    </div>

    {{-- Ce que vous devez faire --}}
    @can('soumettre', $demande)
        <form method="POST" action="{{ route('employe.depenses.soumettre', $demande) }}"
              onsubmit="return confirm('Envoyer cette demande à la comptabilité ? Elle ne sera plus modifiable.')">
            @csrf
            <button type="submit" class="btn-primary w-full inline-flex justify-center items-center gap-2 px-6 py-4 rounded-2xl text-white text-lg font-semibold shadow-sm">
                <i data-lucide="send" class="h-5 w-5"></i> Envoyer la demande
            </button>
        </form>
    @endcan

    @can('signerApprobation', $demande)
        <div class="card !border-[#b9c7e6] p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-2 flex items-center">
                <i data-lucide="pen-line" class="h-4 w-4 mr-2 text-[#3a5faa]"></i> Votre décharge
            </h2>
            <p class="text-sm text-slate-600 mb-4">Signez ci-dessous pour autoriser la comptabilité à procéder au paiement.</p>
            <form method="POST" action="{{ route('employe.depenses.signer', $demande) }}" id="form-signature">
                @csrf
                <input type="hidden" name="signature" id="signature-data">
                <div class="border-2 border-dashed border-slate-300 rounded-xl overflow-hidden bg-white touch-none">
                    <canvas id="pad-signature" class="w-full h-40 cursor-crosshair"></canvas>
                </div>
                <div class="flex gap-2 mt-3">
                    <button type="button" id="effacer-signature"
                            class="inline-flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">
                        <i data-lucide="eraser" class="h-4 w-4 mr-1.5"></i> Effacer
                    </button>
                    <button type="submit" id="btn-signer"
                            class="btn-primary flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm">
                        <i data-lucide="pen-tool" class="h-4 w-4 mr-2"></i> Signer
                    </button>
                </div>
                <p id="signature-vide" class="hidden text-xs text-red-500 mt-2 text-center">Tracez votre signature avant de valider.</p>
            </form>
        </div>
    @endcan

    @can('justifier', $demande)
        <div class="card !border-emerald-200 p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-2 flex items-center">
                <i data-lucide="receipt" class="h-4 w-4 mr-2 text-emerald-500"></i> Justifier la dépense
            </h2>
            <p class="text-sm text-slate-600 mb-4">{{ $recus->count() }} reçu(s) joint(s). Ajoutez vos reçus puis envoyez la justification.</p>

            @can('ajouterJustificatif', $demande)
                <form method="POST" action="{{ route('employe.depenses.justificatifs.store', $demande) }}" enctype="multipart/form-data" class="space-y-3 mb-4">
                    @csrf
                    <input type="hidden" name="type" value="recu">
                    <input type="file" name="fichier" accept=".pdf,image/*" required
                           class="w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef2fa] file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[#3a5faa]">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-medium text-[#3a5faa] bg-[#eef2fa] hover:bg-[#dde5f4]">
                        <i data-lucide="upload" class="h-4 w-4 mr-2"></i> Ajouter ce reçu
                    </button>
                </form>
            @endcan

            <form method="POST" action="{{ route('employe.depenses.justification', $demande) }}"
                  onsubmit="return confirm('Envoyer la justification à la comptabilité ?')">
                @csrf
                <button type="submit" @disabled($recus->isEmpty())
                        class="w-full inline-flex justify-center items-center px-4 py-3 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    <i data-lucide="send" class="h-4 w-4 mr-2"></i> Soumettre la justification
                </button>
                @if ($recus->isEmpty())
                    <p class="text-xs text-red-500 mt-2 text-center">Ajoutez au moins un reçu pour activer ce bouton.</p>
                @endif
            </form>
        </div>
    @endcan

    {{-- Le reste, replié : détails, pièces, paiement, historique --}}
    <details class="card px-5 py-4 group">
        <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-semibold text-slate-700">
            Détails de la demande
            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180"></i>
        </summary>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm mt-4 pt-4 border-t border-slate-100">
            <div><dt class="text-slate-500 text-xs font-medium">Bénéficiaire</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->beneficiaire }}</dd></div>
            <div><dt class="text-slate-500 text-xs font-medium">Catégorie</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->categorie?->nom ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 text-xs font-medium">Date de besoin</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->date_besoin?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 text-xs font-medium">Montant en lettres</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->montantEnLettres() }}</dd></div>
            @if ($demande->description)
                <div class="sm:col-span-2"><dt class="text-slate-500 text-xs font-medium">Description</dt><dd class="font-medium text-slate-800 mt-0.5 whitespace-pre-line">{{ $demande->description }}</dd></div>
            @endif
        </dl>
    </details>

    <details class="card px-5 py-4 group">
        <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-semibold text-slate-700">
            <span>Pièces jointes <span class="text-slate-500 font-normal">({{ $demande->justificatifs->count() }})</span></span>
            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180"></i>
        </summary>
        <ul class="space-y-2 mt-4 pt-4 border-t border-slate-100">
            @forelse ($demande->justificatifs as $justificatif)
                <li>
                    <a href="{{ route('depenses.justificatif', $justificatif) }}" target="_blank"
                       class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 px-3 py-2.5 hover:bg-[#eef2fa]/60">
                        <span class="inline-flex items-center min-w-0 text-sm text-slate-800">
                            <i data-lucide="file" class="h-4 w-4 mr-2 shrink-0 text-slate-500"></i>
                            <span class="truncate">{{ $justificatif->nom_original ?: basename($justificatif->fichier) }}</span>
                        </span>
                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700">{{ $typesJustificatif[$justificatif->type] ?? $justificatif->type }}</span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500 text-center py-3 border border-dashed border-slate-200 rounded-xl">Aucune pièce jointe.</li>
            @endforelse
        </ul>

        @if ($demande->statut !== DemandeDepense::STATUT_PAYEE)
            @can('ajouterJustificatif', $demande)
                <form method="POST" action="{{ route('employe.depenses.justificatifs.store', $demande) }}"
                      enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                    @csrf
                    <select name="type" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                        @foreach ($typesJustificatif as $valeur => $libelle)
                            <option value="{{ $valeur }}">{{ $libelle }}</option>
                        @endforeach
                    </select>
                    <input type="file" name="fichier" accept=".pdf,image/*" required
                           class="w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef2fa] file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[#3a5faa]">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 rounded-xl text-sm font-medium text-[#3a5faa] bg-[#eef2fa] hover:bg-[#dde5f4]">
                        <i data-lucide="upload" class="h-4 w-4 mr-2"></i> Ajouter la pièce
                    </button>
                </form>
            @endcan
        @endif
    </details>

    @if ($payee)
        <details class="card px-5 py-4 group">
            <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-semibold text-slate-700">
                Paiement
                <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180"></i>
            </summary>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm mt-4 pt-4 border-t border-slate-100">
                <div><dt class="text-slate-500 text-xs font-medium">Mode</dt><dd class="font-medium text-slate-900 mt-0.5">{{ DemandeDepense::MODES_PAIEMENT[$demande->mode_paiement] ?? $demande->mode_paiement }}</dd></div>
                <div><dt class="text-slate-500 text-xs font-medium">Date</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->date_paiement?->format('d/m/Y') }}</dd></div>
                <div class="col-span-2"><dt class="text-slate-500 text-xs font-medium">Référence</dt><dd class="font-medium text-slate-900 mt-0.5">{{ $demande->reference_paiement ?? '—' }}</dd></div>
            </dl>
            <div class="flex flex-wrap gap-3 mt-4">
                @if ($demande->preuve_paiement)
                    <a href="{{ route('depenses.preuve-paiement', $demande) }}" target="_blank"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-[#3a5faa] bg-[#eef2fa] hover:bg-[#dde5f4]">
                        <i data-lucide="paperclip" class="h-4 w-4 mr-1.5"></i> Preuve de paiement
                    </a>
                @endif
                @can('telechargerBon', $demande)
                    <a href="{{ route('depenses.bon-sortie', $demande) }}" target="_blank"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-[#3a5faa] bg-[#eef2fa] hover:bg-[#dde5f4]">
                        <i data-lucide="file-down" class="h-4 w-4 mr-1.5"></i> Bon de sortie (PDF)
                    </a>
                @endcan
            </div>
        </details>
    @endif

    <details class="card px-5 py-4 group">
        <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-semibold text-slate-700">
            Historique
            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180"></i>
        </summary>
        <ol class="relative border-l-2 border-slate-100 ml-1.5 space-y-5 mt-4">
            @forelse ($demande->validations as $validation)
                <li class="ml-5">
                    <span class="absolute -left-[7px] flex h-3.5 w-3.5 rounded-full bg-[#3a5faa] ring-4 ring-white"></span>
                    <p class="text-sm font-medium text-slate-900">
                        {{ ValidationDepense::ETAPES[$validation->etape] ?? $validation->etape }}
                        <span class="text-slate-500 font-normal">·</span>
                        {{ ValidationDepense::DECISIONS[$validation->decision] ?? $validation->decision }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $validation->user?->name ?? '—' }} · {{ $validation->signe_le->format('d/m/Y à H:i') }}</p>
                    @if ($validation->commentaire)
                        <p class="mt-1.5 text-sm text-slate-700 bg-slate-50 rounded-lg px-3 py-2 italic">« {{ $validation->commentaire }} »</p>
                    @endif
                </li>
            @empty
                <li class="ml-5 text-sm text-slate-500">Aucune étape enregistrée pour l'instant.</li>
            @endforelse
        </ol>
    </details>

    @can('annuler', $demande)
        <form method="POST" action="{{ route('employe.depenses.annuler', $demande) }}"
              onsubmit="return confirm('Annuler définitivement cette demande ?')" class="text-center pt-2">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700">
                <i data-lucide="ban" class="h-4 w-4"></i> Annuler cette demande
            </button>
        </form>
    @endcan
</div>
@endsection

@can('signerApprobation', $demande)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('pad-signature');
        const champ = document.getElementById('signature-data');
        const form = document.getElementById('form-signature');
        const erreur = document.getElementById('signature-vide');
        const ctx = canvas.getContext('2d');
        let dessine = false, aDessine = false;

        let largeur = 0;
        function dimensionner() {
            largeur = canvas.getBoundingClientRect().width;
            aDessine = false; // redimensionner efface le tracé
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#1e1b4b';
        }
        dimensionner();
        // Sur mobile, 'resize' part aussi quand la barre d'adresse se masque : on ne réagit qu'à un vrai changement de largeur.
        window.addEventListener('resize', function () {
            if (Math.abs(canvas.getBoundingClientRect().width - largeur) > 1) dimensionner();
        });

        function position(e) {
            const rect = canvas.getBoundingClientRect();
            const p = e.touches ? e.touches[0] : e;
            return { x: p.clientX - rect.left, y: p.clientY - rect.top };
        }

        function debut(e) {
            e.preventDefault();
            dessine = true;
            aDessine = true;
            erreur.classList.add('hidden');
            const { x, y } = position(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
        }
        function tracer(e) {
            if (!dessine) return;
            e.preventDefault();
            const { x, y } = position(e);
            ctx.lineTo(x, y);
            ctx.stroke();
        }
        function fin() { dessine = false; }

        canvas.addEventListener('mousedown', debut);
        canvas.addEventListener('mousemove', tracer);
        window.addEventListener('mouseup', fin);
        canvas.addEventListener('touchstart', debut, { passive: false });
        canvas.addEventListener('touchmove', tracer, { passive: false });
        canvas.addEventListener('touchend', fin);

        document.getElementById('effacer-signature').addEventListener('click', function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            aDessine = false;
        });

        form.addEventListener('submit', function (e) {
            if (!aDessine) {
                e.preventDefault();
                erreur.classList.remove('hidden');
                return;
            }
            champ.value = canvas.toDataURL('image/png');
        });
    });
</script>
@endpush
@endcan
