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
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
            border: 1px solid #e5e7eb;
        }
        .header {
            height: 48px;
            background: #4338ca;
            color: white;
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        .logo-container {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 13px;
            max-width: 60%;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .titre {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .contenu {
            display: flex;
            padding: 16px;
            position: relative;
            height: calc(100% - 96px); /* Hauteur totale moins header et footer */
        }
        .colonne-gauche {
            width: 100px;
            margin-right: 16px;
        }
        .colonne-centre {
            flex: 1;
            padding-right: 16px;
            display: flex;
            flex-direction: column;
        }
        .colonne-droite {
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .photo-container {
            width: 100px;
            height: 130px;
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #9ca3af;
            font-size: 12px;
            text-align: center;
        }
        .nom {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #4338ca;
            padding-bottom: 4px;
            display: inline-block;
        }
        .poste {
            font-size: 14px;
            font-style: italic;
            margin-bottom: 14px;
            color: #4338ca;
        }
        .info-section {
            background-color: #f9fafb;
            border-radius: 5px;
            padding: 8px 10px;
            border-left: 3px solid #4338ca;
            flex-grow: 1;
        }
        .detail {
            font-size: 12px;
            margin-bottom: 6px;
            color: #4B5563;
            display: flex;
        }
        .detail-label {
            color: #4B5563;
            font-weight: 600;
            min-width: 90px;
            display: inline-block;
        }
        .detail-value {
            color: #6B7280;
        }
        .qrcode-container {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 5px;
        }
        .qrcode {
            width: 90%;
            height: 90%;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 16px;
            background-color: #f3f4f6;
            font-size: 9px;
            color: #6B7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }
        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e0e7ff;
            color: #4338ca;
            font-size: 9px;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            font-weight: bold;
            z-index: 3;
        }
        .italic {
            font-style: italic;
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
                {{ $entreprise->nom ?? 'CDI DISTRIBUTION & MULTISERVICES' }}
            </div>
            <div class="titre">Carte d'employé</div>
        </div>
        
        <!-- Contenu principal -->
        <div class="contenu">
            <!-- Colonne Gauche (Photo) -->
            <div class="colonne-gauche">
                <div class="qrcode-container">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qrcode">
                </div>
            </div>
            
            <!-- Colonne Centre (Informations) -->
            <div class="colonne-centre">
                <div class="nom">{{ $employeur->nom }} {{ $employeur->prenom }}</div>
                <div class="poste">{{ $employeur->poste }}</div>
                
                <div class="info-section">
                    <div class="detail">
                        <span class="detail-label">Matricule:</span> 
                        <span class="detail-value">{{ $employeur->matricule ?? 'MAT-CDI-RC001' }}</span>
                    </div>
                    <div class="detail">
                        <span class="detail-label">Département:</span> 
                        <span class="detail-value">{{ $employeur->departement->nom ?? 'Logistique et Achats' }}</span>
                    </div>
                    
                    @if($employeur->telephone)
                    <div class="detail">
                        <span class="detail-label">Tél:</span>
                        <span class="detail-value">{{ $employeur->telephone }}</span>
                    </div>
                    @else
                    <div class="detail">
                        <span class="detail-label">Tél:</span>
                        <span class="detail-value">+225 07 37 38 39 40</span>
                    </div>
                    @endif
                    
                    @if($employeur->email)
                    <div class="detail">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $employeur->email }}</span>
                    </div>
                    @else
                    <div class="detail">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ strtolower($employeur->prenom) }}.{{ strtolower($employeur->nom) }}@exemple.com</span>
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
            <span>{{ $entreprise->nom ?? 'CDI DISTRIBUTION & MULTISERVICES' }}</span>
            <span class="italic">Genius Work</span>
        </div>
    </div>
</body>
</html>