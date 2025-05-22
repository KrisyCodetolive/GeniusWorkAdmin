<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Visite</title>
    <style>
        @page {
            size: 80mm 150mm; /* Format standard pour tickets thermiques ou petits tickets */
            margin: 5mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            width: 70mm; /* Largeur standard pour tickets thermiques */
            line-height: 1.2;
        }
        .ticket {
            width: 100%;
            margin: 0 auto;
            padding: 2mm;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 1px solid #333;
            padding-bottom: 2mm;
        }
        .logo {
            max-width: 40mm;
            height: auto;
            margin-bottom: 2mm;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin: 1mm 0;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin: 1mm 0;
        }
        .site-name {
            font-size: 10pt;
            margin-bottom: 1mm;
        }
        .info-section {
            margin-bottom: 3mm;
        }
        .info-section h3 {
            font-size: 10pt;
            margin: 0;
            padding: 1mm 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row {
            margin-bottom: 0.5mm;
            font-size: 9pt;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: auto;
            margin-right: 1mm;
        }
        .info-value {
            display: inline-block;
        }
        .footer {
            text-align: center;
            margin-top: 3mm;
            font-size: 7pt;
            color: #666;
            padding-top: 1mm;
        }
        .footer p {
            margin: 0.5mm 0;
        }
        .genius-signature {
            font-style: italic;
            font-weight: bold;
            font-size: 8pt;
            color: #0066cc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1mm;
        }
        
        .barcode {
            text-align: center;
            margin: 3mm 0;
        }
        .id-box {
            display: inline-block;
            padding: 2mm 4mm;
            border: 1px solid #333;
            font-size: 9pt;
            font-weight: bold;
            border-radius: 2mm;
            background-color: #f8f8f8;
            width: 80%;
            text-align: center;
            word-break: break-all;
        }
        .position-info {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin: 2mm 0;
            padding: 1.5mm;
            border: 1px solid #333;
            border-radius: 2mm;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <div class="title">TICKET DE VISITE</div>
            <div class="subtitle">{{ $entreprise->nom }}</div>
            <div class="site-name">{{ $site->nom }}</div>
        </div>
        
        <div class="position-info">
            Visite N° {{ $position }} / {{ $total_visites }}
        </div>
        
        <div class="info-section">
            <h3>Informations du visiteur</h3>
            <div class="info-row">
                <div class="info-label">Nom complet:</div>
                <div class="info-value">{{ $visiteur->nom_complet }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone:</div>
                <div class="info-value">{{ $visiteur->telephone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Code visiteur:</div>
                <div class="info-value">{{ $visiteur->code_visiteur }}</div>
            </div>
        </div>
        
        
        <div class="info-section">
            <h3>Détails de la visite</h3>
            <div class="info-row">
                <div class="info-label">Date d'arrivée:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($visite->date_arrivee)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Motif:</div>
                <div class="info-value">{{ $visite->motif_visite }}</div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Personne à rencontrer:</div>
                <div class="info-value">{{ $visite->personne_a_rencontrer }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Département à visiter:</div>
                <div class="info-value">{{ $visite->departement_a_visiter }}</div>
            </div>
            @if($visite->badge_visiteur)
            <div class="info-row">
                <div class="info-label">Badge visiteur:</div>
                <div class="info-value">{{ $visite->badge_visiteur }}</div>
            </div>
            @endif
        </div>
        
        <div class="barcode">
            <div class="id-box">ID: {{ $visite->uuid ?? $visite->id }}</div>
        </div>
        
        <div class="footer">
            <p>Ticket généré le {{ $date_generation }}</p>
            <p>Veuillez conserver ce ticket jusqu'à la fin de votre visite.</p>
            <p>{{ $entreprise->nom }} - {{ $site->adresse }}</p>
            <div class="genius-signature">Généré via Genius Work</div>
        </div>
    </div>
</body>
</html>
