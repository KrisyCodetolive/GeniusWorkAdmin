<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code de Pointage - {{ $site->nom }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .site-info {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }
        
        .qr-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .qr-code {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            display: inline-block;
        }
        
        .instructions {
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        
        .instructions h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        
        @media print {
            body {
                background-color: #fff;
            }
            
            .no-print {
                display: none;
            }
            
            .container {
                width: 100%;
                max-width: none;
                padding: 0;
            }
            
            .instructions {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($entreprise->logo)
                <img src="{{ asset('storage/' . $entreprise->logo) }}" alt="{{ $entreprise->nom }}" class="logo">
            @endif
            <h1>QR Code de Pointage</h1>
            <div class="site-info">
                <p>{{ $entreprise->nom }} - Site: {{ $site->nom }}</p>
                <p>{{ $site->adresse }}, {{ $site->code_postal }} {{ $site->ville }}</p>
            </div>
        </div>
        
        <div class="qr-container">
            <div class="qr-code">
                {!! $qrCode !!}
            </div>
        </div>
        
        <div class="instructions">
            <h2>Comment utiliser ce QR code :</h2>
            <ol>
                <li>Ouvrez l'application GENIUS WORK sur votre téléphone</li>
                <li>Connectez-vous à votre compte</li>
                <li>Appuyez sur "Scanner un QR code"</li>
                <li>Scannez ce QR code pour enregistrer votre pointage</li>
                <li>Sélectionnez le type de pointage (entrée, sortie, pause)</li>
                <li>Confirmez avec votre authentification biométrique</li>
            </ol>
        </div>
        
        <div class="footer">
            <p>QR code généré le {{ date('d/m/Y à H:i') }} - Valide pendant 24 heures</p>
            <p>GENIUS WORK - Système de pointage sécurisé</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print();" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Imprimer
            </button>
            <button onclick="window.close();" style="padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Fermer
            </button>
        </div>
    </div>
    
    <script>
        // Déclencher automatiquement l'impression
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
