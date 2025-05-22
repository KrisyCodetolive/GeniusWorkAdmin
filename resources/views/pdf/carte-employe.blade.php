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
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
        }
        .header {
            height: 48px;
            background: linear-gradient(to right, #3a5faa, #324f88);
            color: white;
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-container {
            display: flex;
            align-items: center;
        }
        .logo {
            height: 24px;
            width: auto;
            filter: brightness(0) invert(1);
        }
        .titre {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .contenu {
            display: flex;
            padding: 16px;
            position: relative;
            z-index: 2;
        }
        .colonne-gauche {
            width: 110px;
            margin-right: 16px;
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
            border: 2px solid #3a5faa;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .nom {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #111827;
        }
        .poste {
            font-size: 14px;
            font-style: italic;
            margin-bottom: 12px;
            color: #6B7280;
        }
        .detail {
            font-size: 12px;
            margin-bottom: 6px;
            color: #4B5563;
        }
        .detail-label {
            color: #6B7280;
            width: 85px;
            display: inline-block;
        }
        .qrcode-container {
            width: 110px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
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
            background-color: #f9fafb;
            font-size: 10px;
            color: #6B7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }
        .badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: #d1fae5;
            color: #3a5faa;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            font-weight: bold;
            z-index: 3;
        }
    </style>
</head>
<body>
    <div class="carte-container">
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
                
                <div class="detail">
                    <span class="detail-label">Matricule: {{ $employeur->matricule ?? 'N/A' }}</span> 
                </div>
                <div class="detail">
                    <span class="detail-label">Département: {{ $employeur->departement->nom ?? 'N/A' }}</span> 
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