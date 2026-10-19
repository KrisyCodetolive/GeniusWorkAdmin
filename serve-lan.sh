#!/usr/bin/env bash
# Lance le serveur Laravel sur toutes les interfaces (accessible depuis le réseau local).
cd "$(dirname "$0")"
IP=$(ip -4 route get 1.1.1.1 2>/dev/null | grep -oP 'src \K\S+')
echo "Portail dépenses : http://${IP:-127.0.0.1}:8000/depenses"
PHP_CLI_SERVER_WORKERS=4 exec php artisan serve --host=0.0.0.0 --port=8000
