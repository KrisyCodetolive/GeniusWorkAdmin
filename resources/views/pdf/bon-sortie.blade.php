<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de sortie {{ $demande->reference }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            color: #333;
            font-size: 10.5pt;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #3498db;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header table {
            width: 100%;
        }
        .entreprise {
            font-size: 15pt;
            font-weight: bold;
            color: #3498db;
        }
        .entreprise-infos {
            font-size: 9pt;
            color: #7f8c8d;
        }
        h1 {
            color: #2c3e50;
            font-size: 18pt;
            margin: 0;
            text-align: right;
        }
        .reference {
            text-align: right;
            color: #7f8c8d;
            font-size: 10pt;
        }
        .statut {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9pt;
            background-color: #d4edda;
            color: #155724;
        }
        .statut.approuvee {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        table.details th,
        table.details td {
            border: 1px solid #dee2e6;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }
        table.details th {
            background-color: #f5f7fa;
            width: 32%;
            font-weight: bold;
            color: #2c3e50;
        }
        .montant {
            border: 2px solid #2c3e50;
            padding: 10px 12px;
            margin-bottom: 18px;
        }
        .montant .chiffres {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
        }
        .montant .lettres {
            font-style: italic;
            color: #555;
        }
        h2 {
            font-size: 12pt;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
            margin: 18px 0 10px 0;
        }
        table.signatures {
            width: 100%;
            border-collapse: collapse;
        }
        table.signatures td {
            border: 1px solid #dee2e6;
            width: 50%;
            padding: 8px;
            vertical-align: top;
            height: 150px;
        }
        .signature-titre {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .signature-meta {
            font-size: 8.5pt;
            color: #7f8c8d;
        }
        .signature-image {
            max-height: 80px;
            max-width: 220px;
            margin-top: 6px;
        }
        .non-requis {
            color: #7f8c8d;
            font-style: italic;
            margin-top: 20px;
        }
        .footer {
            margin-top: 22px;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            font-size: 8pt;
            color: #7f8c8d;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="entreprise">{{ $demande->entreprise?->raison_sociale ?: $demande->entreprise?->nom }}</div>
                    <div class="entreprise-infos">
                        {{ $demande->entreprise?->adresse }}
                        @if($demande->entreprise?->telephone) — {{ $demande->entreprise->telephone }} @endif
                        @if($demande->entreprise?->rccm)<br>RCCM : {{ $demande->entreprise->rccm }} @endif
                        @if($demande->entreprise?->nif) — NIF : {{ $demande->entreprise->nif }} @endif
                    </div>
                </td>
                <td>
                    <h1>BON DE SORTIE DE CAISSE</h1>
                    <div class="reference">N° {{ $demande->reference }}</div>
                    <div class="reference">
                        <span class="statut {{ $demande->statut }}">{{ \App\Models\DemandeDepense::STATUTS[$demande->statut] ?? $demande->statut }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="montant">
        <div class="chiffres">{{ number_format((float) $demande->montant, 0, ',', ' ') }} {{ $demande->devise }}</div>
        <div class="lettres">Arrêté à la somme de : {{ $demande->montantEnLettres() }}</div>
    </div>

    <table class="details">
        <tr>
            <th>Objet</th>
            <td>{{ $demande->objet }}</td>
        </tr>
        @if($demande->description)
            <tr>
                <th>Description</th>
                <td>{{ $demande->description }}</td>
            </tr>
        @endif
        <tr>
            <th>Bénéficiaire</th>
            <td>{{ $demande->beneficiaire }}</td>
        </tr>
        <tr>
            <th>Demandeur</th>
            <td>{{ $demande->nomDemandeur() }}</td>
        </tr>
        <tr>
            <th>Imputation</th>
            <td>
                {{ $demande->categorie?->nom ?? '—' }}
                @if($demande->categorie?->code_comptable) (compte {{ $demande->categorie->code_comptable }}) @endif
                @if($demande->departement) — {{ $demande->departement->nom }} @endif
            </td>
        </tr>
        <tr>
            <th>Date de la demande</th>
            <td>{{ $demande->created_at?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <h2>Validations</h2>
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-titre">Comptabilité</div>
                @if($comptable)
                    <div class="signature-meta">
                        {{ $comptable->user?->name }} — le {{ $comptable->signe_le->format('d/m/Y à H:i') }}
                    </div>
                    @if($signatureComptable)
                        <img class="signature-image" src="{{ $signatureComptable }}" alt="Signature comptabilité">
                    @endif
                @else
                    <div class="non-requis">En attente</div>
                @endif
            </td>
            <td>
                <div class="signature-titre">Direction générale (CEO)</div>
                @if($ceo)
                    <div class="signature-meta">
                        {{ $ceo->user?->name }} — le {{ $ceo->signe_le->format('d/m/Y à H:i') }}
                    </div>
                    @if($signatureCeo)
                        <img class="signature-image" src="{{ $signatureCeo }}" alt="Signature CEO">
                    @endif
                @else
                    <div class="non-requis">Non requise (montant sous le seuil de validation)</div>
                @endif
            </td>
            <td>
                <div class="signature-titre">Demandeur (décharge)</div>
                @if($validationDemandeur)
                    <div class="signature-meta">
                        {{ $validationDemandeur->user?->name }} — le {{ $validationDemandeur->signe_le->format('d/m/Y à H:i') }}
                    </div>
                    @if($signatureDemandeur)
                        <img class="signature-image" src="{{ $signatureDemandeur }}" alt="Signature demandeur">
                    @endif
                @else
                    <div class="non-requis">En attente</div>
                @endif
            </td>
        </tr>
    </table>

    @if($demande->statut === \App\Models\DemandeDepense::STATUT_PAYEE)
        <h2>Paiement</h2>
        <table class="details">
            <tr>
                <th>Mode de paiement</th>
                <td>{{ \App\Models\DemandeDepense::MODES_PAIEMENT[$demande->mode_paiement] ?? $demande->mode_paiement }}</td>
            </tr>
            @if($demande->reference_paiement)
                <tr>
                    <th>Référence du paiement</th>
                    <td>{{ $demande->reference_paiement }}</td>
                </tr>
            @endif
            <tr>
                <th>Date du paiement</th>
                <td>{{ $demande->date_paiement?->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Payé par</th>
                <td>{{ $demande->payeePar?->name }}</td>
            </tr>
        </table>
    @endif

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} par GeniusWork.<br>
        Empreinte du document (SHA-256) : {{ $hash }}
    </div>
</body>
</html>
