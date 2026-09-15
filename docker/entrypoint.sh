#!/bin/bash
set -e

# ===========================================
# Entrypoint — Génère le .env et attend la DB
# ===========================================

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

# Genius Tools API (QR codes)
GENIUS_TOOLS_API_KEY="${GENIUS_TOOLS_API_KEY:-}"
GENIUS_TOOLS_BASE_URL="${GENIUS_TOOLS_BASE_URL:-https://linkqr.genius.ci/api}"

# Redis (optionnel)
REDIS_CLIENT="${REDIS_CLIENT:-phpredis}"
REDIS_HOST="${REDIS_HOST:-}"
REDIS_PASSWORD="${REDIS_PASSWORD:-}"
REDIS_PORT="${REDIS_PORT:-6379}"
REDIS_DB="${REDIS_DB:-0}"

SANCTUM_STATEFUL_DOMAINS="${SANCTUM_STATEFUL_DOMAINS:-}"
SESSION_DOMAIN="${SESSION_DOMAIN:-}"
EOF

# Générer la clé APP_KEY si manquante
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
    echo "🔑 Génération de la clé APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# Attendre que la base de données soit prête (via PHP/pdo)
echo "⏳ Attente de la base de données (${DB_HOST}:${DB_PORT})..."
max_retries=30
retry=0
while [ $retry -lt $max_retries ]; do
    retry=$((retry + 1))
    php -r "
        try {
            \$pdo = new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
                '${DB_USERNAME}',
                '${DB_PASSWORD}',
                [PDO::ATTR_TIMEOUT => 5]
            );
            echo \"OK\n\";
            exit(0);
        } catch (Exception \$e) {
            echo \$e->getMessage() . \"\n\";
            exit(1);
        }
    " 2>/dev/null && break
    echo "  Tentative $retry/$max_retries..."
    sleep 2
done

if [ $retry -ge $max_retries ]; then
    echo "❌ Impossible de se connecter à la base de données après $max_retries tentatives"
    echo "   Host: ${DB_HOST}:${DB_PORT}"
    echo "   Database: ${DB_DATABASE}"
    echo "   User: ${DB_USERNAME}"
    # Afficher l'erreur réelle
    php -r "
        try {
            \$pdo = new PDO(
                'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
                '${DB_USERNAME}',
                '${DB_PASSWORD}',
                [PDO::ATTR_TIMEOUT => 5]
            );
        } catch (Exception \$e) {
            echo '   Erreur: ' . \$e->getMessage() . \"\n\";
        }
    " 2>&1
    exit 1
fi
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
