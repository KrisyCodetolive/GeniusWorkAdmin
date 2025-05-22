<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte d'employé - {{ $employeur->nom_complet }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            font-size: 12px;
        }
        .carte-container {
            width: 100%;
            max-width: 420px;
            height: 260px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
            border: 1px solid #e5e7eb;
        }
        .header {
            height: 52px;
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            color: white;
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }
        .logo-container {
            display: flex;
            align-items: center;
            z-index: 2;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .logo {
            height: 28px;
            width: auto;
            filter: brightness(0) invert(1);
            margin-right: 8px;
        }
        .titre {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            z-index: 2;
            position: relative;
        }
        .contenu {
            display: flex;
            padding: 16px;
            position: relative;
            z-index: 2;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.9), rgba(249, 250, 251, 0.9));
        }
        .colonne-gauche {
            width: 110px;
            margin-right: 20px;
        }
        .colonne-centre {
            flex: 1;
            padding-right: 16px;
        }
        .colonne-droite {
            width: 110px;
        }
        .photo-container {
            width: 110px;
            height: 140px;
            overflow: hidden;
            border: 2px solid #4f46e5;
            border-radius: 10px;
            margin-bottom: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .nom {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            display: inline-block;
        }
        .poste {
            font-size: 14px;
            font-style: italic;
            margin-bottom: 14px;
            color: #4f46e5;
            font-weight: 500;
        }
        .detail {
            font-size: 12px;
            margin-bottom: 8px;
            color: #4B5563;
            display: flex;
            align-items: center;
        }
        .detail-label {
            color: #4B5563;
            font-weight: 600;
            margin-right: 4px;
        }
        .detail-value {
            color: #6B7280;
        }
        .qrcode-container {
            width: 110px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 10px;
            border: 2px solid #4f46e5;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 4px;
        }
        .qrcode {
            width: 100%;
            height: 100%;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 16px;
            background-color: #f3f4f6;
            font-size: 10px;
            color: #6B7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }
        .badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background-color: #c7d2fe;
            color: #4f46e5;
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            font-weight: bold;
            z-index: 3;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
        }
        .italic {
            font-style: italic;
        }
        .info-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 3px solid #4f46e5;
        }
    </style>
</head>
<body>
    <div class="carte-container">
        <!-- Badge de statut -->
        <div class="badge">ACTIF</div>
        
        <!-- En-tête -->
        <div class="header">
            <div class="logo-container">
                {{ $entreprise->nom ?? 'GENIUS GROUPS' }}
            </div>
            <div class="titre">Carte d'employé</div>
        </div>
        
        <!-- Contenu principal -->
        <div class="contenu">
            <!-- Colonne Gauche (Photo) -->
            <div class="colonne-gauche">
                <div class="photo-container">
                    <div style="width: 100%; height: 100%; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; color: #9ca3af; font-size: 12px; text-align: center;">
                        Photo
                    </div>
                </div>
            </div>
            
            <!-- Colonne Centre (Informations) -->
            <div class="colonne-centre">
                <div class="nom">{{ $employeur->nom }} {{ $employeur->prenom }}</div>
                <div class="poste">{{ $employeur->poste }}</div>
                
                <div class="info-section">
                    <div class="detail">
                        <span class="detail-label">Matricule:</span> 
                        <span class="detail-value">{{ $employeur->matricule ?? 'N/A' }}</span>
                    </div>
                    <div class="detail">
                        <span class="detail-label">Département:</span> 
                        <span class="detail-value">{{ $employeur->departement->nom ?? 'N/A' }}</span>
                    </div>
                    
                    @if($employeur->telephone)
                    <div class="detail">
                        <span class="detail-label">Tél:</span>
                        <span class="detail-value">{{ $employeur->telephone }}</span>
                    </div>
                    @endif
                    
                    @if($employeur->email)
                    <div class="detail">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $employeur->email }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Colonne Droite (QR Code) -->
            <div class="colonne-droite">
                <div class="qrcode-container">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qrcode">
                </div>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="footer">
            <span>Généré le: {{ now()->format('d/m/Y') }}</span>
            <span>{{ $entreprise->nom ?? 'GENIUS GROUPS' }}</span>
            <span class="italic">Genius Work</span>
        </div>
    </div>
</body>
</html>