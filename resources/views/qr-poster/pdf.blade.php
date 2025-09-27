<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiche QR Code - {{ $site->nom }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .poster-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            position: relative;
        }
        
        .poster-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .site-name {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .poster-content {
            padding: 40px 30px;
            text-align: center;
        }
        
        .main-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .qr-section {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        
        .qr-code-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .qr-code-container img,
        .qr-code-container svg {
            width: 250px;
            height: 250px;
            display: block;
        }
        
        .qr-instructions {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .steps-container {
            display: table;
            width: 100%;
            margin: 30px 0;
        }
        
        .steps-row {
            display: table-row;
        }
        
        .step {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px 10px;
            vertical-align: top;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .step-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .step-description {
            font-size: 12px;
            color: #64748b;
            line-height: 1.3;
        }
        
        .info-section {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }
        
        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .info-value {
            font-size: 13px;
            color: #1e293b;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        
        .footer-content {
            display: table;
            width: 100%;
        }
        
        .footer-left,
        .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }
        
        .footer-left {
            text-align: left;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .footer-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .footer-text {
            font-size: 10px;
            opacity: 0.8;
        }
        
        .warning-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .warning-title {
            font-size: 14px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .warning-text {
            font-size: 12px;
            color: #92400e;
        }
        
        .url-display {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 10px;
            word-break: break-all;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="poster-container">
        <!-- Header -->
        <div class="poster-header">
            <div class="company-logo">
                {{ strtoupper(substr($entreprise->nom, 0, 2)) }}
            </div>
            <div class="company-name">{{ $entreprise->nom }}</div>
            <div class="site-name">{{ $site->nom }}</div>
        </div>
        
        <!-- Content -->
        <div class="poster-content">
            <h1 class="main-title">Pointage Mobile</h1>
            <p class="subtitle">Scannez ce QR code avec votre smartphone pour pointer votre présence</p>
            
            <!-- QR Code Section -->
            <div class="qr-section">
                <div class="qr-code-container">
                    <!-- Génération d'un QR code via notre générateur sécurisé -->
                    <img src="{{ \App\Helpers\QRCodeGenerator::getQRCodeUrl($pointageUrl, 250) }}" alt="QR Code pour {{ $site->nom }}" width="250" height="250" />
                </div>
                <div class="qr-instructions">
                    <strong>Instructions :</strong> Ouvrez l'appareil photo de votre téléphone et pointez-le vers ce QR code. 
                    Suivez ensuite les instructions à l'écran pour vous authentifier et enregistrer votre pointage.
                </div>
            </div>
            
            <!-- Steps -->
            <div class="steps-container">
                <div class="steps-row">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-title">Scanner</div>
                        <div class="step-description">Ouvrez votre appareil photo et scannez le QR code</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">Authentifier</div>
                        <div class="step-description">Connectez-vous avec votre compte Google via WebAuthn</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">Localiser</div>
                        <div class="step-description">Autorisez la géolocalisation pour vérifier votre position</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-title">Pointer</div>
                        <div class="step-description">Confirmez votre pointage d'entrée ou de sortie</div>
                    </div>
                </div>
            </div>
            
            <!-- Site Information -->
            <div class="info-section">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">Site</div>
                            <div class="info-value">{{ $site->nom }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Adresse</div>
                            <div class="info-value">{{ $site->adresse }}<br>{{ $site->ville }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">QR Code généré le</div>
                            <div class="info-value">{{ $generatedAt->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Expire le</div>
                            <div class="info-value">{{ $expiresAt->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-title">⚠️ Important</div>
                <div class="warning-text">
                    Ce QR code expire automatiquement après 24 heures pour des raisons de sécurité. 
                    Générez un nouveau QR code si celui-ci a expiré.
                </div>
            </div>
            
            <!-- URL Display for manual entry -->
            <div style="margin-top: 20px;">
                <strong style="font-size: 12px;">URL de pointage (saisie manuelle) :</strong>
                <div class="url-display">{{ $pointageUrl }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div class="footer-title">Système de Pointage Mobile</div>
                    <div class="footer-text">Authentification sécurisée via WebAuthn</div>
                </div>
                <div class="footer-right">
                    <div class="footer-title">Support</div>
                    <div class="footer-text">En cas de problème, contactez votre administrateur</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
