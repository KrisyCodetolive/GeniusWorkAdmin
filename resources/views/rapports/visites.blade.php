<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des visites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #4f46e5;
        }
        .header p {
            font-size: 12px;
            margin: 5px 0;
            color: #666;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .info-box h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #4f46e5;
        }
        .stats-container {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        .stat-box {
            flex: 1;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .stat-box h3 {
            font-size: 12px;
            margin: 0;
            color: #666;
        }
        .stat-box p {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .total { color: #4f46e5; }
        .en-cours { color: #3b82f6; }
        .terminees { color: #10b981; }
        .annulees { color: #ef4444; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
        .chart-container {
            width: 100%;
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rapport des visites</h1>
            <p>Période : {{ $dateDebut }} - {{ $dateFin }}</p>
            @if(isset($site))
                <p>Site : {{ $site->nom }}</p>
            @endif
            @if(isset($entreprise))
                <p>Entreprise : {{ $entreprise->nom }}</p>
            @endif
            <p>Généré le : {{ $date_generation }}</p>
        </div>
        
        <div class="info-box">
            <h2>Résumé</h2>
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total des visites</h3>
                    <p class="total">{{ $stats['total'] }}</p>
                </div>
                <div class="stat-box">
                    <h3>En cours</h3>
                    <p class="en-cours">{{ $stats['en_cours'] }}</p>
                </div>
                <div class="stat-box">
                    <h3>Terminées</h3>
                    <p class="terminees">{{ $stats['terminees'] }}</p>
                </div>
                <div class="stat-box">
                    <h3>Annulées</h3>
                    <p class="annulees">{{ $stats['annulees'] }}</p>
                </div>
            </div>
        </div>
        
        @if(!empty($statsBySite))
            <div class="info-box">
                <h2>Répartition par site</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>Total</th>
                            <th>En cours</th>
                            <th>Terminées</th>
                            <th>Annulées</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statsBySite as $siteStat)
                            <tr>
                                <td>{{ $siteStat['nom'] }}</td>
                                <td>{{ $siteStat['total'] }}</td>
                                <td>{{ $siteStat['en_cours'] }}</td>
                                <td>{{ $siteStat['terminees'] }}</td>
                                <td>{{ $siteStat['annulees'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
        <div class="info-box">
            <h2>Liste des visites</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Visiteur</th>
                        <th>Organisation</th>
                        @if(empty($site))
                            <th>Site</th>
                        @endif
                        <th>Personne à rencontrer</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Durée</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visites as $visite)
                        <tr>
                            <td>{{ Carbon\Carbon::parse($visite->date_arrivee)->format('d/m/Y H:i') }}</td>
                            <td>{{ $visite->visiteur->nom_complet }}</td>
                            <td>{{ $visite->visiteur->organisation }}</td>
                            @if(empty($site))
                                <td>{{ $visite->site->nom }}</td>
                            @endif
                            <td>{{ $visite->personne_a_rencontrer }}</td>
                            <td>{{ $visite->motif }}</td>
                            <td>
                                @if($visite->statut == 'en_cours')
                                    <span style="color: #3b82f6;">En cours</span>
                                @elseif($visite->statut == 'terminee')
                                    <span style="color: #10b981;">Terminée</span>
                                @elseif($visite->statut == 'annulee')
                                    <span style="color: #ef4444;">Annulée</span>
                                @endif
                            </td>
                            <td>
                                @if($visite->date_depart && $visite->statut == 'terminee')
                                    {{ Carbon\Carbon::parse($visite->date_arrivee)->diffForHumans(Carbon\Carbon::parse($visite->date_depart), true) }}
                                @elseif($visite->statut == 'en_cours')
                                    En cours
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="footer">
        <p>Généré via Genius Work | {{ $date_generation }}</p>
    </div>
</body>
</html>
