<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport d'Heures Supplémentaires</title>
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
            width: 30%;
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
        .departement-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .departement-box {
            width: 48%;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .departement-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .departement-hours {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Rapport d'Heures Supplémentaires</h1>
    
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
            <div class="stat-title">Total heures supplémentaires</div>
            <div class="stat-value">{{ $statistiques['total_heures_formatees'] }}</div>
        </div>
        <div class="stat-box bg-warning">
            <div class="stat-title">Moyenne minutes par employé</div>
            <div class="stat-value">{{ $statistiques['moyenne_minutes_par_employe'] }} min</div>
        </div>
    </div>
    
    <h2>Heures supplémentaires par département</h2>
    
    <div class="departement-container">
        @foreach($statistiques['supplementaires_par_departement'] as $departement)
            <div class="departement-box">
                <div class="departement-name">{{ $departement['departement']->nom }}</div>
                <div class="departement-hours">{{ $departement['heures_formatees'] }}</div>
            </div>
        @endforeach
    </div>
    
    <h2>Détails par employé</h2>
    
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Heures supplémentaires</th>
                <th>Nombre d'occurrences</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donnees as $donnee)
            <tr>
                <td>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</td>
                <td>{{ $donnee['employe']->departement->nom ?? 'N/A' }}</td>
                <td>{{ $donnee['heures_formatees'] }}</td>
                <td>{{ count($donnee['supplementaires']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    <h2>Détails des heures supplémentaires par employé</h2>
    
    @foreach($donnees as $donnee)
        @if(count($donnee['supplementaires']) > 0)
            <h3>{{ $donnee['employe']->nom }} {{ $donnee['employe']->prenom }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Durée</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnee['supplementaires'] as $supplementaire)
                    @php
                        $heures = floor($supplementaire->duree_minutes / 60);
                        $minutes = $supplementaire->duree_minutes % 60;
                        $dureeFormatee = sprintf('%02d:%02d', $heures, $minutes);
                        
                        $badgeClass = $supplementaire->statut === 'approuve' ? 'badge-success' : ($supplementaire->statut === 'refuse' ? 'badge-danger' : 'badge-warning');
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($supplementaire->date)->format('d/m/Y') }}</td>
                        <td>{{ $dureeFormatee }}</td>
                        <td>{{ $supplementaire->motif }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst($supplementaire->statut) }}
                            </span>
                        </td>
                        <td>{{ $supplementaire->validateur ? $supplementaire->validateur->nom . ' ' . $supplementaire->validateur->prenom : 'N/A' }}</td>
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
