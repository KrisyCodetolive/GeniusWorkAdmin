# Déploiement Kiosque — Genius Work Pointage TV

## Architecture

```
┌──────────────────────────┐         ┌──────────────────────────┐
│  MINI PC (Linux)         │         │  VPS (serveur)            │
│  Chrome kiosk mode       │ HTTPS   │  Laravel + MySQL + Nginx  │
│  → https://domaine/kiosk  │ ──────► │  /kiosk/{token}           │
│  ← vidéo + voice feedback │ ◄────── │  POST /kiosk/{token}/scan │
└──────────────────────────┘         └──────────────────────────┘
       │ douchette USB                      │
       └── simule clavier                   │
```

---

## 1. Déploiement sur le VPS

### Prérequis serveur
- Ubuntu 22.04+ ou Debian 12+
- PHP 8.2+
- MySQL 8.0+ ou MariaDB
- Nginx
- Composer
- Node.js 20+ (pour build les assets)
- Certbot (pour SSL Let's Encrypt)

### Étapes

```bash
# 1. Installer les dépendances
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-bcmath php8.2-intl unzip git composer certbot python3-certbot-nginx

# 2. Cloner le projet
cd /var/www
sudo git clone <votre-repo-git> geniuswork
cd geniuswork

# 3. Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# 4. Configurer l'environnement
cp .env.example .env
# Éditer .env :
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_URL=https://votre-domaine.com
#   DB_DATABASE=genius_work
#   DB_USERNAME=genius
#   DB_PASSWORD=votre-mot-de-passe

php artisan key:generate

# 5. Migrations et seeders
php artisan migrate --force
php artisan db:seed --force

# 6. Build des assets frontend
npm install
npm run build

# 7. Permissions
sudo chown -R www-data:www-data /var/www/geniuswork
sudo chmod -R 775 storage bootstrap/cache

# 8. Configurer Nginx
sudo nano /etc/nginx/sites-available/geniuswork
```

### Config Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/geniuswork/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/geniuswork /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 9. SSL Let's Encrypt
sudo certbot --nginx -d votre-domaine.com

# 10. Optimisations production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 11. Queue worker (pour notifications, etc.)
# Créer un service systemd pour le queue worker
sudo nano /etc/systemd/system/geniuswork-queue.service
```

```ini
[Unit]
Description=Genius Work Queue Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/geniuswork
ExecStart=/usr/bin/php artisan queue:work --tries=3 --max-time=60
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable geniuswork-queue
sudo systemctl start geniuswork-queue
```

### Déposer la vidéo entreprise

```bash
# Placer la vidéo MP4 dans public/videos/video.mp4
# Format recommandé : 1920x1080, H.264, ~50Mbps
scp votre-video.mp4 user@vps:/var/www/geniuswork/public/videos/video.mp4
sudo chown www-data:www-data /var/www/geniuswork/public/videos/video.mp4
```

### Récupérer l'URL Kiosque

```bash
# Dans l'admin Filament → Sites → bouton "Mode Kiosque" (ouvre l'URL)
# Ou via tinker :
php artisan tinker --execute="echo url('/kiosk/' . \App\Models\Site::first()->kiosk_token);"
```

---

## 2. Configuration du Mini PC (Linux)

### Installation du système

```bash
# Installer Ubuntu 22.04 LTS Desktop (minimal)
# Au démarrage : auto-login activé
```

### Chrome en mode kiosque

```bash
# Installer Google Chrome
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt update && sudo apt install -y google-chrome-stable unclutter

# unclutter = cacher le curseur de la souris quand inactif
```

### Script de démarrage kiosque

```bash
# Créer le script
sudo nano /opt/kiosk.sh
```

```bash
#!/bin/bash
# Script de démarrage du mode kiosque Genius Work

KIOSK_URL="https://votre-domaine.com/kiosk/VOTRE_TOKEN_ICI"

# Cacher le curseur après 3s d'inactivité
unclutter -idle 3 &

# Lancer Chrome en mode kiosque
google-chrome-stable \
    --kiosk \
    --no-first-run \
    --no-default-browser-check \
    --disable-translate \
    --disable-features=TranslateUI \
    --disable-save-password-bubble \
    --disable-session-crashed-bubble \
    --noerrdialogs \
    --autoplay-policy=no-user-gesture-required \
    --start-fullscreen \
    --window-position=0,0 \
    --window-size=1920,1080 \
    "$KIOSK_URL"

# Relancer en cas de crash
while true; do
    sleep 5
    google-chrome-stable --kiosk "$KIOSK_URL"
done
```

```bash
sudo chmod +x /opt/kiosk.sh
```

### Démarrage automatique au boot

```bash
# Méthode 1 : via autostart (Ubuntu Desktop)
mkdir -p ~/.config/autostart
nano ~/.config/autostart/geniuswork-kiosk.desktop
```

```ini
[Desktop Entry]
Type=Application
Name=Genius Work Kiosk
Exec=/opt/kiosk.sh
Terminal=false
X-GNOME-Autostart-enabled=true
```

```bash
# Méthode 2 : via systemd (plus robuste)
sudo nano /etc/systemd/system/kiosk.service
```

```ini
[Unit]
Description=Genius Work Kiosk Mode
After=graphical.target

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
```

### Configuration supplémentaire mini PC

```bash
# Désactiver l'économiseur d'écran
gsettings set org.gnome.desktop.screensaver lock-enabled false
gsettings set org.gnome.desktop.session idle-delay 0

# Désactiver la mise en veille
sudo systemctl mask sleep.target suspend.target hibernate.target hybrid-sleep.target

# Désactiver le gestionnaire de mise à jour automatique
sudo apt remove -y update-notifier

# Auto-login au démarrage (si pas déjà fait)
sudo nano /etc/gdm3/custom.conf
# [daemon]
# AutomaticLoginEnable=true
# AutomaticLogin=votre-utilisateur
```

### Vérifier la douchette

```bash
# La douchette USB simule un clavier HID
# Vérifier qu'elle est détectée
lsusb

# Tester : ouvrir un terminal, scanner un QR code
# Le code doit apparaître suivi d'un Entrée

# Si la douchette ajoute un préfixe/suffixe, configurer via :
# - le manuel de la douchette (souvent un code-barre de config)
# - ou le logiciel fourni avec la douchette
```

---

## 3. Test final

1. Allumer le mini PC → Chrome s'ouvre en kiosque sur l'URL
2. La vidéo entreprise se lance en plein écran, en boucle
3. L'horloge s'affiche en haut à droite
4. Scanner un badge QR avec la douchette
5. La vidéo se met en pause
6. L'overlay affiche : nom, type (entrée/sortie), heure
7. Le message vocal est lu ("Bonjour Jean, bonne journée !")
8. Après 5 secondes, l'overlay disparaît et la vidéo reprend
9. Vérifier dans l'admin → Présences que le pointage est enregistré

---

## 4. Maintenance

### Changer la vidéo
```bash
# Remplacer le fichier sur le VPS
scp nouvelle-video.mp4 user@vps:/var/www/geniuswork/public/videos/video.mp4
# Le kiosque recharge automatiquement la page toutes les 30s si connexion perdue
# Pour forcer le rechargement : redémarrer le mini PC ou Ctrl+R (si clavier branché)
```

### Régénérer un token kiosque
```bash
php artisan tinker --execute="
\$site = \App\Models\Site::find('ID_DU_SITE');
\$site->kiosk_token = \Illuminate\Support\Str::random(48);
\$site->save();
echo url('/kiosk/' . \$site->kiosk_token);
"
# Mettre à jour l'URL dans /opt/kiosk.sh sur le mini PC
```

### Logs
```bash
# Logs Laravel
tail -f /var/www/geniuswork/storage/logs/laravel.log | grep -i "Kiosk"

# Logs Nginx
sudo tail -f /var/log/nginx/access.log
```
