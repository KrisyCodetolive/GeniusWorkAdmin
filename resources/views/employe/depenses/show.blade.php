@extends('layouts.portail-employe')

@section('title', $demande->reference)

@section('content')
@php
    use App\Models\DemandeDepense;
    use App\Models\ValidationDepense;

    $badgeClasses = [
        'gray' => 'bg-gray-100 text-gray-600',
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

    $bandeaux = [
        DemandeDepense::STATUT_BROUILLON => ['bg-gray-50 border-gray-200 text-gray-700', 'pencil', 'Brouillon : ajoutez vos pièces puis soumettez la demande à la comptabilité.'],
        DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE => ['bg-amber-50 border-amber-200 text-amber-800', 'hourglass', 'En attente de validation par la comptabilité.'],
        DemandeDepense::STATUT_EN_ATTENTE_CEO => ['bg-amber-50 border-amber-200 text-amber-800', 'hourglass', 'Validée par la comptabilité, en attente du CEO.'],
        DemandeDepense::STATUT_APPROUVEE => ['bg-blue-50 border-blue-200 text-blue-800', 'check-circle-2', 'Approuvée — en attente du paiement par la comptabilité.'],
        DemandeDepense::STATUT_PAYEE => ['bg-blue-50 border-blue-200 text-blue-800', 'receipt', 'Dépense payée : joignez vos reçus puis soumettez la justification.'],
        DemandeDepense::STATUT_JUSTIFICATION_SOUMISE => ['bg-amber-50 border-amber-200 text-amber-800', 'hourglass', 'Justification envoyée — la comptabilité vérifie vos reçus.'],
        DemandeDepense::STATUT_CLOTUREE => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'check-circle-2', 'Dossier clôturé : vos justificatifs ont été validés.'],
        DemandeDepense::STATUT_REJETEE => ['bg-red-50 border-red-200 text-red-800', 'x-circle', 'Demande rejetée. Consultez le motif dans l\'historique.'],
        DemandeDepense::STATUT_ANNULEE => ['bg-gray-50 border-gray-200 text-gray-700', 'ban', 'Demande annulée.'],
    ];
    $bandeau = $bandeaux[$demande->statut] ?? null;
@endphp

<div class="max-w-4xl mx-auto">
    <a href="{{ route('employe.depenses.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 mb-4">
        <i data-lucide="arrow-left" class="h-4 w-4 mr-1"></i> Mes dépenses
    </a>

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 break-all">{{ $demande->reference }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClasses[$couleur] }}">
                    {{ DemandeDepense::STATUTS[$demande->statut] ?? $demande->statut }}
                </span>
            </div>
            <p class="text-gray-500 mt-1 break-words">{{ $demande->objet }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 shrink-0">
            @can('soumettre', $demande)
                <form method="POST" action="{{ route('employe.depenses.soumettre', $demande) }}"
                      onsubmit="return confirm('Soumettre cette demande à la comptabilité ? Elle ne sera plus modifiable.')">
                    @csrf
                    <button type="submit" class="btn-primary w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-white text-sm font-medium shadow-sm">
                        <i data-lucide="send" class="h-4 w-4 mr-2"></i> Soumettre
                    </button>
                </form>
            @endcan
            @can('annuler', $demande)
                <form method="POST" action="{{ route('employe.depenses.annuler', $demande) }}"
                      onsubmit="return confirm('Annuler définitivement cette demande ?')">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-medium text-red-600 bg-white border border-red-200 hover:bg-red-50">
                        <i data-lucide="ban" class="h-4 w-4 mr-2"></i> Annuler
                    </button>
                </form>
            @endcan
        </div>
    </div>

    {{-- Bandeau de statut --}}
    @if ($bandeau)
        <div class="mb-6 rounded-xl border px-4 py-3 text-sm flex items-center gap-2 {{ $bandeau[0] }}">
            <i data-lucide="{{ $bandeau[1] }}" class="h-5 w-5 shrink-0"></i>
            <span>{{ $bandeau[2] }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-3 space-y-5">
            {{-- Détails --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
                <div class="flex items-baseline justify-between gap-4 mb-5">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Montant</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format((float) $demande->montant, 0, ',', ' ') }}
                            <span class="text-sm font-medium text-gray-400">{{ $demande->devise }}</span>
                        </p>
                        <p class="text-xs text-gray-400 italic mt-0.5">{{ $demande->montantEnLettres() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Validation CEO</p>
                        <p class="text-sm font-medium text-gray-700 mt-1">
                            {{ $demande->necessiteValidationCeo() ? 'Requise' : 'Non requise' }}
                        </p>
                    </div>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm border-t border-gray-100 pt-5">
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide">Bénéficiaire</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->beneficiaire }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide">Catégorie</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->categorie?->nom ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide">Date de besoin</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->date_besoin?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide">Créée le</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->created_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                    @if ($demande->description)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-400 text-xs uppercase tracking-wide">Description</dt>
                            <dd class="font-medium text-gray-700 mt-0.5 whitespace-pre-line">{{ $demande->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Paiement --}}
            @if ($payee)
                <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                        <i data-lucide="banknote" class="h-4 w-4 mr-2 text-emerald-500"></i> Paiement
                    </h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-gray-400 text-xs uppercase tracking-wide">Mode</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ DemandeDepense::MODES_PAIEMENT[$demande->mode_paiement] ?? $demande->mode_paiement }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-400 text-xs uppercase tracking-wide">Date</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->date_paiement?->format('d/m/Y') }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-400 text-xs uppercase tracking-wide">Référence</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $demande->reference_paiement ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-3 mt-5 pt-4 border-t border-gray-100">
                        @if ($demande->preuve_paiement)
                            <a href="{{ route('depenses.preuve-paiement', $demande) }}" target="_blank"
                               class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                <i data-lucide="paperclip" class="h-4 w-4 mr-1.5"></i> Preuve de paiement
                            </a>
                        @endif
                        @can('telechargerBon', $demande)
                            <a href="{{ route('depenses.bon-sortie', $demande) }}" target="_blank"
                               class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                <i data-lucide="file-down" class="h-4 w-4 mr-1.5"></i> Bon de sortie (PDF)
                            </a>
                        @endcan
                    </div>
                </div>
            @endif

            {{-- Historique --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                    <i data-lucide="history" class="h-4 w-4 mr-2 text-indigo-500"></i> Historique
                </h2>
                <ol class="relative border-l-2 border-gray-100 ml-1.5 space-y-5">
                    @forelse ($demande->validations as $validation)
                        <li class="ml-5">
                            <span class="absolute -left-[7px] flex h-3.5 w-3.5 rounded-full bg-indigo-500 ring-4 ring-white"></span>
                            <p class="text-sm font-medium text-gray-900">
                                {{ ValidationDepense::ETAPES[$validation->etape] ?? $validation->etape }}
                                <span class="text-gray-400 font-normal">·</span>
                                {{ ValidationDepense::DECISIONS[$validation->decision] ?? $validation->decision }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $validation->user?->name ?? '—' }} · {{ $validation->signe_le->format('d/m/Y à H:i') }}
                            </p>
                            @if ($validation->commentaire)
                                <p class="mt-1.5 text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 italic">« {{ $validation->commentaire }} »</p>
                            @endif
                        </li>
                    @empty
                        <li class="ml-5 text-sm text-gray-400">Aucune étape enregistrée pour l'instant.</li>
                    @endforelse
                </ol>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center">
                    <i data-lucide="paperclip" class="h-4 w-4 mr-2 text-indigo-500"></i>
                    Justificatifs <span class="ml-1 text-gray-400 font-normal">({{ $demande->justificatifs->count() }})</span>
                </h2>
                <ul class="space-y-2">
                    @forelse ($demande->justificatifs as $justificatif)
                        <li>
                            <a href="{{ route('depenses.justificatif', $justificatif) }}" target="_blank"
                               class="flex items-center justify-between gap-2 rounded-xl border border-gray-100 px-3 py-2.5 hover:bg-indigo-50/50 hover:border-indigo-100 transition-colors">
                                <span class="inline-flex items-center min-w-0 text-sm text-gray-700">
                                    <i data-lucide="file" class="h-4 w-4 mr-2 shrink-0 text-gray-400"></i>
                                    <span class="truncate">{{ $justificatif->nom_original ?: basename($justificatif->fichier) }}</span>
                                </span>
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">
                                    {{ $typesJustificatif[$justificatif->type] ?? $justificatif->type }}
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400 text-center py-3 border border-dashed border-gray-200 rounded-xl">Aucune pièce jointe.</li>
                    @endforelse
                </ul>

                @can('ajouterJustificatif', $demande)
                    <form method="POST" action="{{ route('employe.depenses.justificatifs.store', $demande) }}"
                          enctype="multipart/form-data" class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                        @csrf
                        <select name="type" class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($typesJustificatif as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected($demande->statut === DemandeDepense::STATUT_PAYEE && $valeur === 'recu')>
                                    {{ $libelle }}
                                </option>
                            @endforeach
                        </select>
                        <input type="file" name="fichier" accept=".pdf,image/*" required
                               class="w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-200">
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 rounded-xl text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                            <i data-lucide="upload" class="h-4 w-4 mr-2"></i> Ajouter la pièce
                        </button>
                    </form>
                @endcan
            </div>

            @can('signerApprobation', $demande)
                <div class="bg-white rounded-2xl border border-indigo-200 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2 flex items-center">
                        <i data-lucide="pen-line" class="h-4 w-4 mr-2 text-indigo-500"></i> Votre décharge
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        Demande approuvée : signez ci-dessous pour autoriser la comptabilité à procéder au paiement.
                    </p>
                    <form method="POST" action="{{ route('employe.depenses.signer', $demande) }}" id="form-signature">
                        @csrf
                        <input type="hidden" name="signature" id="signature-data">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl overflow-hidden bg-white touch-none">
                            <canvas id="pad-signature" class="w-full h-40 cursor-crosshair"></canvas>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" id="effacer-signature"
                                    class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                                <i data-lucide="eraser" class="h-4 w-4 mr-1.5"></i> Effacer
                            </button>
                            <button type="submit" id="btn-signer"
                                    class="btn-primary flex-1 inline-flex justify-center items-center px-4 py-2 rounded-xl text-white text-sm font-medium shadow-sm">
                                <i data-lucide="pen-tool" class="h-4 w-4 mr-2"></i> Signer
                            </button>
                        </div>
                        <p id="signature-vide" class="hidden text-xs text-red-500 mt-2 text-center">Tracez votre signature avant de valider.</p>
                    </form>
                </div>
            @endcan

            @can('justifier', $demande)
                <div class="bg-white rounded-2xl border border-emerald-200 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2 flex items-center">
                        <i data-lucide="receipt" class="h-4 w-4 mr-2 text-emerald-500"></i> Justifier la dépense
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ $recus->count() }} reçu(s) joint(s). La comptabilité vérifie les pièces avant clôture.
                    </p>
                    <form method="POST" action="{{ route('employe.depenses.justification', $demande) }}"
                          onsubmit="return confirm('Envoyer la justification à la comptabilité ?')">
                        @csrf
                        <button type="submit" @disabled($recus->isEmpty())
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                            <i data-lucide="send" class="h-4 w-4 mr-2"></i> Soumettre la justification
                        </button>
                        @if ($recus->isEmpty())
                            <p class="text-xs text-red-500 mt-2 text-center">Ajoutez au moins un reçu pour activer ce bouton.</p>
                        @endif
                    </form>
                </div>
            @endcan
        </div>
    </div>
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

        function dimensionner() {
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
        window.addEventListener('resize', dimensionner);

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
