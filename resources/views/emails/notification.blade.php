<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sujet }}</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $sujet }}</h1>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            
            <div class="message">
                {!! nl2br(e($message)) !!}
            </div>
            
            @if(isset($data) && count($data) > 0)
                <div class="info">
                    <h3>Informations complémentaires</h3>
                    <ul>
                        @foreach($data as $key => $value)
                            @if(!is_array($value) && !is_object($value))
                                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>: {{ $value }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(isset($data['url']))
                <p>
                    <a href="{{ $data['url'] }}" class="button">Voir les détails</a>
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
