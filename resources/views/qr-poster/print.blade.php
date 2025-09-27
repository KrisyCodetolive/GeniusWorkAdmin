<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiche QR Code - {{ $site->nom }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            padding: 20px;
        }
        
        .poster-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        
        .poster-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        
        .poster-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="20" cy="80" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .site-name {
            font-size: 20px;
            font-weight: 500;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .poster-content {
            padding: 50px 40px;
            text-align: center;
        }
        
        .main-title {
            font-size: 36px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 40px;
            line-height: 1.5;
        }
        
        .qr-section {
            background: #f8fafc;
            border-radius: 20px;
            padding: 40px;
            margin: 40px 0;
            border: 3px dashed #e2e8f0;
            position: relative;
        }
        
        .qr-section::before {
            content: '📱';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 15px;
            font-size: 24px;
        }
        
        .qr-code-container {
            display: inline-block;
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .qr-code-container svg {
            display: block;
            width: 300px;
            height: 300px;
        }
        
        .qr-instructions {
            font-size: 16px;
            color: #475569;
            line-height: 1.6;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .step {
            text-align: center;
            padding: 20px;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            margin: 0 auto 15px;
        }
        
        .step-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .step-description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.4;
        }
        
        .info-section {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 16px;
            color: #1e293b;
            font-weight: 600;
        }
        
        .footer {
            background: #1e293b;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .footer-left {
            flex: 1;
            text-align: left;
        }
        
        .footer-right {
            flex: 1;
            text-align: right;
        }
        
        .footer-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .footer-text {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .print-actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            margin: 0 10px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-actions {
                display: none;
            }
            
            .poster-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }
        }
        
        @media (max-width: 768px) {
            .poster-header {
                padding: 30px 20px;
            }
            
            .poster-content {
                padding: 30px 20px;
            }
            
            .main-title {
                font-size: 28px;
            }
            
            .qr-code-container svg {
                width: 250px;
                height: 250px;
            }
            
            .steps-container {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-left,
            .footer-right {
                text-align: center;
            }
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
                    <div id="qrcode" class="qrcode-container">
                        <!-- Image de secours si JavaScript est désactivé -->
                        <noscript>
                            <img src="{{ \App\Helpers\QRCodeGenerator::getQRCodeUrl($pointageUrl, 300) }}" alt="QR Code pour {{ $site->nom }}" width="300" height="300" />
                            <p style="color: #b91c1c; margin-top: 10px;">JavaScript est désactivé. Veuillez l'activer pour une meilleure expérience.</p>
                        </noscript>
                    </div>
                </div>
                <div class="qr-instructions">
                    <strong>Instructions :</strong> Ouvrez l'appareil photo de votre téléphone et pointez-le vers ce QR code. 
                    Suivez ensuite les instructions à l'écran pour vous authentifier et enregistrer votre pointage.
                </div>
            </div>
            
            <!-- Steps -->
            <div class="steps-container">
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
            
            <!-- Site Information -->
            <div class="info-section">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Site</div>
                        <div class="info-value">{{ $site->nom }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adresse</div>
                        <div class="info-value">{{ $site->adresse }}, {{ $site->ville }}</div>
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
    
    <!-- Print Actions -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Imprimer cette affiche
        </button>
        <a href="{{ route('qr-poster.download-pdf', $site) }}" class="btn btn-secondary">
            📄 Télécharger en PDF
        </a>
        <button onclick="window.history.back()" class="btn btn-secondary">
            ← Retour
        </button>
    </div>
    
    <!-- Inclure notre bibliothèque QR Code simplifiée -->
    <script src="{{ asset('js/simple-qrcode.js') }}"></script>
    
    <script>
        // Attendre que la page soit complètement chargée
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Générer le QR code
                var pointageUrl = "{{ $pointageUrl }}";
                var qrContainer = document.getElementById('qrcode');
                
                // Utiliser notre fonction simplifiée
                generateQRCode(qrContainer, pointageUrl, 300);
            } catch (e) {
                console.error('Erreur lors de la génération du QR code:', e);
                document.getElementById('qrcode').innerHTML = '<div style="padding: 20px; background: #fee2e2; color: #b91c1c; border-radius: 5px;">Erreur de génération du QR code</div>';
            }
            
            // Optionnel : ouvrir automatiquement la boîte de dialogue d'impression
            // window.print();
        });
        
        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
