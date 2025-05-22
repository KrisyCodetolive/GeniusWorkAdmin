<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport d'heures de travail</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }
        .stats-container {
            width: 100%;
            margin-bottom: 20px;
            display: table;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            vertical-align: top;
            text-align: center;
        }
        .stat-box h3 {
            font-size: 12px;
            margin: 0 0 8px 0;
            color: #555;
            text-align: center;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .stat-box .subtext {
            font-size: 10px;
            color: #777;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            font-size: 11px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête du rapport -->
        <div class="header">
            <h1>Rapport d'heures de travail</h1>
            <p>{{ isset($entreprise) ? $entreprise->nom : 'Entreprise' }} 
                @if(isset($site) && $site)
                    - Site: {{ $site->nom }}
                @else
                    - Tous les sites
                @endif
            </p>
            <p>Période: {{ $date_debut->format('d/m/Y') }} au {{ $date_fin->format('d/m/Y') }}</p>
            <p>Généré le: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        
        <!-- Résumé des statistiques -->
        <div class="stats-container">
            <div class="stat-box">
                <h3>Heures travaillées</h3>
                <div class="value">{{ $statistiques['temps_total_formate'] }}</div>
                <div class="subtext">{{ $statistiques['total_employes'] }} employés</div>
            </div>
            
            <div class="stat-box">
                <h3>Moyenne par employé</h3>
                <div class="value">{{ $statistiques['temps_moyen_par_employe_formate'] }}</div>
                <div class="subtext">Sur la période</div>
            </div>
            
            <div class="stat-box">
                <h3>Heures supplémentaires</h3>
                <div class="value">{{ $statistiques['heures_supplementaires_total_formate'] }}</div>
                <div class="subtext">Total sur la période</div>
            </div>
            
            <div class="stat-box">
                <h3>Jours travaillés</h3>
                <div class="value">{{ $statistiques['jours_travailles_total'] }}</div>
                <div class="subtext">Moyenne: {{ $statistiques['jours_travailles_moyen_par_employe'] }} jours/employé</div>
            </div>
        </div>
        
        <!-- Statistiques détaillées -->
        <h2>Statistiques détaillées</h2>
        
        <div style="margin-bottom: 20px;">
            <h3>Heures par jour de la semaine</h3>
            <table>
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Heures totales</th>
                        <th>Nombre de présences</th>
                        <th>Moyenne par présence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistiques['statistiques_par_jour_semaine'] as $jour => $stat)
                    <tr>
                        <td>{{ $stat['jour_traduit'] }}</td>
                        <td>{{ $stat['total_formate'] }}</td>
                        <td>{{ $stat['nombre_presences'] }}</td>
                        <td>{{ $stat['moyenne_formate'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h3>Heures par site</h3>
            <table>
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Heures totales</th>
                        <th>Nombre de présences</th>
                        <th>Moyenne par présence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistiques['statistiques_par_site'] as $site)
                    <tr>
                        <td>{{ $site['nom'] }}</td>
                        <td>{{ $site['total_formate'] }}</td>
                        <td>{{ $site['nombre_presences'] }}</td>
                        <td>{{ $site['moyenne_formate'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="page-break"></div>
        
        <!-- Tableau des heures de travail par employé -->
        <h2>Heures de travail par employé</h2>
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Heures travaillées</th>
                    <th>Jours travaillés</th>
                    <th>Moyenne par jour</th>
                    <th>Heures supp.</th>
                    <th>Retards</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistiques['employes'] as $donnees)
                <tr>
                    <td>{{ $donnees['employe']->prenom }} {{ $donnees['employe']->nom }}</td>
                    <td>{{ $donnees['temps_total_formate'] }}</td>
                    <td>{{ $donnees['jours_travailles'] }}</td>
                    <td>{{ $donnees['temps_moyen_par_jour_formate'] }}</td>
                    <td>{{ $donnees['heures_supplementaires_formate'] }}</td>
                    <td>{{ $donnees['retard_total_formate'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="footer">
            <p>Rapport généré automatiquement par GENIUS WORK - {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
