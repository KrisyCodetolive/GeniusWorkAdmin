<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de présences</title>
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
        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-present {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-absent {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-retard {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-sortie {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-conge {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .validation-approuve {
            background-color: #d1fae5;
            color: #065f46;
        }
        .validation-rejete {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .validation-attente {
            background-color: #e5e7eb;
            color: #4b5563;
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
            <h1>Rapport de présences</h1>
            <p>{{ $entreprise->nom }} 
                @if($site)
                    - Site: {{ $site->nom }}
                @else
                    - Tous les sites
                @endif
            </p>
            <p>Période: {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</p>
            <p>Généré le: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        
        <!-- Résumé des statistiques -->
        <div class="stats-container">
            <div class="stat-box">
                <h3>Employés présents</h3>
                <div class="value">{{ $stats['employes_presents'] }}</div>
                <div class="subtext">sur {{ $stats['total_employes'] }} ({{ $stats['taux_presence'] }}%)</div>
            </div>
            
            <div class="stat-box">
                <h3>Heures travaillées</h3>
                <div class="value">{{ $stats['heures_travaillees'] }} h</div>
                <div class="subtext">Total sur la période</div>
            </div>
            
            <div class="stat-box">
                <h3>Minutes de retard</h3>
                <div class="value">{{ $stats['minutes_retard'] }}</div>
                <div class="subtext">Total sur la période</div>
            </div>
            
            <div class="stat-box">
                <h3>Présences validées</h3>
                <div class="value">
                    {{ isset($stats['validation']['approuve']) ? $stats['validation']['approuve'] : 0 }}
                </div>
                <div class="subtext">
                    {{ $presences->count() > 0 ? round((isset($stats['validation']['approuve']) ? $stats['validation']['approuve'] : 0) / $presences->count() * 100, 1) : 0 }}% du total
                </div>
            </div>
        </div>
        
        <!-- Statistiques détaillées -->
        <h2>Statistiques détaillées</h2>
        
        <div style="margin-bottom: 20px;">
            <h3>Présences par statut</h3>
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Nombre</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['statuts'] as $statut => $count)
                    <tr>
                        <td>
                            @if($statut == 'present') Présent 
                            @elseif($statut == 'absent') Absent 
                            @elseif($statut == 'retard') En retard 
                            @elseif($statut == 'sortie') Sorti 
                            @elseif($statut == 'conge') En congé 
                            @else {{ $statut }} @endif
                        </td>
                        <td>{{ $count }}</td>
                        <td>{{ $presences->count() > 0 ? round(($count / $presences->count()) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h3>Présences par site</h3>
            <table>
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Nombre</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['par_site'] as $site => $count)
                    <tr>
                        <td>{{ $site }}</td>
                        <td>{{ $count }}</td>
                        <td>{{ $presences->count() > 0 ? round(($count / $presences->count()) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="page-break"></div>
        
        <!-- Tableau des présences -->
        <h2>Liste des présences</h2>
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Site</th>
                    <th>Date entrée</th>
                    <th>Date sortie</th>
                    <th>Durée (min)</th>
                    <th>Statut</th>
                    <th>Validation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($presences as $presence)
                <tr>
                    <td>{{ $presence->employeur ? $presence->employeur->prenom . ' ' . $presence->employeur->nom : 'N/A' }}</td>
                    <td>{{ $presence->site ? $presence->site->nom : 'N/A' }}</td>
                    <td>{{ $presence->date_heure_entree ? $presence->date_heure_entree->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $presence->date_heure_sortie ? $presence->date_heure_sortie->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>{{ $presence->duree_effective ?? 'N/A' }}</td>
                    <td>
                        <span class="status {{ $presence->statut ? 'status-'.$presence->statut : '' }}">
                            @if($presence->statut == 'present') Présent 
                            @elseif($presence->statut == 'absent') Absent 
                            @elseif($presence->statut == 'retard') En retard 
                            @elseif($presence->statut == 'sortie') Sorti 
                            @elseif($presence->statut == 'conge') En congé 
                            @else {{ $presence->statut }} @endif
                        </span>
                    </td>
                    <td>
                        <span class="status {{ $presence->statut_validation == 'approuve' ? 'validation-approuve' : ($presence->statut_validation == 'rejete' ? 'validation-rejete' : 'validation-attente') }}">
                            @if($presence->statut_validation == 'approuve') Approuvé 
                            @elseif($presence->statut_validation == 'rejete') Rejeté 
                            @else En attente @endif
                        </span>
                    </td>
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
