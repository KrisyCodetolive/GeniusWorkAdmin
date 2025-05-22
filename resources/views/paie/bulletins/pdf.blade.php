<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie - {{ $bulletin->reference }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            position: relative;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
        }
        .logo-area {
            position: absolute;
            top: 0;
            left: 0;
            width: 150px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #2c3e50;
            font-size: 16px;
        }
        .title-area {
            text-align: center;
            padding-top: 10px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
            padding: 0;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0;
            color: #555;
        }
        .reference {
            position: absolute;
            top: 10px;
            right: 0;
            font-size: 12px;
            color: #555;
            text-align: right;
        }
        .reference .ref-number {
            font-weight: bold;
            color: #2c3e50;
            font-size: 14px;
            margin-top: 3px;
        }
        .info-section {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: nowrap;
            gap: 20px;
        }
        .info-box {
            flex: 1;
            min-width: 45%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background-color: #f9f9f9;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .info-box h3 {
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
            color: #2c3e50;
        }
        .info-item {
            margin-bottom: 8px;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .info-value {
            width: 60%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .section-header {
            background-color: #edf2f7;
            font-weight: bold;
            color: #2c3e50;
        }
        .total-row {
            font-weight: bold;
            background-color: #edf2f7;
            color: #2c3e50;
            border-top: 2px solid #e0e0e0;
        }
        .grand-total {
            font-weight: bold;
            background-color: #2c3e50;
            color: white;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #2c3e50;
            font-size: 11px;
            color: #555;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            text-align: center;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 70px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .text-primary {
            color: #2c3e50;
        }
        .currency {
            font-family: 'Courier New', monospace;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .container {
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-area">
                GENIUS WORK
            </div>
            <div class="title-area">
                <h1>BULLETIN DE PAIE</h1>
                <p>Période du {{ \Carbon\Carbon::parse($bulletin->periode_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($bulletin->periode_fin)->format('d/m/Y') }}</p>
            </div>
            <div class="reference">
                <span>Référence</span>
                <div class="ref-number">{{ $bulletin->reference }}</div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Employeur</h3>
                <div class="info-item">
                    <span class="info-label">Nom:</span>
                    <span class="info-value text-bold">{{ $bulletin->employeur->entreprise->nom ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $bulletin->employeur->entreprise->adresse ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">RCCM:</span>
                    <span class="info-value">{{ $bulletin->employeur->entreprise->rccm ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Compte Contribuable:</span>
                    <span class="info-value">{{ $bulletin->employeur->entreprise->compte_contribuable ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="info-box">
                <h3>Employé</h3>
                <div class="info-item">
                    <span class="info-label">Nom:</span>
                    <span class="info-value text-bold">{{ $bulletin->employeur->nom_complet ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Matricule:</span>
                    <span class="info-value">{{ $bulletin->employeur->matricule ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fonction:</span>
                    <span class="info-value">{{ $bulletin->employeur->fonction ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date d'embauche:</span>
                    <span class="info-value">{{ $bulletin->employeur->date_embauche ? \Carbon\Carbon::parse($bulletin->employeur->date_embauche)->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Éléments de salaire -->
        <table>
            <thead>
                <tr>
                    <th width="40%">Désignation</th>
                    <th width="15%">Base</th>
                    <th width="15%">Taux</th>
                    <th width="15%" class="text-right">Montant</th>
                    <th width="15%">Imposable</th>
                </tr>
            </thead>
            <tbody>
                <!-- Salaire de base -->
                @foreach($elementsSalaire as $element)
                <tr>
                    <td>{{ $element->libelle }}</td>
                    <td>{{ number_format($element->base, 0, ',', ' ') }}</td>
                    <td>{{ $element->taux ? $element->taux . '%' : '-' }}</td>
                    <td class="text-right">{{ number_format($element->montant, 0, ',', ' ') }}</td>
                    <td>{{ $element->imposable ? 'Oui' : 'Non' }}</td>
                </tr>
                @endforeach

                <!-- Indemnités -->
                @if(count($elementsIndemnites) > 0)
                    <tr class="section-header">
                        <td colspan="5"><strong>Indemnités</strong></td>
                    </tr>
                    @foreach($elementsIndemnites as $element)
                    <tr>
                        <td>{{ $element->libelle }}</td>
                        <td>{{ $element->base ? number_format($element->base, 0, ',', ' ') : '-' }}</td>
                        <td>{{ $element->taux ? $element->taux . '%' : '-' }}</td>
                        <td class="text-right">{{ number_format($element->montant, 0, ',', ' ') }}</td>
                        <td>{{ $element->imposable ? 'Oui' : 'Non' }}</td>
                    </tr>
                    @endforeach
                @endif

                <!-- Primes -->
                @if(count($elementsPrimes) > 0)
                    <tr class="section-header">
                        <td colspan="5"><strong>Primes</strong></td>
                    </tr>
                    @foreach($elementsPrimes as $element)
                    <tr>
                        <td>{{ $element->libelle }}</td>
                        <td>{{ $element->base ? number_format($element->base, 0, ',', ' ') : '-' }}</td>
                        <td>{{ $element->taux ? $element->taux . '%' : '-' }}</td>
                        <td class="text-right">{{ number_format($element->montant, 0, ',', ' ') }}</td>
                        <td>{{ $element->imposable ? 'Oui' : 'Non' }}</td>
                    </tr>
                    @endforeach
                @endif

                <!-- Salaire brut -->
                <tr class="total-row">
                    <td colspan="3">Salaire Brut</td>
                    <td class="text-right">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>

                <!-- Retenues -->
                @if(count($elementsRetenues) > 0)
                    <tr class="section-header">
                        <td colspan="5"><strong>Retenues</strong></td>
                    </tr>
                    @foreach($elementsRetenues as $element)
                    <tr>
                        <td>{{ $element->libelle }}</td>
                        <td>{{ $element->base ? number_format($element->base, 0, ',', ' ') : '-' }}</td>
                        <td>{{ $element->taux ? $element->taux . '%' : '-' }}</td>
                        <td class="text-right">{{ number_format($element->montant, 0, ',', ' ') }}</td>
                        <td>{{ $element->imposable ? 'Oui' : 'Non' }}</td>
                    </tr>
                    @endforeach
                @endif

                <!-- Total des retenues -->
                <tr class="total-row">
                    <td colspan="3">Total des Retenues</td>
                    <td class="text-right">{{ number_format($bulletin->total_retenues, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>

                <!-- Salaire net -->
                <tr class="grand-total">
                    <td colspan="3">Salaire Net à Payer</td>
                    <td class="text-right currency">{{ number_format($bulletin->salaire_net, 0, ',', ' ') }} FCFA</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Charges patronales -->
        @if(count($elementsCharges) > 0)
        <h3>Charges Patronales</h3>
        <table>
            <thead>
                <tr>
                    <th width="40%">Désignation</th>
                    <th width="15%">Base</th>
                    <th width="15%">Taux</th>
                    <th width="15%" class="text-right">Montant</th>
                    <th width="15%"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($elementsCharges as $element)
                <tr>
                    <td>{{ $element->libelle }}</td>
                    <td>{{ $element->base ? number_format($element->base, 0, ',', ' ') : '-' }}</td>
                    <td>{{ $element->taux ? $element->taux . '%' : '-' }}</td>
                    <td class="text-right">{{ number_format($element->montant, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">Total des Charges Patronales</td>
                    <td class="text-right">{{ number_format($bulletin->charges_patronales, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Signature de l'employeur</div>
                <div>Nom et cachet</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Signature de l'employé</div>
                <div>Lu et approuvé</div>
            </div>
        </div>

        <div class="footer">
            <p class="text-center">Date de paiement: <strong>{{ \Carbon\Carbon::parse($bulletin->date_paiement)->format('d/m/Y') }}</strong></p>
            <p class="text-center">Ce document est généré automatiquement par l'application <strong>Genius Work</strong> et nécessite une signature manuscrite pour être valide.</p>
            <p class="text-center">Document généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
