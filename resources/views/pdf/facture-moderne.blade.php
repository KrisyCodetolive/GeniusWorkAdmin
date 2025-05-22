<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }} - GENIUS WORK</title>
    <style>
        /* Variables CSS */
        :root {
            --primary-color: #4f46e5;
            --primary-light: #eef2ff;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --success-light: #d1fae5;
            --border-color: #e2e8f0;
            --background-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --text-muted: #94a3b8;
        }
        
        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: var(--text-color);
            line-height: 1.5;
            font-size: 9pt;
            background-color: white;
        }
        
        /* Conteneur principal */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
            position: relative;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        /* Watermark pour factures payées */
        .status-paid {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: var(--success-color);
            opacity: 0.1;
            text-transform: uppercase;
            border: 10px solid var(--success-color);
            padding: 16px;
            font-weight: bold;
            letter-spacing: 8px;
            z-index: 0;
        }
        
        /* Utilitaires */
        .flex {
            display: flex;
        }
        
        .flex-col {
            flex-direction: column;
        }
        
        .items-center {
            align-items: center;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .justify-end {
            justify-content: flex-end;
        }
        
        .gap-2 {
            gap: 8px;
        }
        
        .gap-3 {
            gap: 12px;
        }
        
        .gap-4 {
            gap: 16px;
        }
        
        .gap-8 {
            gap: 32px;
        }
        
        .mt-2 {
            margin-top: 8px;
        }
        
        .mt-3 {
            margin-top: 12px;
        }
        
        .mt-4 {
            margin-top: 16px;
        }
        
        .mt-6 {
            margin-top: 24px;
        }
        
        .mt-8 {
            margin-top: 32px;
        }
        
        .mt-12 {
            margin-top: 48px;
        }
        
        .mb-2 {
            margin-bottom: 8px;
        }
        
        .mb-3 {
            margin-bottom: 12px;
        }
        
        .mb-4 {
            margin-bottom: 16px;
        }
        
        .p-2 {
            padding: 8px;
        }
        
        .p-3 {
            padding: 12px;
        }
        
        .p-4 {
            padding: 16px;
        }
        
        .p-6 {
            padding: 24px;
        }
        
        .p-8 {
            padding: 32px;
        }
        
        .py-1 {
            padding-top: 4px;
            padding-bottom: 4px;
        }
        
        .px-3 {
            padding-left: 12px;
            padding-right: 12px;
        }
        
        .rounded-full {
            border-radius: 9999px;
        }
        
        .rounded-lg {
            border-radius: 8px;
        }
        
        .rounded-xl {
            border-radius: 12px;
        }
        
        .border {
            border: 1px solid var(--border-color);
        }
        
        .border-t {
            border-top: 1px solid var(--border-color);
        }
        
        .border-b {
            border-bottom: 1px solid var(--border-color);
        }
        
        .text-xs {
            font-size: 7pt;
        }
        
        .text-sm {
            font-size: 8pt;
        }
        
        .text-base {
            font-size: 9pt;
        }
        
        .text-lg {
            font-size: 10pt;
        }
        
        .text-xl {
            font-size: 12pt;
        }
        
        .text-2xl {
            font-size: 14pt;
        }
        
        .text-3xl {
            font-size: 18pt;
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .font-black {
            font-weight: 900;
        }
        
        .uppercase {
            text-transform: uppercase;
        }
        
        .italic {
            font-style: italic;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-primary {
            color: var(--primary-color);
        }
        
        .text-secondary {
            color: var(--secondary-color);
        }
        
        .text-success {
            color: var(--success-color);
        }
        
        .text-white {
            color: white;
        }
        
        .text-muted {
            color: var(--text-muted);
        }
        
        .bg-primary {
            background-color: var(--primary-color);
        }
        
        .bg-primary-light {
            background-color: var(--primary-light);
        }
        
        .bg-success {
            background-color: var(--success-color);
        }
        
        .bg-success-light {
            background-color: var(--success-light);
        }
        
        .bg-white {
            background-color: white;
        }
        
        .bg-light {
            background-color: var(--background-color);
        }
        
        .w-full {
            width: 100%;
        }
        
        .w-80 {
            width: 320px;
        }
        
        .grid {
            display: grid;
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .space-y-2 > * + * {
            margin-top: 8px;
        }
        
        /* Composants spécifiques */
        .invoice-header {
            position: relative;
            z-index: 1;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            background-color: var(--primary-color);
            color: white;
            padding: 8px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14pt;
        }
        
        .logo-text {
            color: var(--primary-color);
            font-weight: 900;
            font-size: 18pt;
            letter-spacing: -0.5pt;
        }
        
        .invoice-details {
            background-color: var(--background-color);
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .invoice-number {
            color: var(--primary-color);
            font-weight: 900;
            font-size: 16pt;
            margin-bottom: 12px;
            letter-spacing: -0.5pt;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-paid {
            background-color: var(--success-light);
            color: var(--success-color);
        }
        
        .badge-pending {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .section-card {
            background-color: var(--background-color);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-color);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .entity-name {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        .table-container {
            margin-top: 48px;
            margin-bottom: 48px;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .invoice-table th {
            background-color: var(--primary-light);
            color: var(--primary-color);
            text-align: left;
            padding: 16px;
            font-weight: 600;
            font-size: 8pt;
        }
        
        .invoice-table th:first-child {
            border-top-left-radius: 12px;
        }
        
        .invoice-table th:last-child {
            border-top-right-radius: 12px;
        }
        
        .invoice-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 9pt;
            vertical-align: top;
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        .summary-table {
            width: 320px;
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
            padding: 12px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 9pt;
        }
        
        .summary-table td {
            text-align: right;
            padding: 12px;
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
            padding: 16px;
            color: white;
        }
        
        .info-box {
            background-color: var(--primary-light);
            padding: 24px;
            border-radius: 12px;
            font-size: 9pt;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            opacity: 0.9;
        }
        
        .notes-box {
            background-color: var(--background-color);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .footer {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 7pt;
            color: var(--text-muted);
            line-height: 1.5;
        }
        
        .highlight {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        /* Media queries pour l'impression */
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

        <!-- Composant: En-tête de facture -->
        @include('pdf.components.invoice-header', ['facture' => $facture])
        
        <!-- Composant: Informations de facturation -->
        @include('pdf.components.billing-info', ['facture' => $facture])
        
        <!-- Composant: Tableau des articles -->
        @include('pdf.components.invoice-items', ['facture' => $facture])
        
        <!-- Composant: Résumé des montants -->
        @include('pdf.components.invoice-summary', ['facture' => $facture])
        
        <!-- Composant: Informations de paiement -->
        @include('pdf.components.payment-info', ['facture' => $facture])
        
        <!-- Composant: Informations supplémentaires -->
        @include('pdf.components.additional-info', ['facture' => $facture])
        
        <!-- Composant: Notes -->
        @if($facture->notes)
            @include('pdf.components.notes', ['facture' => $facture])
        @endif
        
        <!-- Composant: Pied de page -->
        @include('pdf.components.invoice-footer')
    </div>
</body>
</html>
