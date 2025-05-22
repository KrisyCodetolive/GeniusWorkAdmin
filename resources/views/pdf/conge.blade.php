<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attestation de Congé</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 11pt;
            line-height: 1.4;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
            position: relative;
        }
        .logo-text {
            font-size: 18pt;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        h1 {
            color: #2c3e50;
            font-size: 20pt;
            margin: 10px 0 5px 0;
        }
        .reference {
            font-size: 10pt;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 15px;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
        }
        .status.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status.secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-section h2 {
            color: #3498db;
            font-size: 14pt;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin: 10px 0;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 180px;
        }
        .info-value {
            flex: 1;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .signature {
            margin-top: 30px;
            padding: 10px;
            border-top: 1px dashed #ddd;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 30px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin-top: 40px;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 20%;
            transform: rotate(-45deg);
            opacity: 0.05;
            font-size: 80pt;
            font-weight: bold;
            color: #3498db;
            z-index: -1;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .details-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .details-table td:first-child {
            font-weight: bold;
            width: 180px;
        }
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
        }
        .column {
            width: 48%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">GENIUS WORK</div>
        
        <div class="header">
            <div class="logo-text">GENIUS WORK</div>
            <h1>Attestation de Congé</h1>
            <div class="reference">Référence: CONGE-{{ $conge->id }}</div>
            <div class="status {{ $statutClass }}">{{ $statutLabel }}</div>
        </div>

        <div class="info-section">
            <h2>Informations de l'employé</h2>
            <div class="info-row">
                <div class="info-label">Nom de l'employé:</div>
                <div class="info-value">{{ $conge->employeur->nom }} {{ $conge->employeur->prenom }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Entreprise:</div>
                <div class="info-value">{{ $conge->employeur->entreprise->nom ?? 'Non spécifié' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Département:</div>
                <div class="info-value">{{ $conge->employeur->departement->nom ?? 'Non spécifié' }}</div>
            </div>
        </div>

        <div class="info-section">
            <h2>Détails du congé</h2>
            <table class="details-table">
                <tr>
                    <td>Type de congé:</td>
                    <td>{{ $conge->typeConge->nom }}</td>
                </tr>
                <tr>
                    <td>Date de début:</td>
                    <td>{{ $dateDebut }}</td>
                </tr>
                <tr>
                    <td>Date de fin:</td>
                    <td>{{ $dateFin }}</td>
                </tr>
                <tr>
                    <td>Durée (jours ouvrables):</td>
                    <td>{{ $conge->duree_jours }}</td>
                </tr>
                <tr>
                    <td>Congé payé:</td>
                    <td>{{ $conge->est_paye ? 'Oui' : 'Non' }}</td>
                </tr>
                @if($conge->motif)
                <tr>
                    <td>Motif:</td>
                    <td>{{ $conge->motif }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="info-section">
            <h2>Validation</h2>
            <table class="details-table">
                <tr>
                    <td>Statut:</td>
                    <td>{{ $statutLabel }}</td>
                </tr>
                @if($conge->validateur)
                <tr>
                    <td>Validé par:</td>
                    <td>{{ $conge->validateur->name }}</td>
                </tr>
                @endif
                <tr>
                    <td>Date de validation:</td>
                    <td>{{ $dateValidation }}</td>
                </tr>
                @if($conge->commentaire_validation)
                <tr>
                    <td>Commentaire:</td>
                    <td>{{ $conge->commentaire_validation }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="signature">
            <div class="signature-title">Signature du responsable:</div>
            <div class="signature-line"></div>
        </div>
    </div>

    <div class="footer">
        <p>Ce document est généré automatiquement par l'application Genius Work et nécessite une signature manuscrite pour être valide.</p>
        <p>Document généré le {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
