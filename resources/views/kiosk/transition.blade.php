<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointage — {{ $site->entreprise->nom ?? config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100vw; height: 100vh; overflow: hidden;
            background: #000; font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        /* Particules flottantes */
        .particle {
            position: fixed; border-radius: 50%;
            background: rgba(59,130,246,0.15);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Carte de transition */
        #card {
            text-align: center; color: #fff;
            max-width: 600px; padding: 40px;
            transform: scale(0.8); opacity: 0;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #card.visible { transform: scale(1); opacity: 1; }

        /* Avatar */
        #avatar {
            width: 140px; height: 140px; border-radius: 50%;
            margin: 0 auto 24px;
            display: flex; align-items: center; justify-content: center;
            font-size: 3.5rem; font-weight: 700; color: #fff;
            position: relative;
            box-shadow: 0 0 60px rgba(59,130,246,0.4);
        }
        #avatar.clockin { background: linear-gradient(135deg, #10b981, #059669); }
        #avatar.clockout { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        #avatar.pause { background: linear-gradient(135deg, #f59e0b, #d97706); }
        #avatar.return_clockin { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        #avatar.error { background: linear-gradient(135deg, #ef4444, #dc2626); }

        /* Anneau animé */
        #ring {
            position: absolute; inset: -8px; border-radius: 50%;
            border: 4px solid rgba(59,130,246,0.3);
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        #name {
            font-size: 2.2rem; font-weight: 700; margin-bottom: 12px;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        #action {
            display: inline-block; padding: 8px 24px; border-radius: 999px;
            font-size: 1.2rem; font-weight: 500; margin-bottom: 16px;
        }
        #action.clockin { background: rgba(16,185,129,0.2); color: #6ee7b7; }
        #action.clockout { background: rgba(59,130,246,0.2); color: #93c5fd; }
        #action.pause { background: rgba(245,158,11,0.2); color: #fcd34d; }
        #action.return_clockin { background: rgba(139,92,246,0.2); color: #c4b5fd; }
        #action.error { background: rgba(239,68,68,0.2); color: #fca5a5; }

        #time { font-size: 1.1rem; opacity: 0.6; margin-bottom: 20px; }

        #info {
            font-size: 0.95rem; opacity: 0.5; margin-bottom: 24px;
            padding: 8px 16px; background: rgba(59,130,246,0.1);
            border-radius: 8px; display: inline-block;
        }

        /* Barre de progression */
        #progress-container {
            width: 300px; height: 4px; border-radius: 999px;
            background: rgba(255,255,255,0.1); margin: 24px auto 0; overflow: hidden;
        }
        #progress-bar {
            height: 100%; width: 0%; border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.1s linear;
        }

        #status { font-size: 0.9rem; opacity: 0.4; margin-top: 12px; }

        /* Checkmark animation */
        #check {
            display: none; margin-top: 20px;
            align-items: center; justify-content: center;
            color: #10b981; font-size: 1.1rem;
        }
        #check.visible { display: flex; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

    <!-- Particules -->
    @for ($i = 0; $i < 20; $i++)
    <div class="particle" style="
        width: {{ rand(4,12) }}px; height: {{ rand(4,12) }}px;
        left: {{ rand(0,100) }}%; top: {{ rand(0,100) }}%;
        animation-delay: -{{ rand(0,5000) }}ms;
    "></div>
    @endfor

    <!-- Carte -->
    <div id="card">
        <div id="avatar {{ $type }}">
            <span>{{ strtoupper($name[0] ?? '?') }}</span>
            <div id="ring"></div>
        </div>
        <div id="name">{{ $name }}</div>
        <div id="action {{ $type }}">
            @if($type === 'clockin')
                ✓ Entrée enregistrée
            @elseif($type === 'clockout')
                ✓ Sortie enregistrée
            @elseif($type === 'pause')
                ⏸ Pause débutée
            @elseif($type === 'return_clockin')
                ▶ Retour de pause
            @else
                Traitement en cours
            @endif
        </div>
        <div id="time">{{ date('d/m/Y') }} à {{ date('H:i') }}</div>
        @if(!empty($info_supplementaire))
        <div id="info">{{ $info_supplementaire }}</div>
        @endif
        <div id="progress-container"><div id="progress-bar"></div></div>
        <div id="status">Vérification terminée</div>
        <div id="check">✓ Enregistré avec succès</div>
    </div>

    @if($message)
    <div id="voice-message" data-text="{{ $message }}" class="hidden"></div>
    @endif

<script>
const KIOSK_URL = '/kiosk/{{ $token }}';
const DURATION = 5000; // 5 secondes

// === Animation d'entrée ===
setTimeout(() => {
    document.getElementById('card').classList.add('visible');
}, 100);

// === Message vocal (Web Speech API) ===
const voiceMessage = document.getElementById('voice-message');
if (voiceMessage) {
    const text = voiceMessage.getAttribute('data-text');
    if (text && 'speechSynthesis' in window) {
        // Petit délai pour laisser l'animation démarrer
        setTimeout(() => {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR';
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            utterance.volume = 1.0;
            window.speechSynthesis.speak(utterance);
        }, 300);
    }
}

// === Barre de progression ===
let elapsed = 0;
const progressBar = document.getElementById('progress-bar');
const statusEl = document.getElementById('status');
const checkEl = document.getElementById('check');

const progressInterval = setInterval(() => {
    elapsed += 50;
    const pct = Math.min((elapsed / DURATION) * 100, 100);
    progressBar.style.width = pct + '%';

    if (elapsed >= DURATION) {
        clearInterval(progressInterval);
        statusEl.textContent = 'Redirection...';
        checkEl.classList.add('visible');

        // Rediriger vers le kiosk après 1s
        setTimeout(() => {
            window.location.href = KIOSK_URL;
        }, 1000);
    }
}, 50);

// === Son de validation (optionnel) ===
try {
    const audio = new Audio('/assets/sounds/notification.mp3');
    audio.volume = 0.5;
    audio.play().catch(() => {});
} catch(e) {}
</script>
</body>
</html>
