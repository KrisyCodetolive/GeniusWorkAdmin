<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture GENIUS WORK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px 0;
        }
        .invoice-details .label {
            font-weight: bold;
            width: 150px;
        }
        .client-details {
            margin-bottom: 30px;
        }
        .client-details h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .summary-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .summary-table .total {
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .payment-info {
            margin-top: 30px;
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
        }
        .payment-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">GENIUS WORK</div>
            <div>Gestion de Pointage & RH</div>
        </div>
        
        <div class="invoice-details">
            <table>
                <tr>
                    <td class="label">Facture N°:</td>
                    <td>{{ $payment['invoice_number'] }}</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td>{{ $payment['payment_date']->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Méthode de paiement:</td>
                    <td>
                        @if($payment['payment_method'] == 'card')
                            Carte de crédit
                        @elseif($payment['payment_method'] == 'mobile_money')
                            Mobile Money
                        @elseif($payment['payment_method'] == 'bank_transfer')
                            Virement bancaire
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="client-details">
            <h3>Informations client</h3>
            <p>
                <strong>Entreprise:</strong> {{ $company['company_name'] }}<br>
                <strong>Adresse:</strong> {{ $company['address'] }}<br>
                <strong>Contact:</strong> {{ $user['full_name'] }}<br>
                <strong>Email:</strong> {{ $company['contact_email'] }}<br>
                <strong>Téléphone:</strong> {{ $company['contact_phone'] }}
            </p>
        </div>
        
        <div class="summary">
            <h3>Récapitulatif de l'abonnement</h3>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Détails</th>
                        <th>Montant (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Forfait {{ $subscription['subscription_plan'] }}</td>
                        <td>Coût de base</td>
                        <td>{{ number_format($subscription['base_cost']) }}</td>
                    </tr>
                    <tr>
                        <td>Utilisateurs</td>
                        <td>{{ $company['company_size'] }} utilisateurs x 100 FCFA</td>
                        <td>{{ number_format($subscription['user_cost']) }}</td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">Total mensuel</td>
                        <td>{{ number_format($subscription['total_cost']) }} FCFA</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="payment-info">
            <h3>Informations de paiement</h3>
            <p>
                Votre abonnement est actif et sera renouvelé automatiquement chaque mois.<br>
                Prochain paiement prévu le: {{ now()->addMonth()->format('d/m/Y') }}
            </p>
        </div>
        
        <div class="footer">
            <p>
                GENIUS WORK - Gestion de Pointage & RH<br>
                support@genius-work.com | www.genius-work.com<br>
                © {{ now()->format('Y') }} GENIUS WORK. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
