#!/bin/bash
# ===========================================
# Script de démarrage Kiosk — Genius Work
# À placer sur le mini PC dans /opt/kiosk.sh
# ===========================================

# === CONFIGURATION ===
# Remplacer par l'URL kiosk de votre site (voir admin → Sites → Mode Kiosque)
KIOSK_URL="https://votre-domaine.com/kiosk/VOTRE_TOKEN_ICI"

# === Démarrage ===

# Cacher le curseur de la souris après 3s d'inactivité
unclutter -idle 3 &

# Désactiver l'économiseur d'écran
xset s off
xset s noblank
xset -dpms

# Lancer Chrome en mode kiosque avec son automatique
google-chrome-stable \
    --kiosk \
    --autoplay-policy=no-user-gesture-required \
    --no-first-run \
    --no-default-browser-check \
    --disable-translate \
    --disable-features=TranslateUI \
    --disable-save-password-bubble \
    --disable-session-crashed-bubble \
    --noerrdialogs \
    --start-fullscreen \
    --window-position=0,0 \
    --window-size=1920,1080 \
    --user-data-dir=/tmp/chrome-kiosk \
    "$KIOSK_URL"

# Boucle de relance en cas de crash
while true; do
    echo "[$(date)] Chrome s'est arrêté, redémarrage dans 5s..."
    sleep 5
    google-chrome-stable \
        --kiosk \
        --autoplay-policy=no-user-gesture-required \
        --no-first-run \
        "$KIOSK_URL"
done
