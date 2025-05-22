<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }} - GENIUS WORK</title>
    <style>
        :root {
            --primary-color: #5046e5; /* Indigo 600 - Couleur principale GENIUS WORK */
            --secondary-color: #64748b; /* Slate 500 */
            --success-color: #10b981; /* Emerald 500 */
            --border-color: #e2e8f0; /* Slate 200 */
            --background-color: #f8fafc; /* Slate 50 */
            --light-bg: #f1f5f9; /* Slate 100 */
            --card-bg: #ffffff; /* White */
            --text-color: #1e293b; /* Slate 800 */
            --text-muted: #94a3b8; /* Slate 400 */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: var(--text-color);
            line-height: 1.4;
            font-size: 9pt;
            background-color: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .status-paid {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: var(--success-color);
            opacity: 0.1;
            text-transform: uppercase;
            border: 10px solid var(--success-color);
            padding: 15px;
            font-weight: bold;
            letter-spacing: 8px;
            z-index: 0;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .header-left {
            max-width: 60%;
            display: flex;
            flex-direction: column;
        }
        
        .header-right {
            max-width: 40%;
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 12px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .logo-text {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 18pt;
            margin-right: 5px;
        }
        
        .company-tagline {
            color: var(--secondary-color);
            font-size: 8pt;
            margin-top: 2px;
        }
        
        .invoice-number {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 5px;
            align-items: center;
        }
        
        .detail-icon {
            color: var(--primary-color);
            margin-right: 5px;
            font-size: 10pt;
            width: 15px;
            text-align: center;
        }
        
        .detail-label {
            color: var(--secondary-color);
            font-weight: 500;
            width: 100px;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            background-color: var(--success-color);
            color: white;
            float: right;
        }
        
        .billing-section {
            margin: 15px 0;
        }
        
        .billing-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        
        .billing-column {
            flex: 1;
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 12px;
            position: relative;
        }
        
        .section-icon {
            position: absolute;
            left: 12px;
            top: 12px;
            color: var(--primary-color);
            font-size: 12pt;
        }
        
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 9pt;
            padding-left: 20px;
        }
        
        .client-info, .company-info {
            line-height: 1.5;
        }
        
        .entity-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background-color: var(--light-bg);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .items-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 8px;
            font-weight: 600;
            font-size: 8pt;
        }
        
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid var(--border-color);
            font-size: 8pt;
            vertical-align: top;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .item-title {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .item-description {
            font-size: 7pt;
            color: var(--secondary-color);
            margin-top: 2px;
        }
        
        .date-range {
            font-size: 7pt;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
        }
        
        .date-range-icon {
            margin-right: 3px;
            color: var(--primary-color);
        }
        
        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin: 15px 0;
        }
        
        .summary-table {
            width: 250px;
            border-collapse: collapse;
        }
        
        .summary-table tr {
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-table tr:last-child {
            border-bottom: none;
        }
        
        .summary-table th {
            text-align: left;
            padding: 5px 8px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 8pt;
        }
        
        .summary-table td {
            text-align: right;
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: 600;
        }
        
        .summary-table .total-row {
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
        }
        
        .summary-table .total-row th,
        .summary-table .total-row td {
            padding: 8px;
            color: white;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 15px 0;
        }
        
        .payment-column {
            flex: 1;
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 12px;
            position: relative;
        }
        
        .payment-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 9pt;
            color: var(--primary-color);
            padding-left: 20px;
        }
        
        .payment-info {
            font-size: 8pt;
            color: var(--text-color);
            line-height: 1.5;
        }
        
        .info-section {
            margin: 15px 0;
            font-size: 8pt;
            color: var(--secondary-color);
            line-height: 1.5;
            padding: 12px;
            background-color: var(--light-bg);
            border-radius: 8px;
        }
        
        .notes {
            margin: 15px 0;
            font-size: 8pt;
            line-height: 1.5;
            padding: 12px;
            background-color: var(--light-bg);
            border-radius: 8px;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .footer {
            margin-top: 20px;
            font-size: 7pt;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
            padding-top: 10px;
        }
        
        .GENIUS WORK-branding {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0.8cm;
            }
            
            body {
                background-color: white;
            }
            
            .container {
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($facture->statut === 'payee')
        <div class="status-paid">PAYÉE</div>
        @endif

        <div class="header">
            <div class="header-left">
                <div class="logo-container">
                    <span class="logo-text">GENIUS WORK</span>
                </div>
                <p class="company-tagline">Votre solution complète de gestion d'entreprise</p>
            </div>
            
            <div class="header-right">
                <div class="invoice-number">Facture #{{ $facture->numero_facture }}</div>
                <div class="detail-row">
                    <span class="detail-icon">📅</span>
                    <span class="detail-label">Date d'émission:</span>
                    <span class="detail-value">{{ $facture->date_facturation->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-icon">📅</span>
                    <span class="detail-label">Date d'échéance:</span>
                    <span class="detail-value">{{ $facture->date_echeance->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut:</span>
                    <span class="status-badge" style="background-color: {{ $facture->statut === 'payee' ? 'var(--success-color)' : 'var(--primary-color)' }}">
                        {{ ucfirst($facture->statut) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="billing-section">
            <div class="billing-row">
                <div class="billing-column">
                    <span class="section-icon">👤</span>
                    <div class="section-title">FACTURÉ À</div>
                    <div class="client-info">
                        <div class="entity-name">{{ $facture->entreprise->nom }}</div>
                        <div>{{ $facture->entreprise->adresse }}</div>
                        <div>{{ $facture->entreprise->code_postal }} {{ $facture->entreprise->ville }}</div>
                        <div>{{ $facture->entreprise->pays }}</div>
                        @if($facture->entreprise->telephone)
                        <div>📞 {{ $facture->entreprise->telephone }}</div>
                        @endif
                        @if($facture->entreprise->email)
                        <div>✉️ {{ $facture->entreprise->email }}</div>
                        @endif
                    </div>
                </div>
                
                <div class="billing-column">
                    <span class="section-icon">🏢</span>
                    <div class="section-title">ÉMIS PAR</div>
                    <div class="company-info">
                        <div class="entity-name">GENIUS WORK</div>
                        <div>123 Rue de l'Innovation</div>
                        <div>75000 Paris</div>
                        <div>France</div>
                        <div>📞 +33 1 23 45 67 89</div>
                        <div>✉️ contact@GENIUS WORK.com</div>
                    </div>
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="40%">Description</th>
                    <th width="25%">Période</th>
                    <th width="10%" class="text-right">Prix HT</th>
                    <th width="10%" class="text-right">TVA</th>
                    <th width="15%" class="text-right">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-title">{{ $facture->abonnement->planAbonnement->nom }}</div>
                        <div class="item-description">{{ $facture->abonnement->planAbonnement->description }}</div>
                    </td>
                    <td>
                        <div class="date-range">
                            <span class="date-range-icon">📅</span> Du {{ $facture->date_facturation->format('d/m/Y') }} au {{ $facture->date_echeance->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="text-right">{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ $facture->taux_tva }}%</td>
                    <td class="text-right">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</td>
                </tr>
                
                @if(isset($facture->fraisUsages) && $facture->fraisUsages->count() > 0)
                    @foreach($facture->fraisUsages as $frais)
                    <tr>
                        <td>
                            <div class="item-title">{{ $frais->type_frais }}</div>
                            <div class="item-description">{{ $frais->description }}</div>
                        </td>
                        <td>
                            <div class="date-range">
                                <span class="date-range-icon">📅</span> Du {{ $frais->periode_debut->format('d/m/Y') }} au {{ $frais->periode_fin->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="text-right">{{ number_format($frais->montant_ht, 2, ',', ' ') }} €</td>
                        <td class="text-right">{{ $frais->taux_tva }}%</td>
                        <td class="text-right">{{ number_format($frais->montant_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <th>Total HT</th>
                    <td>{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <th>TVA ({{ $facture->taux_tva }}%)</th>
                    <td>{{ number_format($facture->montant_ttc - $facture->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-row">
                    <th>Total TTC</th>
                    <td>{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>

        <div class="payment-row">
            <div class="payment-column">
                <span class="section-icon">💳</span>
                <div class="payment-title">Mode de paiement</div>
                <div class="payment-info">
                    {{ ucfirst($facture->mode_paiement) }}
                    @if($facture->reference_paiement)
                    <br>Référence: {{ $facture->reference_paiement }}
                    @endif
                </div>
            </div>

            <div class="payment-column">
                <span class="section-icon">🏦</span>
                <div class="payment-title">Coordonnées bancaires</div>
                <div class="payment-info">
                    Banque: GENIUS WORK Finance<br>
                    IBAN: FR76 1234 5678 9012 3456 7890 123<br>
                    BIC: GWORKFRPP
                </div>
            </div>
        </div>

        <div class="info-section">
            Merci pour votre confiance. Pour toute question concernant cette facture, 
            n'hésitez pas à contacter notre service client à l'adresse 
            <span style="color: var(--primary-color); font-weight: bold;">support@GENIUS WORK.com</span>
            ou par téléphone au <span style="color: var(--primary-color); font-weight: bold;">+33 1 23 45 67 89</span>.
        </div>

        @if($facture->notes)
        <div class="notes">
            <div class="notes-title">Notes</div>
            <div>{{ $facture->notes }}</div>
        </div>
        @endif

        <div class="footer">
            <span class="GENIUS WORK-branding">GENIUS WORK</span> - SIRET: 123 456 789 00012 - TVA: FR12345678900<br>
            123 Rue de l'Innovation, 75000 Paris, France | www.GENIUS WORK.com | contact@GENIUS WORK.com | +33 1 23 45 67 89
        </div>
    </div>
</body>
</html>