<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export des présences</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
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
            color: #4F46E5;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 11px;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .info-box p {
            margin: 5px 0;
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
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            font-size: 11px;
        }
        td {
            padding: 6px 8px;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .page-number {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .type-entree {
            color: #10B981;
        }
        .type-sortie {
            color: #EF4444;
        }
        .type-pause_debut {
            color: #F59E0B;
        }
        .type-pause_fin {
            color: #3B82F6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Export des présences - GENIUS WORK</h1>
        <p>Généré le {{ $dateExport }} par {{ $user }}</p>
    </div>

    <div class="info-box">
        <p><strong>Filtres appliqués :</strong></p>
        <p>Type de pointage : {{ $filters['type'] }}</p>
        <p>Site : {{ $filters['site'] }}</p>
        <p>Nombre total d'enregistrements : {{ count($presences) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Type</th>
                <th>Site</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Méthode</th>
                <th>Statut</th>
                <th>Validateur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presences as $presence)
                <tr>
                    <td>{{ $presence['Employé'] }}</td>
                    <td class="type-{{ strtolower($presence['Type']) }}">{{ $presence['Type'] }}</td>
                    <td>{{ $presence['Site'] }}</td>
                    <td>{{ $presence['Date'] }}</td>
                    <td>{{ $presence['Heure'] }}</td>
                    <td>{{ $presence['Méthode'] }}</td>
                    <td>{{ $presence['Statut'] }}</td>
                    <td>{{ $presence['Validateur'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>GENIUS WORK - Système de gestion des présences</p>
        <p>Ce document est confidentiel et ne doit être partagé qu'avec les personnes autorisées.</p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
