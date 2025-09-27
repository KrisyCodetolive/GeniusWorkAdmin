<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Toutes les Affiches QR Code - {{ $entreprise->nom }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .document-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .document-subtitle {
            font-size: 14px;
            color: #64748b;
        }
        
        .poster-container {
            margin-bottom: 40px;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .poster-header {
            background: #667eea;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .site-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .site-address {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .poster-content {
            padding: 25px;
        }
        
        .poster-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .qr-section {
            text-align: center;
            margin: 25px 0;
        }
        
        .qr-code-container {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .qr-code-container img,
        .qr-code-container svg {
            width: 180px;
            height: 180px;
            display: block;
            background: white;
            border-radius: 4px;
            padding: 8px;
        }
        
        .instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .instructions-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
        }
        
        .steps-list li {
            margin-bottom: 4px;
            color: #92400e;
        }
        
        .info-section {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-cell {
            display: table-cell;
            padding: 5px 10px;
            vertical-align: top;
        }
        
        .info-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        
        .info-value {
            font-size: 11px;
            color: #1e293b;
            font-weight: bold;
            margin-top: 2px;
        }
        
        .warning-box {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 12px;
            margin: 15px 0;
            text-align: center;
        }
        
        .warning-title {
            font-size: 11px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }
        
        .warning-text {
            font-size: 10px;
            color: #dc2626;
            line-height: 1.3;
        }
        
        .footer-info {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Styles pour les deux colonnes sur une page */
        .two-column-layout {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .column:first-child {
            padding-left: 0;
        }
        
        .column:last-child {
            padding-right: 0;
        }
        
        .column .poster-container {
            margin-bottom: 20px;
        }
        
        .column .qr-code-container svg {
            width: 140px;
            height: 140px;
        }
        
        .column .poster-content {
            padding: 20px 15px;
        }
        
        .column .poster-title {
            font-size: 16px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Document Header -->
    <div class="document-header">
        <div class="document-title">Affiches QR Code - Pointage Mobile</div>
        <div class="document-subtitle">{{ $entreprise->nom }} • {{ count($sitesData) }} site(s) • Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>
    
    @php
        $chunkedSites = $sitesData->chunk(2); // Grouper par 2 pour affichage en colonnes
    @endphp
    
    @foreach($chunkedSites as $chunkIndex => $siteChunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="two-column-layout">
            @foreach($siteChunk as $columnIndex => $siteData)
                <div class="column">
                    <div class="poster-container">
                        <!-- Poster Header -->
                        <div class="poster-header">
                            <div class="site-name">{{ $siteData['site']->nom }}</div>
                            <div class="site-address">{{ $siteData['site']->adresse }}, {{ $siteData['site']->ville }}</div>
                        </div>
                        
                        <!-- Poster Content -->
                        <div class="poster-content">
                            <div class="poster-title">Pointage Mobile</div>
                            
                            <!-- QR Code Section -->
                            <div class="qr-section">
                                <div class="qr-code-container">
                                    <!-- Génération d'un QR code via notre générateur sécurisé -->
                                    <img src="{{ \App\Helpers\QRCodeGenerator::getQRCodeUrl($siteData['pointageUrl'], 180) }}" alt="QR Code pour {{ $siteData['site']->nom }}" width="180" height="180" />
                                </div>
                            </div>
                            
                            <!-- Instructions -->
                            <div class="instructions">
                                <div class="instructions-title">📱 Instructions de pointage :</div>
                                <ol class="steps-list">
                                    <li>1. Scannez ce QR code avec votre smartphone</li>
                                    <li>2. Authentifiez-vous avec votre compte Google</li>
                                    <li>3. Autorisez la géolocalisation</li>
                                    <li>4. Confirmez votre pointage d'entrée ou sortie</li>
                                </ol>
                            </div>
                            
                            <!-- Site Information -->
                            <div class="info-section">
                                <div class="info-grid">
                                    <div class="info-row">
                                        <div class="info-cell">
                                            <div class="info-label">Site</div>
                                            <div class="info-value">{{ $siteData['site']->nom }}</div>
                                        </div>
                                        <div class="info-cell">
                                            <div class="info-label">Entreprise</div>
                                            <div class="info-value">{{ $entreprise->nom }}</div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-cell">
                                            <div class="info-label">Expire le</div>
                                            <div class="info-value">{{ $siteData['expiresAt']->format('d/m/Y à H:i') }}</div>
                                        </div>
                                        <div class="info-cell">
                                            @if($siteData['site']->has_geofencing)
                                                <div class="info-label">Géofencing</div>
                                                <div class="info-value">{{ $siteData['site']->rayon_geofencing }}m</div>
                                            @else
                                                <div class="info-label">Géofencing</div>
                                                <div class="info-value">Désactivé</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Warning -->
                            <div class="warning-box">
                                <div class="warning-title">⚠️ Important</div>
                                <div class="warning-text">
                                    Ce QR code expire automatiquement après 24 heures pour des raisons de sécurité.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
    
    <!-- Footer -->
    <div class="footer-info">
        Système de Pointage Mobile avec WebAuthn • Authentification sécurisée • 
        En cas de problème, contactez votre administrateur
    </div>
</body>
</html>
