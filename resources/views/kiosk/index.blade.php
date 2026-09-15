<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $site->entreprise->nom ?? config('app.name') }} — Pointage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100vw; height: 100vh; overflow: hidden;
            background: #000; font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* === Vidéo plein écran === */
        #video-player {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover; z-index: 1;
        }

        /* === Horloge (coin haut-droit) === */
        #clock-overlay {
            position: fixed; top: 20px; right: 20px; z-index: 10;
            text-align: right; color: rgba(255,255,255,0.85);
            text-shadow: 0 2px 8px rgba(0,0,0,0.6);
            pointer-events: none;
        }
        #clock-time { font-size: 2.5rem; font-weight: 700; letter-spacing: 1px; }
        #clock-date { font-size: 1rem; opacity: 0.8; }

        /* === Logo entreprise (coin haut-gauche) === */
        #brand-overlay {
            position: fixed; top: 20px; left: 20px; z-index: 10;
            color: rgba(255,255,255,0.7);
            text-shadow: 0 2px 8px rgba(0,0,0,0.6);
            pointer-events: none;
        }
        #brand-name { font-size: 1.2rem; font-weight: 600; }
        #brand-sub { font-size: 0.8rem; opacity: 0.6; }

        /* === Indicateur "Scannez votre badge" (bas) === */
        #scan-hint {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
            z-index: 10; color: rgba(255,255,255,0.5);
            font-size: 0.9rem; text-shadow: 0 2px 8px rgba(0,0,0,0.6);
            pointer-events: none;
            animation: pulse-text 3s ease-in-out infinite;
        }
        @keyframes pulse-text {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        /* === Input caché pour la douchette === */
        #scanner-input {
            position: fixed; top: -100px; left: -100px;
            width: 1px; height: 1px; opacity: 0;
            border: none; outline: none; z-index: -1;
        }

        /* === Overlay de pointage (apparait au scan) === */
        #pointage-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; z-index: 100;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(10px);
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
        }
        #pointage-overlay.visible { opacity: 1; pointer-events: auto; }

        #pointage-card {
            text-align: center; color: #fff;
            transform: scale(0.8); opacity: 0;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #pointage-overlay.visible #pointage-card {
            transform: scale(1); opacity: 1;
        }

        /* Avatar */
        #emp-avatar {
            width: 120px; height: 120px; border-radius: 50%;
            margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; font-weight: 700; color: #fff;
            box-shadow: 0 0 40px rgba(59,130,246,0.4);
        }
        #emp-avatar.entree { background: linear-gradient(135deg, #10b981, #059669); }
        #emp-avatar.sortie { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        #emp-avatar.pause  { background: linear-gradient(135deg, #f59e0b, #d97706); }
        #emp-avatar.retour { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        #emp-avatar.error  { background: linear-gradient(135deg, #ef4444, #dc2626); }

        #emp-name { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        #emp-action {
            display: inline-block; padding: 6px 20px; border-radius: 999px;
            font-size: 1.1rem; font-weight: 500; margin-bottom: 12px;
        }
        #emp-action.entree { background: rgba(16,185,129,0.2); color: #6ee7b7; }
        #emp-action.sortie { background: rgba(59,130,246,0.2); color: #93c5fd; }
        #emp-action.pause  { background: rgba(245,158,11,0.2); color: #fcd34d; }
        #emp-action.retour { background: rgba(139,92,246,0.2); color: #c4b5fd; }
        #emp-action.error  { background: rgba(239,68,68,0.2); color: #fca5a5; }

        #emp-time { font-size: 1.2rem; opacity: 0.7; margin-bottom: 16px; }
        #emp-info { font-size: 0.9rem; opacity: 0.5; }

        /* Barre de progression */
        #progress-container {
            width: 300px; height: 4px; border-radius: 999px;
            background: rgba(255,255,255,0.1); margin: 20px auto 0; overflow: hidden;
        }
        #progress-bar {
            height: 100%; width: 0%; border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.1s linear;
        }

        /* === Overlay de connexion perdue === */
        #offline-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; z-index: 200;
            display: none; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.95); color: #fff;
            font-size: 1.5rem; text-align: center;
        }
        #offline-overlay.visible { display: flex; }
        #offline-icon {
            font-size: 3rem; margin-bottom: 20px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Vidéo entreprise en boucle (muted au démarrage pour autoplay, son activé au 1er clic) -->
    <video id="video-player" autoplay loop muted playsinline>
        <source src="{{ asset('videos/video.mp4') }}" type="video/mp4">
    </video>

    <!-- Logo entreprise -->
    <div id="brand-overlay">
        <div id="brand-name">{{ $site->entreprise->nom ?? config('app.name') }}</div>
        <div id="brand-sub">{{ $site->nom }}</div>
    </div>

    <!-- Horloge -->
    <div id="clock-overlay">
        <div id="clock-time">--:--</div>
        <div id="clock-date">--</div>
    </div>

    <!-- Indicateur scan -->
    <div id="scan-hint">Scannez votre badge pour pointer</div>

    <!-- Input caché pour la douchette (simule un clavier) -->
    <input type="text" id="scanner-input" autocomplete="off" autofocus>

    <!-- Overlay de pointage -->
    <div id="pointage-overlay">
        <div id="pointage-card">
            <div id="emp-avatar">?</div>
            <div id="emp-name">—</div>
            <div id="emp-action">—</div>
            <div id="emp-time">—</div>
            <div id="emp-info"></div>
            <div id="progress-container"><div id="progress-bar"></div></div>
        </div>
    </div>

    <!-- Overlay hors-ligne -->
    <div id="offline-overlay">
        <div>
            <div id="offline-icon">⟳</div>
            <div>Reconnexion au serveur...</div>
            <div style="font-size: 0.9rem; opacity: 0.5; margin-top: 10px;">
                Vérifiez votre connexion internet
            </div>
        </div>
    </div>

<script>
const KIOSK_TOKEN = '{{ $token }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
const SCAN_URL = '/kiosk/' + KIOSK_TOKEN + '/scan';
const PRESENCES_URL = '/kiosk/' + KIOSK_TOKEN + '/presences';
const OVERLAY_DURATION = 5000; // 5 secondes d'affichage

// === Éléments DOM ===
const video = document.getElementById('video-player');
const scannerInput = document.getElementById('scanner-input');
const overlay = document.getElementById('pointage-overlay');
const offlineOverlay = document.getElementById('offline-overlay');
const progressBar = document.getElementById('progress-bar');

// === État ===
let scanning = false;
let scanBuffer = '';
let lastKeyTime = Date.now();
const SCAN_TIMEOUT = 50; // ms — détection douchette (frappe rapide)
let overlayTimer = null;
let progressTimer = null;
let isOnline = navigator.onLine;

// === Horloge ===
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const days = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    const months = ['jan','fév','mar','avr','mai','jun','jui','aoû','sep','oct','nov','déc'];
    document.getElementById('clock-time').textContent = h + ':' + m;
    document.getElementById('clock-date').textContent =
        days[now.getDay()] + ' ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
}
updateClock();
setInterval(updateClock, 1000);

// === Gestion de la douchette ===
// La douchette simule un clavier : frappe très rapide + Entrée
// On garde le focus sur un input caché en permanence

function refocusInput() {
    scannerInput.focus();
}

// Re-focus immédiatement si on perd le focus
document.addEventListener('focusout', refocusInput);
window.addEventListener('click', refocusInput);

scannerInput.addEventListener('input', function(e) {
    const now = Date.now();
    if (now - lastKeyTime > SCAN_TIMEOUT) {
        scanBuffer = '';
    }
    lastKeyTime = now;
    scanBuffer += e.target.value;
    e.target.value = '';
});

scannerInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = scanBuffer.trim();
        scanBuffer = '';
        if (code && !scanning) {
            handleScan(code);
        }
    }
});

// === Traitement du scan ===
function handleScan(rawCode) {
    scanning = true;

    // Extraire le code utile : si c'est une URL, prendre le dernier segment
    let code = rawCode.trim();
    if (code.includes('/')) {
        const parts = code.split('/');
        code = parts[parts.length - 1];
    }
    // Retirer les éventuels paramètres GET
    if (code.includes('?')) {
        code = code.split('?')[0];
    }
    code = code.trim();

    console.log('Scan brut:', rawCode.substring(0, 30), '→ extrait:', code.substring(0, 15) + '...');

    // Mettre la vidéo en pause
    video.pause();

    // Afficher l'overlay en mode "chargement"
    showOverlay({
        type: 'loading',
        name: 'Vérification...',
        action: 'Traitement en cours',
        time: '',
        info: '',
    });

    fetch(SCAN_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ idno: code }),
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur serveur: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.status === 'success' && data.redirect) {
            // Rediriger vers la page de transition (assistant vocal + animation)
            window.location.href = data.redirect;
        } else {
            // Erreur : afficher l'overlay inline pendant 5s puis reprendre
            showOverlay({
                type: 'error',
                name: 'Erreur',
                action: data.message || 'Badge non reconnu',
                time: new Date().toLocaleTimeString('fr-FR'),
                info: '',
            });
            speak(data.message || 'Erreur, badge non reconnu');
            startOverlayTimer();
        }
    })
    .catch(err => {
        console.error('Erreur scan:', err);
        showOverlay({
            type: 'error',
            name: 'Erreur',
            action: 'Problème de connexion',
            time: new Date().toLocaleTimeString('fr-FR'),
            info: err.message,
        });
        speak('Problème de connexion');
        startOverlayTimer();
    });
}

// === Affichage de l'overlay ===
function showOverlay(data) {
    const avatar = document.getElementById('emp-avatar');
    const nameEl = document.getElementById('emp-name');
    const actionEl = document.getElementById('emp-action');
    const timeEl = document.getElementById('emp-time');
    const infoEl = document.getElementById('emp-info');

    // Classes
    avatar.className = '';
    actionEl.className = '';

    if (data.type === 'entree') {
        avatar.classList.add('entree');
        actionEl.classList.add('entree');
        avatar.textContent = (data.name[0] || '?').toUpperCase();
    } else if (data.type === 'sortie') {
        avatar.classList.add('sortie');
        actionEl.classList.add('sortie');
        avatar.textContent = (data.name[0] || '?').toUpperCase();
    } else if (data.type === 'pause_debut') {
        avatar.classList.add('pause');
        actionEl.classList.add('pause');
        avatar.textContent = (data.name[0] || '?').toUpperCase();
    } else if (data.type === 'pause_fin') {
        avatar.classList.add('retour');
        actionEl.classList.add('retour');
        avatar.textContent = (data.name[0] || '?').toUpperCase();
    } else if (data.type === 'error') {
        avatar.classList.add('error');
        actionEl.classList.add('error');
        avatar.textContent = '✕';
    } else {
        // loading
        avatar.textContent = '⟳';
    }

    nameEl.textContent = data.name;
    actionEl.textContent = data.action;
    timeEl.textContent = data.time;
    infoEl.textContent = data.info;

    overlay.classList.add('visible');
}

function hideOverlay() {
    overlay.classList.remove('visible');
    clearInterval(progressTimer);
    clearTimeout(overlayTimer);

    // Reprendre la vidéo
    video.play().catch(() => {});

    // Réinitialiser
    scanning = false;
    scanBuffer = '';
    refocusInput();
}

function startOverlayTimer() {
    let elapsed = 0;
    progressBar.style.width = '0%';

    progressTimer = setInterval(() => {
        elapsed += 50;
        progressBar.style.width = (elapsed / OVERLAY_DURATION * 100) + '%';
    }, 50);

    overlayTimer = setTimeout(hideOverlay, OVERLAY_DURATION);
}

function getActionLabel(type) {
    const labels = {
        'entree': '✓ Entrée enregistrée',
        'sortie': '✓ Sortie enregistrée',
        'pause_debut': '⏸ Pause débutée',
        'pause_fin': '▶ Retour de pause',
    };
    return labels[type] || type;
}

// === Synthèse vocale (Web Speech API) ===
function speak(text) {
    if (!text || !('speechSynthesis' in window)) return;

    // Annuler les messages en cours
    window.speechSynthesis.cancel();

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'fr-FR';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;
    utterance.volume = 1.0;

    window.speechSynthesis.speak(utterance);
}

// === Détection hors-ligne ===
window.addEventListener('online', () => {
    isOnline = true;
    offlineOverlay.classList.remove('visible');
    video.play().catch(() => {});
    refocusInput();
});

window.addEventListener('offline', () => {
    isOnline = false;
    offlineOverlay.classList.add('visible');
    video.pause();
});

// === Ping régulier pour vérifier la connexion serveur ===
setInterval(() => {
    if (!navigator.onLine) return;
    fetch(PRESENCES_URL, {
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(5000),
    })
    .then(() => {
        if (!isOnline) {
            isOnline = true;
            offlineOverlay.classList.remove('visible');
            video.play().catch(() => {});
            refocusInput();
        }
    })
    .catch(() => {
        if (isOnline) {
            isOnline = false;
            offlineOverlay.classList.add('visible');
        }
    });
}, 30000); // Toutes les 30 secondes

// === Empêcher les raccourcis clavier (mode kiosque) ===
document.addEventListener('keydown', function(e) {
    // Bloquer F11, F5, Ctrl+R, Ctrl+W, Alt+F4, etc.
    if (e.key === 'F5' || e.key === 'F11' ||
        (e.ctrlKey && (e.key === 'r' || e.key === 'w' || e.key === 't' || e.key === 'n')) ||
        (e.altKey && e.key === 'F4')) {
        e.preventDefault();
    }
});

// === Démarrage ===
refocusInput();

// Activer le son de la vidéo au premier clic ou keypress (les navigateurs bloquent l'autoplay avec son)
let soundActivated = false;
function activateSound() {
    if (soundActivated) return;
    soundActivated = true;
    video.muted = false;
    video.volume = 0.8;
    video.play().catch(() => {
        // Si l'unmute échoue (policy), remettre muted
        video.muted = true;
        soundActivated = false;
    });
    // Plein écran au passage
    if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen().catch(() => {});
    }
}
document.addEventListener('click', activateSound);
document.addEventListener('keydown', function once() {
    activateSound();
}, { once: true });

// Re-focus toutes les 2 secondes (sécurité)
setInterval(refocusInput, 2000);
</script>
</body>
</html>
