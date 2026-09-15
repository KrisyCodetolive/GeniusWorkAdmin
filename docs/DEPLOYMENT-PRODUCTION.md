# Déploiement Production — Guide Complet

## 1. Déploiement sur le VPS avec Docker

### 1.1 Préparation du VPS

```bash
# Installer Docker + Docker Compose
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
# Se déconnecter/reconnecter pour prendre effet

# Cloner le projet
git clone <votre-repo-git> geniuswork
cd geniuswork
```

### 1.2 Configuration

```bash
# Copier et éditer l'environnement production
cp .env.production.example .env
nano .env
# Modifier :
#   APP_URL=https://votre-domaine.com
#   DB_PASSWORD=un-vrai-mot-de-passe
#   APP_KEY= (sera générée ci-dessous)

# Générer la clé APP_KEY
docker compose -f docker-compose.production.yml run --rm app php artisan key:generate
```

### 1.3 Activer le SSL automatique (Caddy)

Éditer le `Caddyfile` et remplacer `votre-domaine.com` par votre vrai domaine.

Décommenter la section `caddy` dans `docker-compose.production.yml`.

### 1.4 Démarrer

```bash
# Build + start
docker compose -f docker-compose.production.yml up -d --build

# Voir les logs
docker compose -f docker-compose.production.yml logs -f

# Vérifier que tout tourne
docker compose -f docker-compose.production.yml ps
```

### 1.5 Placer la vidéo entreprise

```bash
# Placer la vidéo dans public/videos/video.mp4
scp votre-video.mp4 user@vps:~/geniuswork/public/videos/video.mp4
```

### 1.6 Récupérer l'URL Kiosque

```bash
docker compose -f docker-compose.production.yml exec app php artisan tinker --execute="
echo url('/kiosk/' . \App\Models\Site::first()->kiosk_token);
"
# Résultat : https://votre-domaine.com/kiosk/Uiqmr1fvY7iU4iBfyOZ0VN2kXc384xrgSKf5rq3QZZOyq1nI
```

### 1.7 Commandes utiles

```bash
# Redémarrer
docker compose -f docker-compose.production.yml restart

# Mettre à jour après un git pull
docker compose -f docker-compose.production.yml up -d --build

# Logs de l'app
docker compose -f docker-compose.production.yml exec app tail -f storage/logs/laravel.log

# Migrations manuelles
docker compose -f docker-compose.production.yml exec app php artisan migrate --force

# Vider le cache
docker compose -f docker-compose.production.yml exec app php artisan optimize:clear
```

---

## 2. Bypass de la restriction son navigateur

### Le problème

Les navigateurs (Chrome, Firefox, Edge) **interdisent l'autoplay avec son** sans interaction utilisateur. C'est une policy W3C — impossible à contourner en JavaScript pur.

### Solution 1 : Chrome Kiosk flags (RECOMMANDÉE — pour le mini PC)

Lancer Chrome avec le flag `--autoplay-policy=no-user-gesture-required` :

```bash
google-chrome-stable \
    --kiosk \
    --autoplay-policy=no-user-gesture-required \
    --no-first-run \
    --no-default-browser-check \
    --start-fullscreen \
    "https://votre-domaine.com/kiosk/TOKEN"
```

Ce flag **désactive complètement** la restriction d'autoplay. La vidéo démarre avec le son immédiatement, sans clic.

### Solution 2 : Chrome policies (permanent)

Créer une policy Chrome qui s'applique à chaque démarrage :

```bash
sudo mkdir -p /etc/opt/chrome/policies/managed
sudo nano /etc/opt/chrome/policies/managed/kiosk.json
```

```json
{
    "AutoplayAllowed": true,
    "AutoplayPolicyWhitelist": ["votre-domaine.com"],
    "DefaultAutoplayPolicy": "no-user-gesture-required"
}
```

Redémarrer Chrome. La vidéo démarre avec le son automatiquement sur `votre-domaine.com`.

### Solution 3 : Firefox (alternative)

```bash
# Dans about:config, modifier :
media.autoplay.default = 0
media.autoplay.allow-muted = true
media.autoplay.enabled = true
```

Ou via `user.js` dans le profil Firefox :

```bash
# Trouver le profil
ls ~/.mozilla/firefox/*.default-release/

# Créer user.js
echo 'user_pref("media.autoplay.default", 0);' >> ~/.mozilla/firefox/*.default-release/user.js
echo 'user_pref("media.autoplay.enabled", true);' >> ~/.mozilla/firefox/*.default-release/user.js
```

### Solution 4 : Chromium kiosk (plus léger que Chrome)

```bash
sudo apt install -y chromium-browser

chromium-browser \
    --kiosk \
    --autoplay-policy=no-user-gesture-required \
    --no-first-run \
    --disable-features=TranslateUI \
    "https://votre-domaine.com/kiosk/TOKEN"
```

### Recommandation finale

Pour le mini PC en production, utiliser **Chrome avec le flag `--autoplay-policy=no-user-gesture-required`**. C'est la solution la plus fiable. La vidéo aura le son dès le démarrage, sans aucune interaction.

---

## 3. Flux mini PC → serveur déployé

### Architecture réseau

```
┌─────────────────────────────────────────────────┐
│  MINI PC (au bureau, connecté à la TV)            │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Chrome Kiosk Mode                         │  │
│  │  URL: https://votre-domaine.com/kiosk/TOKEN│  │
│  │                                            │  │
│  │  ┌──────────────────────────────────────┐  │  │
│  │  │  Vidéo entreprise (plein écran)       │  │  │
│  │  │  Son activé via flag Chrome           │  │  │
│  │  └──────────────────────────────────────┘  │  │
│  │                                            │  │
│  │  Douchette USB → simule clavier → input    │  │
│  │  caché → POST /kiosk/TOKEN/scan            │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  Internet (WiFi ou Ethernet)                     │
└─────────────────────────────────────────────────┘
                    │
                    │ HTTPS
                    ▼
┌─────────────────────────────────────────────────┐
│  VPS (cloud — DigitalOcean, Hetzner, etc.)       │
│                                                  │
│  ┌────────────────────────────────────────────┐  │
│  │  Docker Compose                            │  │
│  │  ├── app (Laravel + Nginx + PHP-FPM)       │  │
│  │  ├── db (MySQL 8.0)                        │  │
│  │  └── caddy (SSL automatique)               │  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  Domaine: https://votre-domaine.com              │
└─────────────────────────────────────────────────┘
```

### Étape 1 : Déployer sur le VPS (une fois)

```bash
# Sur le VPS
git clone <votre-repo-git> geniuswork
cd geniuswork
cp .env.production.example .env
# Éditer .env avec votre domaine + mots de passe
docker compose -f docker-compose.production.yml up -d --build
```

### Étape 2 : Configurer le DNS

Cheir votre registrar (Nom de domaine) :
- Créer un enregistrement A : `votre-domaine.com → IP_DU_VPS`
- Attendre la propagation DNS (max 24h, souvent < 30min)

### Étape 3 : Configurer le mini PC (une fois)

```bash
# Sur le mini PC (Linux)

# 1. Installer Chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt update && sudo apt install -y google-chrome-stable unclutter

# 2. Créer le script kiosk
sudo nano /opt/kiosk.sh
```

```bash
#!/bin/bash
# === Configuration ===
KIOSK_URL="https://votre-domaine.com/kiosk/VOTRE_TOKEN_ICI"

# Cacher le curseur après 3s d'inactivité
unclutter -idle 3 &

# Chrome kiosk avec son automatique
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
    "$KIOSK_URL"

# Relancer en cas de crash
while true; do
    sleep 5
    google-chrome-stable --kiosk --autoplay-policy=no-user-gesture-required "$KIOSK_URL"
done
```

```bash
sudo chmod +x /opt/kiosk.sh

# 3. Démarrage automatique au boot
sudo nano /etc/systemd/system/kiosk.service
```

```ini
[Unit]
Description=Genius Work Kiosk
After=graphical.target
Wants=network-online.target

[Service]
Type=simple
User=votre-utilisateur
Environment=DISPLAY=:0
ExecStart=/opt/kiosk.sh
Restart=always
RestartSec=10

[Install]
WantedBy=graphical.target
```

```bash
sudo systemctl enable kiosk.service
sudo systemctl start kiosk.service

# 4. Désactiver l'économiseur d'écran et la mise en veille
gsettings set org.gnome.desktop.screensaver lock-enabled false
gsettings set org.gnome.desktop.session idle-delay 0
sudo systemctl mask sleep.target suspend.target hibernate.target hybrid-sleep.target

# 5. Auto-login au démarrage
sudo nano /etc/gdm3/custom.conf
# [daemon]
# AutomaticLoginEnable=true
# AutomaticLogin=votre-utilisateur
```

### Étape 4 : Brancher la douchette

```bash
# La douchette USB est détectée comme clavier HID automatiquement
# Vérifier
lsusb

# Tester : scanner un QR code dans un terminal
# Le code doit apparaître suivi d'un Entrée
```

### Étape 5 : Test final

1. Allumer le mini PC → Chrome s'ouvre en kiosk sur l'URL
2. La vidéo entreprise démarre en plein écran **avec le son**
3. L'horloge s'affiche en haut à droite
4. Scanner un badge QR avec la douchette
5. La vidéo se met en pause
6. Page de transition : nom, type, heure, **message vocal**
7. Après 5 secondes → retour à la vidéo
8. Vérifier dans l'admin → Présences que le pointage est enregistré

---

## 4. Sécurité

### Protéger l'URL kiosk

L'URL kiosk contient un token de 48 caractères. Pour plus de sécurité :

```bash
# Régénérer le token
docker compose -f docker-compose.production.yml exec app php artisan tinker --execute="
\$site = \App\Models\Site::find('ID_DU_SITE');
\$site->kiosk_token = \Illuminate\Support\Str::random(48);
\$site->save();
echo url('/kiosk/' . \$site->kiosk_token);
"
# Mettre à jour /opt/kiosk.sh sur le mini PC
```

### Restreindre l'accès admin

Dans `.env`, configurer :
```
SANCTUM_STATEFUL_DOMAINS=votre-domaine.com
SESSION_DOMAIN=votre-domaine.com
```

### Firewall VPS

```bash
# Autoriser uniquement HTTP/HTTPS + SSH
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## 5. Maintenance

### Changer la vidéo

```bash
scp nouvelle-video.mp4 user@vps:~/geniuswork/public/videos/video.mp4
# Le kiosk recharge automatiquement la page si connexion perdue
# Pour forcer : redémarrer le mini PC
```

### Mettre à jour l'application

```bash
# Sur le VPS
cd geniuswork
git pull
docker compose -f docker-compose.production.yml up -d --build
```

### Voir les logs de pointage

```bash
docker compose -f docker-compose.production.yml exec app tail -f storage/logs/laravel.log | grep -i kiosk
```

### Sauvegarde base de données

```bash
# Sauvegarde manuelle
docker compose -f docker-compose.production.yml exec db mysqldump -u root -p${DB_ROOT_PASSWORD} genius_work > backup.sql

# Sauvegarde automatique (cron)
# 0 2 * * * docker compose -f /path/to/docker-compose.production.yml exec -T db mysqldump -u root -prootsecret genius_work > /backups/geniuswork_$(date +\%Y\%m\%d).sql
```
