<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis GENIUS WORK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .quote-info {
            margin-bottom: 30px;
        }
        .quote-number {
            color: #4F46E5;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            color: #4F46E5;
        }
        .footer {
            margin-top: 40px;
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GENIUS WORK - Devis</h1>
        <p>Solution de Gestion de Présence et de Temps de Travail</p>
    </div>

    <div class="quote-info">
        <p><strong>Numéro de devis:</strong> <span class="quote-number">{{ $numeroDevis }}</span></p>
        <p><strong>Date:</strong> {{ $date }}</p>
        <p><strong>Validité:</strong> 30 jours</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Détails</th>
                <th>Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Forfait {{ $forfait }}</td>
                <td>Coût fixe mensuel</td>
                <td>{{ number_format($coutFixe, 0, '.', ',') }} FCFA</td>
            </tr>
            <tr>
                <td>Utilisateurs</td>
                <td>{{ $nombreUtilisateurs }} utilisateurs × {{ number_format($coutParUtilisateur, 0, '.', ',') }} FCFA</td>
                <td>{{ number_format($coutUtilisateurs, 0, '.', ',') }} FCFA</td>
            </tr>
            <tr class="total">
                <td colspan="2">Total Mensuel</td>
                <td>{{ number_format($coutTotal, 0, '.', ',') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    <div class="details">
        <h3>Détails du Forfait {{ $forfait }}</h3>
        <ul>
            @if($forfait === 'Starter')
                <li>Idéal pour les petites équipes (1-50 utilisateurs)</li>
                <li>Coût fixe mensuel: 10 000 FCFA</li>
            @elseif($forfait === 'Side Business')
                <li>Parfait pour les entreprises en croissance (50-100 utilisateurs)</li>
                <li>Coût fixe mensuel: 15 000 FCFA</li>
            @else
                <li>Solution complète pour les grandes entreprises (100+ utilisateurs)</li>
                <li>Coût fixe mensuel: 30 000 FCFA</li>
            @endif
            <li>Coût par utilisateur: 100 FCFA/mois</li>
        </ul>
    </div>

    <div class="footer">
        <p><strong>Conditions de paiement:</strong> Paiement à 30 jours</p>
        <p><strong>Notes:</strong></p>
        <ul>
            <li>Prix en FCFA</li>
            <li>TVA non applicable</li>
            <li>Facturation mensuelle</li>
            <li>Engagement minimum de 3 mois</li>
        </ul>
        <p style="text-align: center; margin-top: 20px;">
            Genius Groups - Innovons pour votre succès
        </p>
    </div>
</body>
</html>
