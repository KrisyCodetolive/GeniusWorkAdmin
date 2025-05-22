<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Congés</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .info {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            width: 23%;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .bg-info {
            background-color: #d1ecf1;
        }
        .bg-success {
            background-color: #d4edda;
        }
        .bg-warning {
            background-color: #fff3cd;
        }
        .bg-danger {
            background-color: #f8d7da;
        }
        .stat-title {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .progress-container {
            width: 100%;
            background-color: #f1f1f1;
            border-radius: 5px;
            height: 15px;
        }
        .progress-bar {
            height: 15px;
            border-radius: 5px;
            text-align: center;
            line-height: 15px;
            color: white;
            font-size: 10px;
        }
        .progress-success {
            background-color: #4CAF50;
        }
        .progress-warning {
            background-color: #ff9800;
        }
        .progress-danger {
            background-color: #f44336;
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
            padding: 10px 0;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-danger {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>Rapport de Congés</h1>
    
    <div class="info">
        <strong>Période :</strong> {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}
    </div>
    
    <h2>Statistiques globales</h2>
    
    <div class="stats-container">
        <div class="stat-box bg-info">
            <div class="stat-title">Nombre d'employés</div>
            <div class="stat-value">{{ $statistiques['total_employes'] }}</div>
        </div>
        <div class="stat-box bg-success">
            <div class="stat-title">Total congés utilisés</div>
            <div class="stat-value">{{ $statistiques['total_conges_utilises'] }} jours</div>
        </div>
        <div class="stat-box bg-warning">
            <div class="stat-title">Total congés restants</div>
            <div class="stat-value">{{ $statistiques['total_conges_restants'] }} jours</div>
        </div>
        <div class="stat-box bg-danger">
            <div class="stat-title">Moyenne par employé</div>
            <div class="stat-value">{{ $statistiques['moyenne_conges_utilises'] }} jours</div>
        </div>
    </div>
    
    <h2>Détails par employé</h2>
    
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Congés annuels</th>
                <th>Jours utilisés</th>
                <th>Jours restants</th>
                <th>Pourcentage utilisé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donnees as $donnee)
            @php
                $congesAnnuels = $donnee['employe']->conges_annuels ?? 0;
                $pourcentageUtilise = $congesAnnuels > 0 
                    ? round(($donnee['jours_utilises'] / $congesAnnuels) * 100, 2) 
                    : 0;
                
                $progressClass = $pourcentageUtilise > 75 ? 'progress-danger' : ($pourcentageUtilise > 50 ? 'progress-warning' : 'progress-success');
            @endphp
            <tr>
                <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                <td>{{ $congesAnnuels }}</td>
                <td>{{ $donnee['jours_utilises'] }}</td>
                <td>{{ $donnee['jours_restants'] }}</td>
                <td>
                    <div class="progress-container">
                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $pourcentageUtilise }}%">
                            {{ $pourcentageUtilise }}%
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    <h2>Détails des congés par employé</h2>
    
    @foreach($donnees as $donnee)
        @if(count($donnee['conges']) > 0)
            <h3>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Type de congé</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Durée (jours)</th>
                        <th>Motif</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnee['conges'] as $conge)
                    @php
                        $duree = $conge->date_debut->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                            return $date->isWeekday();
                        }, $conge->date_fin);
                        
                        $badgeClass = $conge->statut === 'approuve' ? 'badge-success' : ($conge->statut === 'refuse' ? 'badge-danger' : 'badge-warning');
                    @endphp
                    <tr>
                        <td>{{ $conge->type }}</td>
                        <td>{{ $conge->date_debut->format('d/m/Y') }}</td>
                        <td>{{ $conge->date_fin->format('d/m/Y') }}</td>
                        <td>{{ $duree }}</td>
                        <td>{{ $conge->motif }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst($conge->statut) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
    
    <div class="footer">
        Rapport généré le {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | GENIUS WORK
    </div>
</body>
</html>
