<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Présences</title>
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
    </style>
</head>
<body>
    <h1>Rapport de Présences</h1>
    
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
            <div class="stat-title">Taux de présence</div>
            <div class="stat-value">{{ $statistiques['taux_presence'] }}%</div>
        </div>
        <div class="stat-box bg-warning">
            <div class="stat-title">Taux de retard</div>
            <div class="stat-value">{{ $statistiques['taux_retard'] }}%</div>
        </div>
        <div class="stat-box bg-danger">
            <div class="stat-title">Total absences</div>
            <div class="stat-value">{{ $statistiques['total_absences'] }}</div>
        </div>
    </div>
    
    <h2>Détails par employé</h2>
    
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Heures de présence</th>
                <th>Jours de présence</th>
                <th>Jours d'absence</th>
                <th>Retards</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donnees as $donnee)
            <tr>
                <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                <td>{{ $donnee['heures_presence']['heures_formatees'] }}</td>
                <td>{{ $donnee['jours_presence'] }}</td>
                <td>{{ $donnee['jours_absence'] }}</td>
                <td>{{ count($donnee['retards']['details_jours']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    <h2>Détails des retards</h2>
    
    @foreach($donnees as $donnee)
        @if(count($donnee['retards']['details_jours']) > 0)
            <h3>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure prévue</th>
                        <th>Heure réelle</th>
                        <th>Retard (minutes)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnee['retards']['details_jours'] as $retard)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($retard['date'])->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($retard['heure_prevue'])->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($retard['heure_reelle'])->format('H:i') }}</td>
                        <td>{{ $retard['minutes_retard'] }}</td>
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
