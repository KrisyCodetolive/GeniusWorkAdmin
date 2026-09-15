# Déploiement sur Coolify — Genius Work

## Architecture avec Coolify

```
┌─────────────────────────────────────────────────┐
│  Coolify (sur votre VPS)                          │
│                                                   │
│  ┌─────────────────────────────────────────────┐  │
│  │  Reverse proxy intégré (Traefik)            │  │
│  │  SSL automatique (Let's Encrypt)             │  │
│  │  Domaine: https://votre-domaine.com          │  │
│  └─────────────────────────────────────────────┘  │
│                    │                              │
│  ┌─────────────────┴──────────────────────────┐   │
│  │  Docker Compose (docker-compose.coolify.yml)│   │
│  │  ├── app (Laravel + Nginx + PHP-FPM)        │   │
│  │  └── db (MySQL 8.0)                         │   │
│  └─────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
         ▲
         │ HTTPS
┌─────────────────────────────────────────────────┐
│  Mini PC (bureau) → Chrome kiosk → TV            │
└─────────────────────────────────────────────────┘
```

---

## 1. Préparer le repo Git

Assurez-vous que ces fichiers sont commités dans votre repo :

```
├── Dockerfile
├── docker-compose.coolify.yml
├── docker/
│   ├── entrypoint.sh
│   ├── nginx.conf
│   ├── php-fpm.conf
│   ├── php.ini
│   ├── supervisord.conf
│   └── kiosk.sh
└── public/
    └── videos/
        └── video.mp4
```

```bash
git add -A
git commit -m "Ajout config Docker pour Coolify + kiosk pointage"
git push
```

---

## 2. Configurer Coolify

### 2.1 Créer un nouveau projet

1. Aller sur votre Coolify (`https://coolify.votre-domaine.com`)
2. **+ New Project** → nommer "Genius Work"
3. **+ New Resource** → **Docker Compose**

### 2.2 Connecter le repo Git

1. Choisir **GitHub** (ou GitLab)
2. Autoriser Coolify à accéder à votre repo
3. Sélectionner le repo `GeniusWorkAdmin`
4. Branch : `main` (ou votre branche de prod)

### 2.3 Configurer le Docker Compose

1. **Docker Compose Location** : `docker-compose.coolify.yml`
2. Coolify détecte automatiquement les 2 services : `app` et `db`

### 2.4 Configurer le domaine

1. Aller dans **Service** → `app`
2. **Domains** : `votre-domaine.com`
3. Coolify génère automatiquement le SSL Let's Encrypt

### 2.5 Variables d'environnement

Dans **Service** → `app` → **Environment Variables**, ajouter :

| Variable | Valeur |
|----------|--------|
| `APP_NAME` | `Genius Work` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://votre-domaine.com` |
| `DB_HOST` | `db` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `genius_work` |
| `DB_USERNAME` | `genius` |
| `DB_PASSWORD` | `un-mot-de-passe-securise` |
| `DB_ROOT_PASSWORD` | `un-autre-mot-de-passe` |
| `SANCTUM_STATEFUL_DOMAINS` | `votre-domaine.com` |
| `SESSION_DOMAIN` | `votre-domaine.com` |

### 2.6 Déployer

1. Cliquer **Deploy**
2. Coolify build l'image Docker
3. Démarre les conteneurs
4. Configure le SSL automatiquement
5. L'app est accessible sur `https://votre-domaine.com`

---

## 3. Récupérer l'URL Kiosque

Après le premier déploiement, aller dans **Coolify** → **Service app** → **Terminal** :

```bash
php artisan tinker --execute="echo url('/kiosk/' . \App\Models\Site::first()->kiosk_token);"
```

Résultat :
```
https://votre-domaine.com/kiosk/Uiqmr1fvY7iU4iBfyOZ0VN2kXc384xrgSKf5rq3QZZOyq1nI
```

---

## 4. Placer la vidéo entreprise

### Option A : Via le repo Git (recommandé)

```bash
# En local
cp votre-video.mp4 public/videos/video.mp4
git add public/videos/video.mp4
git commit -m "Ajout vidéo entreprise"
git push
# Coolify redéploie automatiquement
```

### Option B : Via le volume persistant

```bash
# Sur le VPS, copier la vidéo dans le volume Docker
docker cp votre-video.mp4 $(docker ps -qf "name=app"):/var/www/html/public/videos/video.mp4
```

---

## 5. Configurer le mini PC

### 5.1 Installer Chrome + unclutter

```bash
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
sudo apt update && sudo apt install -y google-chrome-stable unclutter
```

### 5.2 Créer le script kiosk

```bash
sudo nano /opt/kiosk.sh
```

```bash
#!/bin/bash
# === Configuration ===
KIOSK_URL="https://votre-domaine.com/kiosk/VOTRE_TOKEN_ICI"

# Cacher le curseur après 3s
unclutter -idle 3 &

# Désactiver l'économiseur d'écran
xset s off
xset s noblank
xset -dpms

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
    --user-data-dir=/tmp/chrome-kiosk \
    "$KIOSK_URL"

# Relance auto en cas de crash
while true; do
    sleep 5
    google-chrome-stable --kiosk --autoplay-policy=no-user-gesture-required "$KIOSK_URL"
done
```

```bash
sudo chmod +x /opt/kiosk.sh
```

### 5.3 Démarrage automatique au boot

```bash
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
```

### 5.4 Désactiver l'économiseur d'écran

```bash
gsettings set org.gnome.desktop.screensaver lock-enabled false
gsettings set org.gnome.desktop.session idle-delay 0
sudo systemctl mask sleep.target suspend.target hibernate.target hybrid-sleep.target
```

### 5.5 Auto-login

```bash
sudo nano /etc/gdm3/custom.conf
# [daemon]
# AutomaticLoginEnable=true
# AutomaticLogin=votre-utilisateur
```

---

## 6. Test final

1. Allumer le mini PC → Chrome s'ouvre en kiosk sur `https://votre-domaine.com/kiosk/TOKEN`
2. La vidéo entreprise démarre en plein écran **avec le son** (grâce au flag `--autoplay-policy`)
3. L'horloge s'affiche en haut à droite
4. Scanner un badge QR avec la douchette
5. Page de transition : nom + type + **message vocal**
6. Après 5s → retour à la vidéo
7. Vérifier dans l'admin → Présences

---

## 7. Maintenance avec Coolify

### Mettre à jour l'application

```bash
# En local
git push
# Coolify redéploie automatiquement (si auto-deploy activé)
# Ou manuellement : Coolify → Deploy
```

### Voir les logs

```bash
# Via Coolify UI → Service app → Logs
# Ou via terminal
docker logs -f $(docker ps -qf "name=app")
```

### Changer la vidéo

```bash
# Via git (recommandé)
cp nouvelle-video.mp4 public/videos/video.mp4
git commit -am "Nouvelle vidéo entreprise"
git push
```

### Régénérer le token kiosk

```bash
# Via Coolify → Terminal
php artisan tinker --execute="
\$site = \App\Models\Site::first();
\$site->kiosk_token = \Illuminate\Support\Str::random(48);
\$site->save();
echo url('/kiosk/' . \$site->kiosk_token);
"
# Mettre à jour /opt/kiosk.sh sur le mini PC
sudo systemctl restart kiosk.service
```

### Sauvegarde DB

```bash
# Via Coolify → Service db → Terminal
mysqldump -u root -p${DB_ROOT_PASSWORD} genius_work > /tmp/backup.sql
# Copier hors du conteneur
docker cp $(docker ps -qf "name=db"):/tmp/backup.sql ./backup.sql
```
