<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $facture->numero_facture }} - Genius Work</title>
    <style>
        :root {
            --primary-color: #5046e5; /* Indigo 600 - Couleur principale GENIUS WORK */
            --primary-light: #6366f1; /* Indigo 500 */
            --primary-dark: #4338ca; /* Indigo 700 */
            --secondary-color: #64748b; /* Slate 500 */
            --success-color: #10b981; /* Emerald 500 */
            --border-color: #e2e8f0; /* Slate 200 */
            --background-color: #f8fafc; /* Slate 50 */
            --light-bg: #f1f5f9; /* Slate 100 */
            --card-bg: #ffffff; /* White */
            --text-color: #1e293b; /* Slate 800 */
            --text-muted: #94a3b8; /* Slate 400 */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
        }
        
        .status-paid {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: var(--success-color);
            opacity: 0.15;
            text-transform: uppercase;
            border: 10px solid var(--success-color);
            padding: 15px;
            font-weight: bold;
            letter-spacing: 8px;
            z-index: 0;
            pointer-events: none;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
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
            background: linear-gradient(145deg, var(--light-bg), #ffffff);
            border-radius: 12px;
            padding: 15px;
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--primary-color);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .logo-text {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 28pt;
            margin-right: 5px;
            text-shadow: 1px 1px 0 rgba(0,0,0,0.1);
            letter-spacing: -0.5px;
        }
        
        .company-tagline {
            color: var(--secondary-color);
            font-size: 9pt;
            margin-top: 5px;
            font-style: italic;
        }
        
        .invoice-number {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 12px;
            text-align: center;
            text-shadow: 0.5px 0.5px 0 rgba(0,0,0,0.05);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        

        
        .detail-label {
            color: var(--secondary-color);
            font-weight: 500;
            width: 110px;
        }
        
        .detail-value {
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            background-color: var(--success-color);
            color: white;
            float: right;
            box-shadow: var(--shadow-sm);
            letter-spacing: 0.5px;
        }
        
        .billing-section {
            margin: 20px 0;
        }
        
        .billing-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        
        .billing-column {
            flex: 1;
            background: linear-gradient(145deg, var(--light-bg), #ffffff);
            border-radius: 12px;
            padding: 15px;
            position: relative;
            box-shadow: var(--shadow-md);
            border-top: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
        }
        
        .section-icon {
            position: absolute;
            left: 15px;
            top: 15px;
            color: var(--primary-color);
            font-size: 12pt;
        }
        
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 9pt;
            padding-left: 25px;
            letter-spacing: 1px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 5px;
        }
        
        .client-info, .company-info {
            line-height: 1.6;
        }
        
        .entity-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 25px 0;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            page-break-inside: auto;
        }
        
        .items-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 12px 10px;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table th:first-child {
            border-top-left-radius: 12px;
        }
        
        .items-table th:last-child {
            border-top-right-radius: 12px;
        }
        
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 9pt;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .items-table tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }
        
        .items-table tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .item-title {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 10pt;
        }
        
        .item-description {
            font-size: 8pt;
            color: var(--secondary-color);
            margin-top: 5px;
            line-height: 1.4;
        }
        
        .date-range {
            font-size: 8pt;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            background-color: rgba(80, 70, 229, 0.05);
            padding: 6px 8px;
            border-radius: 4px;
            margin-top: 2px;
        }
        

        
        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
        }
        
        .summary-table {
            width: 300px;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: var(--shadow-md);
            border-radius: 12px;
            overflow: hidden;
            margin-left: auto;
        }
        
        .summary-table tr {
            background-color: white;
        }
        
        .summary-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        
        .summary-table th {
            text-align: left;
            padding: 10px 15px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 9pt;
        }
        
        .summary-table td {
            text-align: right;
            padding: 10px 15px;
            font-size: 10pt;
            font-weight: 600;
        }
        
        .summary-table .total-row {
            background-color: var(--primary-color);
        }
        
        .summary-table .total-row th,
        .summary-table .total-row td {
            padding: 12px 15px;
            color: white;
            font-weight: bold;
            font-size: 11pt;
        }
     
        
      
        
        .footer {
            margin-top: 40px;
            font-size: 8pt;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.6;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        
        .GENIUS WORK-branding {
            color: var(--primary-color);
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            
            body {
                background-color: white;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .container {
                padding: 0;
                box-shadow: none;
                height: 100%;
                position: relative;
            }
            
            .billing-column, .payment-column, .header-right, .info-section, .notes {
                box-shadow: none;
                border-left: 2px solid var(--primary-color);
                page-break-inside: avoid;
            }
            
            .items-table, .summary-table {
                border: 1px solid var(--border-color);
                page-break-inside: auto;
            }
            
            .items-table tr {
                page-break-inside: avoid;
            }
            
            .summary-container {
                page-break-inside: avoid;
            }
            
            .header {
                page-break-after: avoid;
            }
            
            .billing-section {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($facture->statut_paiement === 'paye')
        <div class="status-paid">PAYÉE</div>
        @endif

        <div class="header">
            <div class="header-left">
                <div class="logo-container">
                    <span class="logo-text">Genius Work</span>
                </div>
                <p class="company-tagline">Votre solution complète de gestion d'entreprise</p>
            </div>
            
            <div class="header-right">
                <div class="invoice-number">Facture #{{ $facture->numero_facture }}</div>
                <div class="detail-row">
                    <span class="detail-label">Date d'émission:</span>
                    <span class="detail-value">{{ $facture->date_facturation->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date d'échéance:</span>
                    <span class="detail-value">{{ $facture->date_echeance->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut:</span>
                    <span class="status-badge" style="background-color: {{ $facture->statut_paiement === 'paye' ? 'var(--success-color)' : 'var(--primary-color)' }}">
                        {{ ucfirst($facture->statut_paiement) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="billing-section">
            <div class="billing-row">
                <div class="billing-column">
                    <div class="section-title">FACTURÉ À</div>
                    <div class="client-info">
                        <div class="entity-name">{{ $facture->entreprise->nom }}</div>
                        <div>{{ $facture->entreprise->adresse }}</div>
                        <div>{{ $facture->entreprise->code_postal }} {{ $facture->entreprise->ville }}</div>
                        <div>{{ $facture->entreprise->pays }}</div>
                        @if($facture->entreprise->telephone)
                        <div> {{ $facture->entreprise->telephone }}</div>
                        @endif
                        @if($facture->entreprise->email)
                        <div> {{ $facture->entreprise->email }}</div>
                        @endif
                    </div>
                </div>
                
                <div class="billing-column">
                    <div class="section-title">ÉMIS PAR</div>
                    <div class="company-info">
                        <div class="entity-name">Genius Work</div>
                        <div>Côte d'Ivoire, Abidjan</div>
                        <div>Riviera Bonoumin, GENIUS GROUPS</div>
                        <div>Côte d'Ivoire</div>
                        <div> +225 07 07 07 07 07</div>
                        <div> work@genius.ci</div>
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
                            Du {{ $facture->date_facturation->format('d/m/Y') }} au {{ $facture->date_echeance->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="text-right">{{ number_format($facture->montant_ht, 0, '', ' ') }} XOF</td>
                    <td class="text-right">{{ $facture->taux_tva }}%</td>
                    <td class="text-right">{{ number_format($facture->montant_ttc, 0, '', ' ') }} XOF</td>
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
                                Du {{ $frais->periode_debut->format('d/m/Y') }} au {{ $frais->periode_fin->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="text-right">{{ number_format($frais->montant_ht, 0, '', ' ') }} XOF</td>
                        <td class="text-right">{{ $frais->taux_tva }}%</td>
                        <td class="text-right">{{ number_format($frais->montant_ttc, 0, '', ' ') }} XOF</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <th>Total HT</th>
                    <td>{{ number_format($facture->montant_ht, 0, '', ' ') }} XOF</td>
                </tr>
                <tr>
                    <th>TVA ({{ $facture->taux_tva }}%)</th>
                    <td>{{ number_format($facture->montant_tva, 0, '', ' ') }} XOF</td>
                </tr>
                <tr class="total-row">
                    <th>Total TTC</th>
                    <td>{{ number_format($facture->montant_ttc, 0, '', ' ') }} XOF</td>
                </tr>
            </table>
        </div>


        <div class="footer">
            <span class="GENIUS WORK-branding">Genius Work</span><br>
            Côte d'Ivoire, Abidjan, Riviera Bonoumin, GENIUS GROUPS<br>
            work.genius.ci | work@genius.ci | +225 07 07 07 07 07
        </div>
    </div>
</body>
</html>