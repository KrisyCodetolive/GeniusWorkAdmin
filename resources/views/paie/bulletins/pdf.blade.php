@php
    /*
     * Bulletin de paie — reproduction du gabarit bulletin_genius_work_squelette.html.
     * Les libellés sont ceux du gabarit ; les valeurs viennent du bulletin, du salarié et de l'entreprise.
     * Mise en page en tableaux (DomPDF ne gère ni grid, ni flex, ni dégradés).
     */
    $bulletin->loadMissing(['employeur.entreprise', 'elements']);
    $employe = $bulletin->employeur;
    $entreprise = $employe?->entreprise ?? $bulletin->entreprise;
    $metaBulletin = $bulletin->meta_donnees ?? [];
    $metaEmploye = $employe?->meta_donnees ?? [];
    $configEntreprise = $entreprise?->configuration ?? [];

    // Formatage
    $fmt = fn ($v) => number_format(round((float) $v), 0, ',', ' ');
    $taux = fn ($t) => ($t === null || (float) $t == 0) ? '—' : rtrim(rtrim(number_format((float) $t, 4, ',', ''), '0'), ',') . ' %';
    $val = fn ($v) => filled($v) ? $v : 'À renseigner';
    $date = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : 'À renseigner';

    // Mode stagiaire : déterminé par le type de contrat
    $stage = str_contains(mb_strtolower($employe?->type_contrat ?? ''), 'stag');

    // Éléments du bulletin
    $elements = $bulletin->elements->sortBy('ordre')->values();
    $parType = fn ($type) => $elements->where('type', $type)->values();
    $retenues = $parType('retenue_salariale');
    $charges = $parType('charge_patronale');
    $estCmu = fn ($e) => stripos($e->libelle, 'CMU') !== false || stripos($e->code, 'CMU') !== false;

    // 02 Cotisations sociales : une ligne = part salarié + part patronale
    $cotisations = [
        'Retraite' => [
            $retenues->firstWhere('categorie', 'cnps_employe'),
            $charges->firstWhere('categorie', 'cnps_employeur'),
        ],
        'Prestations familiales' => [null, $charges->firstWhere('categorie', 'prestations_familiales')],
        'Assurance maternité' => [null, $charges->first(fn ($e) => $e->categorie === 'assurance_maladie' && ! $estCmu($e))],
        'Accidents du travail / maladies professionnelles' => [null, $charges->firstWhere('categorie', 'accident_travail')],
        'CMU' => [$retenues->first($estCmu), $charges->first($estCmu)],
    ];
    $idsCotisations = collect($cotisations)->flatten()->filter()->pluck('id');
    $totalCotisationsSalarie = $retenues->whereIn('id', $idsCotisations)->sum('montant');
    $totalChargesPatronales = $charges->sum('montant');

    // 03 Retenues fiscales
    $its = $retenues->firstWhere('categorie', 'igr');
    $ricf = (float) ($metaBulletin['ricf'] ?? 0);
    $totalFiscal = $its?->montant ?? 0;

    // 04 Autres retenues : avance et acompte (méta) + toute retenue qui n'est ni cotisation ni impôt
    $avance = (float) ($metaBulletin['avance'] ?? 0);
    $acompte = (float) ($metaBulletin['acompte'] ?? 0);
    $autresRetenues = (float) ($metaBulletin['autres_retenues'] ?? 0)
        + $retenues->reject(fn ($e) => $idsCotisations->contains($e->id) || $e->categorie === 'igr')->sum('montant');
    $totalAutres = $avance + $acompte + $autresRetenues;

    // 05 Paiement
    $modePaiement = mb_strtolower($metaBulletin['mode_paiement'] ?? '');
    $statutPaiement = mb_strtolower($metaBulletin['statut_paiement'] ?? '');
    $coche = fn (bool $ok) => '<span class="box">' . ($ok ? '✓' : '&nbsp;') . '</span>';

    $nomPrenoms = trim(($employe?->nom ?? '') . ' ' . ($employe?->prenom ?? ''));
    $personnesCmu = fn ($e) => max(1, (int) ($e?->base ?: 1));
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>GENIUS WORK — Bulletin de paie</title>
<style>
    @page { margin: 12mm 14mm; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color: #233241; font-size: 9.5px; }
    table { width: 100%; border-collapse: collapse; }
    td, th { vertical-align: top; }
    .topbar { height: 7px; background-color: #102b46; margin-bottom: 14px; }

    /* En-tête */
    .header { border-bottom: 1px solid #d8e1e8; padding-bottom: 12px; }
    .logo { width: 44px; height: 44px; padding-top: 9px; line-height: 1; border-radius: 12px; background-color: #102b46; color: #fff; text-align: center; font-size: 21px; font-weight: bold; }
    .brand h1 { margin: 0; color: #102b46; font-size: 17px; letter-spacing: .5px; }
    .brand p { margin: 3px 0 0; color: #71808f; }
    .doc { text-align: right; }
    .eyebrow { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: #71808f; font-weight: bold; }
    .doc h2 { margin: 3px 0; font-size: 19px; color: #102b46; letter-spacing: 1px; }
    .ref { display: inline-block; background-color: #eaf3f9; color: #1d5f91; font-weight: bold; padding: 5px 9px; border-radius: 6px; }
    .period { margin-top: 6px; color: #71808f; }

    /* Cartes */
    .card { border: 1px solid #d8e1e8; border-radius: 9px; background: #fff; }
    .card-head { padding: 7px 10px; background-color: #f2f7fa; border-bottom: 1px solid #d8e1e8; border-radius: 9px 9px 0 0; }
    .card-head strong { color: #102b46; font-size: 10px; }
    .badge { float: right; font-size: 7.5px; text-transform: uppercase; letter-spacing: .7px; background-color: #eaf3f9; color: #1d5f91; padding: 3px 6px; border-radius: 10px; font-weight: bold; }
    .card-body { padding: 8px 10px; }
    .info td { padding: 3px 0; border-bottom: 1px dashed #e7edf1; }
    .info tr:last-child td { border-bottom: 0; }
    .label { color: #71808f; width: 105px; }
    .edit { font-weight: bold; }

    /* Sections */
    .section { margin-top: 13px; page-break-inside: avoid; }
    .section-title { margin-bottom: 6px; color: #102b46; font-weight: bold; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; }
    .num { display: inline-block; width: 19px; padding: 4px 0; line-height: 1; border-radius: 6px; background-color: #102b46; color: #fff; text-align: center; font-size: 9px; margin-right: 6px; }
    .grid { border: 1px solid #d8e1e8; }
    .grid th { background-color: #102b46; color: #fff; text-align: left; padding: 6px 7px; font-size: 8px; text-transform: uppercase; letter-spacing: .3px; }
    .grid td { padding: 5px 7px; border-top: 1px solid #e8edf1; }
    .numc { text-align: right; white-space: nowrap; }
    .grid th.numc { text-align: right; }
    .group td { background-color: #f4f7f9; color: #102b46; font-weight: bold; text-transform: uppercase; font-size: 8px; }
    .total td { background-color: #eaf3f9; font-weight: bold; color: #102b46; }
    .small { font-size: 7.5px; color: #71808f; line-height: 1.45; margin: 5px 0 0; }

    /* Mode stagiaire */
    .switch { background-color: #fff7e4; border: 1px solid #f0dfb2; border-radius: 8px; padding: 7px 9px; margin-top: 12px; }
    .switch strong { color: #7b5a16; margin-right: 6px; }

    /* Synthèse */
    .summary-card { border: 1px solid #d8e1e8; border-radius: 9px; }
    .summary-head { background-color: #102b46; color: #fff; padding: 7px 10px; font-weight: bold; border-radius: 9px 9px 0 0; }
    .summary-row td { padding: 6px 10px; border-top: 1px solid #edf1f4; }
    .net td { background-color: #0b8f67; color: #fff; font-size: 13px; font-weight: bold; padding: 8px 10px; }
    .cost td { background-color: #f4f7f9; font-weight: bold; color: #102b46; }

    /* Paiement */
    .box { display: inline-block; width: 9px; height: 9px; line-height: 9px; border: 1px solid #64748b; text-align: center; font-size: 8px; margin-right: 4px; vertical-align: middle; }
    .signature { height: 62px; border-bottom: 1px solid #64748b; margin-top: 12px; }

    .footer { margin-top: 13px; padding-top: 7px; border-top: 1px solid #d8e1e8; color: #71808f; font-size: 7.5px; }
</style>
</head>
<body>
<div class="topbar"></div>

{{-- En-tête --}}
<table class="header">
    <tr>
        <td>
            <table class="brand">
                <tr>
                    <td style="width: 54px;"><div class="logo">G</div></td>
                    <td style="vertical-align: middle;">
                        <h1>GENIUS WORK</h1>
                        <p><strong>{{ $entreprise?->raison_sociale ?: $entreprise?->nom }}</strong> · {{ $val($entreprise?->adresse) }}</p>
                        <p>Tél. : {{ $val($entreprise?->telephone) }} · Email : {{ $val($entreprise?->email) }}</p>
                    </td>
                </tr>
            </table>
        </td>
        <td class="doc" style="width: 42%;">
            <div class="eyebrow">Document RH / Finance</div>
            <h2>{{ $stage ? 'JUSTIFICATIF D’INDEMNITÉ DE STAGE' : 'BULLETIN DE PAIE' }}</h2>
            <div class="ref">Référence : <span>{{ $bulletin->reference }}</span></div>
            <div class="period">Période : <strong>{{ $date($bulletin->periode_debut) }} au {{ $date($bulletin->periode_fin) }}</strong></div>
        </td>
    </tr>
</table>

{{-- Employeur / Salarié --}}
<table style="margin-top: 13px;">
    <tr>
        <td style="width: 49%;">
            <div class="card">
                <div class="card-head"><strong>Employeur</strong><span class="badge">Identification</span></div>
                <div class="card-body">
                    <table class="info">
                        <tr><td class="label">Raison sociale</td><td class="edit">{{ $val($entreprise?->raison_sociale ?: $entreprise?->nom) }}</td></tr>
                        <tr><td class="label">Adresse</td><td class="edit">{{ $val($entreprise?->adresse) }}</td></tr>
                        <tr><td class="label">RCCM</td><td class="edit">{{ $val($entreprise?->rccm) }}</td></tr>
                        <tr><td class="label">NCC</td><td class="edit">{{ $val($entreprise?->nif) }}</td></tr>
                        <tr><td class="label">N° CNPS</td><td class="edit">{{ $val($configEntreprise['numero_cnps'] ?? null) }}</td></tr>
                        <tr><td class="label">Téléphone</td><td class="edit">{{ $val($entreprise?->telephone) }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 49%;">
            <div class="card">
                <div class="card-head"><strong>{{ $stage ? 'Stagiaire' : 'Salarié' }}</strong><span class="badge">Bénéficiaire</span></div>
                <div class="card-body">
                    <table class="info">
                        <tr><td class="label">Nom &amp; prénoms</td><td class="edit">{{ $val($nomPrenoms) }}</td></tr>
                        <tr><td class="label">Matricule</td><td class="edit">{{ $val($employe?->matricule) }}</td></tr>
                        <tr><td class="label">Fonction / poste</td><td class="edit">{{ $val($employe?->poste) }}</td></tr>
                        <tr><td class="label">Catégorie</td><td class="edit">{{ $val($metaEmploye['categorie'] ?? null) }}</td></tr>
                        <tr><td class="label">Date d'embauche</td><td class="edit">{{ $date($employe?->date_embauche) }}</td></tr>
                        <tr><td class="label">N° CNPS</td><td class="edit">{{ $val($metaEmploye['numero_cnps'] ?? null) }}</td></tr>
                        <tr><td class="label">Jours / heures</td><td class="edit">{{ $val($metaBulletin['jours_heures'] ?? null) }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

@if($stage)
<div class="switch">
    <strong>MODE STAGIAIRE</strong>
    <span>Document de traçabilité du paiement d'une indemnité de stage. Le régime social et fiscal doit être déterminé selon la nature du stage et les règles applicables.</span>
</div>
@endif

{{-- 01 Rémunération --}}
<div class="section">
    <div class="section-title"><span class="num">01</span> Rémunération</div>
    <table class="grid">
        <thead><tr><th>Désignation</th><th class="numc">Base / quantité</th><th class="numc">Taux</th><th class="numc">Montant</th><th>Imposable</th></tr></thead>
        <tbody>
            @foreach([
                'Salaire / indemnité' => $parType('salaire')->concat($parType('indemnite')),
                'Primes' => $parType('prime'),
            ] as $groupe => $lignes)
                @if($lignes->isNotEmpty())
                    <tr class="group"><td colspan="5">{{ $groupe }}</td></tr>
                    @foreach($lignes as $e)
                    <tr>
                        <td>{{ $stage && $e->categorie === 'salaire_base' ? 'Indemnité de stage' : $e->libelle }}</td>
                        <td class="numc">{{ $e->base !== null ? $fmt($e->base) : '—' }}</td>
                        <td class="numc">{{ $taux($e->taux) }}</td>
                        <td class="numc">{{ $fmt($e->montant) }}</td>
                        <td>{{ $e->categorie === 'prime_autre' ? 'Selon nature' : ($e->imposable ? 'Oui' : 'Non') }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
            <tr class="total"><td colspan="3">SALAIRE BRUT / TOTAL INDEMNITÉ</td><td class="numc">{{ $fmt($bulletin->salaire_brut) }}</td><td></td></tr>
        </tbody>
    </table>
</div>

@unless($stage)
{{-- 02 Cotisations sociales --}}
<div class="section">
    <div class="section-title"><span class="num">02</span> Cotisations sociales — CNPS &amp; CMU</div>
    <table class="grid">
        <thead><tr><th>Désignation</th><th class="numc">Base</th><th class="numc">Taux salarié</th><th class="numc">Retenue salarié</th><th class="numc">Taux patronal</th><th class="numc">Charge patronale</th></tr></thead>
        <tbody>
            @foreach($cotisations as $designation => [$sal, $pat])
                @if($designation === 'CMU')
                <tr>
                    <td>CMU</td>
                    <td class="numc">{{ ($sal || $pat) ? $personnesCmu($sal ?? $pat) . ' personne' . ($personnesCmu($sal ?? $pat) > 1 ? 's' : '') : '—' }}</td>
                    <td class="numc">{{ $sal ? $fmt($sal->montant / $personnesCmu($sal)) . ' FCFA' : '—' }}</td>
                    <td class="numc">{{ $sal ? $fmt($sal->montant) : '—' }}</td>
                    <td class="numc">{{ $pat ? $fmt($pat->montant / $personnesCmu($pat)) . ' FCFA' : '—' }}</td>
                    <td class="numc">{{ $pat ? $fmt($pat->montant) : '—' }}</td>
                </tr>
                @else
                <tr>
                    <td>{{ $designation }}</td>
                    <td class="numc">{{ ($sal ?? $pat)?->base !== null ? $fmt(($sal ?? $pat)->base) : '—' }}</td>
                    <td class="numc">{{ $sal ? $taux($sal->taux) : '—' }}</td>
                    <td class="numc">{{ $sal ? $fmt($sal->montant) : '—' }}</td>
                    <td class="numc">{{ $pat ? $taux($pat->taux) : '—' }}</td>
                    <td class="numc">{{ $pat ? $fmt($pat->montant) : '—' }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="total"><td colspan="3">TOTAL</td><td class="numc">{{ $fmt($totalCotisationsSalarie) }}</td><td></td><td class="numc">{{ $fmt($totalChargesPatronales) }}</td></tr>
        </tbody>
    </table>
    <p class="small">Paramètres de référence intégrés dans ce modèle : retraite 6,3 % salarié / 7,7 % employeur ; prestations familiales 5 % ; maternité 0,75 % ; AT/MP 2 à 5 % selon le secteur ; CMU 1 000 FCFA/personne/mois, répartie à 50 % employeur / 50 % salarié. Les assiettes et plafonds doivent rester paramétrables.</p>
</div>

{{-- 03 Retenues fiscales --}}
<div class="section">
    <div class="section-title"><span class="num">03</span> Retenues fiscales</div>
    <table class="grid">
        <thead><tr><th>Désignation</th><th class="numc">Assiette</th><th class="numc">Barème</th><th class="numc">Montant</th></tr></thead>
        <tbody>
            <tr><td>ITS — impôt sur traitements et salaires</td><td class="numc">{{ $its?->base !== null ? $fmt($its->base) : $fmt($bulletin->salaire_brut) }}</td><td class="numc">Barème progressif</td><td class="numc">{{ $fmt($totalFiscal) }}</td></tr>
            <tr><td>Réduction d'impôt pour charges de famille (RICF)</td><td class="numc">Selon situation</td><td class="numc">Paramétrable</td><td class="numc">{{ $fmt($ricf) }}</td></tr>
        </tbody>
    </table>
    <p class="small">Barème intégré : 0–75 000 : 0 % ; 75 001–240 000 : 16 % ; 240 001–800 000 : 21 % ; 800 001–2 400 000 : 24 % ; 2 400 001–8 000 000 : 28 % ; au-delà de 8 000 000 : 32 %. La RICF dépend de la situation familiale.</p>
</div>
@endunless

{{-- 04 Autres retenues --}}
<div class="section">
    <div class="section-title"><span class="num">04</span> Autres retenues</div>
    <table class="grid">
        <thead><tr><th>Désignation</th><th class="numc">Montant</th></tr></thead>
        <tbody>
            <tr><td>Avance sur salaire / indemnité</td><td class="numc">{{ $fmt($avance) }}</td></tr>
            <tr><td>Acompte</td><td class="numc">{{ $fmt($acompte) }}</td></tr>
            <tr><td>Autres retenues autorisées</td><td class="numc">{{ $fmt($autresRetenues) }}</td></tr>
            <tr class="total"><td>TOTAL AUTRES RETENUES</td><td class="numc">{{ $fmt($totalAutres) }}</td></tr>
        </tbody>
    </table>
</div>

{{-- Synthèse --}}
<table style="margin-top: 12px;">
    <tr>
        <td style="width: 56%;">
            <div class="summary-card">
                <div class="summary-head">Synthèse du paiement</div>
                <table>
                    <tr class="summary-row"><td>Salaire brut / indemnité</td><td class="numc"><strong>{{ $fmt($bulletin->salaire_brut) }} FCFA</strong></td></tr>
                    <tr class="summary-row"><td>Cotisations salarié</td><td class="numc"><strong>{{ $fmt($totalCotisationsSalarie) }} FCFA</strong></td></tr>
                    <tr class="summary-row"><td>Retenues fiscales</td><td class="numc"><strong>{{ $fmt($totalFiscal) }} FCFA</strong></td></tr>
                    <tr class="summary-row"><td>Autres retenues</td><td class="numc"><strong>{{ $fmt($totalAutres) }} FCFA</strong></td></tr>
                    <tr class="net"><td>NET À PAYER</td><td class="numc">{{ $fmt($bulletin->salaire_net) }} FCFA</td></tr>
                </table>
            </div>
        </td>
        @unless($stage)
        <td style="width: 2%;"></td>
        <td style="width: 42%;">
            <div class="summary-card">
                <div class="summary-head">Charges patronales &amp; coût employeur</div>
                <table>
                    <tr class="summary-row"><td>Salaire brut</td><td class="numc"><strong>{{ $fmt($bulletin->salaire_brut) }} FCFA</strong></td></tr>
                    <tr class="summary-row"><td>Cotisations patronales</td><td class="numc"><strong>{{ $fmt($totalChargesPatronales) }} FCFA</strong></td></tr>
                    <tr class="summary-row cost"><td>COÛT TOTAL EMPLOYEUR</td><td class="numc">{{ $fmt($bulletin->salaire_brut + $totalChargesPatronales) }} FCFA</td></tr>
                </table>
            </div>
        </td>
        @endunless
    </tr>
</table>

{{-- 05 Informations de paiement --}}
<div class="section">
    <div class="section-title"><span class="num">05</span> Informations de paiement</div>
    <table>
        <tr>
            <td style="width: 49%;">
                <div class="card"><div class="card-body">
                    <table class="info">
                        <tr><td class="label">Mode</td><td>
                            <table>
                                <tr>
                                    <td>{!! $coche(str_contains($modePaiement, 'esp')) !!} Espèces</td>
                                    <td>{!! $coche(str_contains($modePaiement, 'virement')) !!} Virement bancaire</td>
                                </tr>
                                <tr><td>{!! $coche(str_contains($modePaiement, 'mobile')) !!} Mobile Money</td><td></td></tr>
                            </table>
                        </td></tr>
                        <tr><td class="label">Statut</td><td>
                            <table>
                                <tr>
                                    <td>{!! $coche(in_array($statutPaiement, ['payé', 'paye'])) !!} Payé</td>
                                    <td>{!! $coche(in_array($statutPaiement, ['impayé', 'impaye'])) !!} Impayé</td>
                                </tr>
                            </table>
                        </td></tr>
                        <tr><td class="label">Date de paiement</td><td>{{ $date($bulletin->date_paiement) }}</td></tr>
                        <tr><td class="label">Référence paiement</td><td>{{ $val($metaBulletin['reference_paiement'] ?? null) }}</td></tr>
                    </table>
                </div></div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <div class="card"><div class="card-body">
                    <strong>Signature de l'employeur</strong>
                    <div class="signature"></div>
                    <div class="small">Nom et cachet</div>
                </div></div>
            </td>
        </tr>
    </table>
</div>

@if($stage)
{{-- 06 Informations relatives au stage --}}
<div class="section">
    <div class="section-title"><span class="num">06</span> Informations relatives au stage</div>
    <div class="card"><div class="card-body">
        <table>
            <tr>
                <td style="width: 50%; padding-right: 6px;">
                    <table class="info">
                        <tr><td class="label">Type de stage</td><td>{{ $val($metaEmploye['type_stage'] ?? null) }}</td></tr>
                        <tr><td class="label">Convention / contrat</td><td>{{ $val($metaEmploye['convention_stage'] ?? null) }}</td></tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 6px;">
                    <table class="info">
                        <tr><td class="label">Établissement</td><td>{{ $val($metaEmploye['etablissement_stage'] ?? null) }}</td></tr>
                        <tr><td class="label">Période du stage</td><td>{{ $employe?->date_embauche && $employe?->date_fin_contrat ? $date($employe->date_embauche) . ' au ' . $date($employe->date_fin_contrat) : 'À renseigner' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div></div>
</div>
@endif

<table class="footer">
    <tr>
        <td>GENIUS WORK · Document RH / Finance</td>
        <td style="text-align: center;">Document généré le {{ now()->format('d/m/Y') }}</td>
        <td style="text-align: right;">Signature manuscrite requise pour validation</td>
    </tr>
</table>
</body>
</html>
