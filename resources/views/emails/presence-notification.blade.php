<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4a6cf7;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header.retard {
            background-color: #f7a74a; /* Orange pour les retards */
        }
        .header.absence {
            background-color: #f74a4a; /* Rouge pour les absences */
        }
        .header.sortie_manquante {
            background-color: #f7d54a; /* Jaune pour les sorties manquantes */
        }
        .content {
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4a6cf7;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 20px;
        }
        .info {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #4a6cf7;
            margin: 20px 0;
        }
        .info.retard {
            border-left-color: #f7a74a;
        }
        .info.absence {
            border-left-color: #f74a4a;
        }
        .info.sortie_manquante {
            border-left-color: #f7d54a;
        }
        .employee-info {
            background-color: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .date-info {
            font-weight: bold;
            color: #555;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $type }}">
            <h1>Notification de {{ ucfirst(str_replace('_', ' ', $type)) }}</h1>
        </div>
        <div class="content">
            <p>Bonjour {{ $employeur->prenom }} {{ $employeur->nom }},</p>
            
            <div class="date-info">
                Date : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </div>
            
            <div class="message">
                {!! nl2br(e($message)) !!}
            </div>
            
            <div class="employee-info">
                <h3>Informations de l'employé</h3>
                <ul>
                    <li><strong>Nom complet :</strong> {{ $employeur->nom_complet }}</li>
                    <li><strong>Email :</strong> {{ $employeur->email }}</li>
                    <li><strong>Entreprise :</strong> {{ $entreprise->nom }}</li>
                    @if(isset($employeur->departement) && $employeur->departement)
                        <li><strong>Département :</strong> {{ $employeur->departement->nom }}</li>
                    @endif
                </ul>
            </div>
            
            @if(isset($data) && count($data) > 0)
                <div class="info {{ $type }}">
                    <h3>Détails de la notification</h3>
                    <ul>
                        @foreach($data as $key => $value)
                            @if(!is_array($value) && !is_object($value) && $key !== 'url')
                                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>: {{ $value }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(isset($data['url']))
                <p>
                    <a href="{{ $data['url'] }}" class="button">Accéder à votre espace</a>
                </p>
            @else
                <p>
                    <a href="{{ config('app.url') }}/login" class="button">Accéder à votre espace</a>
                </p>
            @endif
            
            <p>Cordialement,</p>
            <p>L'équipe GENIUS WORK</p>
        </div>
        <div class="footer">
            <p>Ce message a été envoyé automatiquement par le système GENIUS WORK.</p>
            <p>© {{ date('Y') }} GENIUS WORK. Tous droits réservés.</p>
            <p>
                <small>Si vous avez reçu ce message par erreur, merci de nous en informer et de le supprimer.</small>
            </p>
        </div>
    </div>
</body>
</html>
