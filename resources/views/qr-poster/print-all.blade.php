<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Affiches QR Code - {{ $entreprise->nom }}</title>
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
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 32px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 18px;
            color: #64748b;
        }
        
        .posters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .poster-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .poster-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .site-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .site-address {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .poster-content {
            padding: 30px 25px;
            text-align: center;
        }
        
        .qr-code-container {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            display: inline-block;
        }
        
        .qr-code-container svg {
            width: 200px;
            height: 200px;
            display: block;
            background: white;
            border-radius: 8px;
            padding: 10px;
        }
        
        .poster-info {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 600;
        }
        
        .instructions {
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
            margin-top: 15px;
            padding: 10px;
            background: #fef3c7;
            border-radius: 6px;
            border: 1px solid #f59e0b;
        }
        
        .print-actions {
            text-align: center;
            margin: 30px 0;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            border: none;
            cursor: pointer;
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
            
            .posters-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .poster-card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
                break-inside: avoid;
            }
            
            .header {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
        }
        
        @media (max-width: 768px) {
            .posters-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Affiches QR Code - {{ $entreprise->nom }}</h1>
        <p>{{ count($sitesData) }} site(s) avec QR codes actifs</p>
    </div>
    
    <!-- Print Actions -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Imprimer toutes les affiches
        </button>
        <a href="{{ route('qr-poster.download-all-pdf') }}" class="btn btn-secondary">
            📄 Télécharger tout en PDF
        </a>
        <button onclick="window.history.back()" class="btn btn-secondary">
            ← Retour
        </button>
    </div>
    
    <!-- Posters Grid -->
    <div class="posters-grid">
        @foreach($sitesData as $siteData)
        <div class="poster-card">
            <!-- Poster Header -->
            <div class="poster-header">
                <div class="site-name">{{ $siteData['site']->nom }}</div>
                <div class="site-address">{{ $siteData['site']->adresse }}, {{ $siteData['site']->ville }}</div>
            </div>
            
            <!-- Poster Content -->
            <div class="poster-content">
                <h3 style="font-size: 18px; color: #1e293b; margin-bottom: 15px;">Pointage Mobile</h3>
                
                <!-- QR Code -->
                <div class="qr-code-container">
                    <div class="qrcode" data-url="{{ $siteData['pointageUrl'] }}" id="qrcode-{{ $siteData['site']->id }}">
                        <!-- Image de secours si JavaScript est désactivé -->
                        <noscript>
                            <img src="{{ \App\Helpers\QRCodeGenerator::getQRCodeUrl($siteData['pointageUrl'], 200) }}" alt="QR Code pour {{ $siteData['site']->nom }}" width="200" height="200" />
                        </noscript>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="instructions">
                    <strong>📱 Instructions :</strong><br>
                    1. Scannez ce QR code avec votre smartphone<br>
                    2. Authentifiez-vous avec votre compte Google<br>
                    3. Autorisez la géolocalisation<br>
                    4. Confirmez votre pointage
                </div>
                
                <!-- Site Info -->
                <div class="poster-info">
                    <div class="info-row">
                        <span class="info-label">Site :</span>
                        <span class="info-value">{{ $siteData['site']->nom }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Entreprise :</span>
                        <span class="info-value">{{ $entreprise->nom }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Expire le :</span>
                        <span class="info-value">{{ $siteData['expiresAt']->format('d/m/Y à H:i') }}</span>
                    </div>
                    @if($siteData['site']->has_geofencing)
                    <div class="info-row">
                        <span class="info-label">Géofencing :</span>
                        <span class="info-value">{{ $siteData['site']->rayon_geofencing }}m</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Footer Info -->
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center; margin-top: 30px;">
        <p style="color: #64748b; font-size: 14px; margin-bottom: 10px;">
            <strong>⚠️ Important :</strong> Ces QR codes expirent automatiquement après 24 heures pour des raisons de sécurité.
        </p>
        <p style="color: #64748b; font-size: 12px;">
            Généré le {{ now()->format('d/m/Y à H:i') }} • Système de Pointage Mobile avec WebAuthn
        </p>
    </div>
    
    <!-- Inclure notre bibliothèque QR Code simplifiée -->
    <script src="{{ asset('js/simple-qrcode.js') }}"></script>
    
    <script>
        // Attendre que la page soit complètement chargée
        document.addEventListener('DOMContentLoaded', function() {
            // Sélectionner tous les conteneurs de QR codes
            var qrContainers = document.querySelectorAll('.qrcode');
            console.log('Génération de ' + qrContainers.length + ' QR codes');
            
            // Générer un QR code pour chaque conteneur
            qrContainers.forEach(function(container) {
                try {
                    var pointageUrl = container.getAttribute('data-url');
                    
                    // Utiliser notre fonction simplifiée
                    generateQRCode(container, pointageUrl, 200);
                } catch (e) {
                    console.error('Erreur lors de la génération du QR code:', e);
                    container.innerHTML = '<div style="padding: 20px; background: #fee2e2; color: #b91c1c; border-radius: 5px;">Erreur de génération du QR code</div>';
                }
            });
        });
        
        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
        
        // Optimisation pour l'impression
        window.addEventListener('beforeprint', function() {
            document.body.style.background = 'white';
        });
        
        window.addEventListener('afterprint', function() {
            document.body.style.background = '#f8fafc';
        });
    </script>
</body>
</html>
