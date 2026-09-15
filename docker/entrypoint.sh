#!/bin/bash
set -e

# ===========================================
# Entrypoint — Génère le .env depuis les variables d'environnement
# (Coolify injecte les vars via l'UI, pas via un fichier .env)
# ===========================================

# Générer le fichier .env depuis les variables d'environnement
echo "📝 Génération du fichier .env..."
cat > /var/www/html/.env <<EOF
APP_NAME="${APP_NAME:-Genius Work}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-genius_work}"
DB_USERNAME="${DB_USERNAME:-genius}"
DB_PASSWORD="${DB_PASSWORD:-secret}"

BROADCAST_DRIVER="${BROADCAST_DRIVER:-log}"
CACHE_DRIVER="${CACHE_DRIVER:-file}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"

SANCTUM_STATEFUL_DOMAINS="${SANCTUM_STATEFUL_DOMAINS:-}"
SESSION_DOMAIN="${SESSION_DOMAIN:-}"
EOF

# Générer la clé APP_KEY si manquante
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
    echo "🔑 Génération de la clé APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# Attendre que la base de données soit prête
echo "⏳ Attente de la base de données..."
max_retries=30
retry=0
while ! mysqladmin ping -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-genius}" -p"${DB_PASSWORD:-secret}" --silent 2>/dev/null; do
    retry=$((retry + 1))
    if [ $retry -ge $max_retries ]; then
        echo "❌ Impossible de se connecter à la base de données après $max_retries tentatives"
        exit 1
    fi
    echo "  Tentative $retry/$max_retries..."
    sleep 2
done
echo "✅ Base de données accessible"

# Optimisations Laravel
echo "🔧 Optimisations Laravel..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan event:cache --no-interaction

# Migrations
echo "📊 Exécution des migrations..."
php artisan migrate --force --no-interaction

# Démarrer Supervisor (Nginx + PHP-FPM + Queue)
echo "🚀 Démarrage des services..."
exec "$@"
