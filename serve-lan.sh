#!/usr/bin/env bash
# Lance le serveur Laravel sur toutes les interfaces (accessible depuis le réseau local).
cd "$(dirname "$0")/public" || exit 1
IP=$(ip -4 route get 1.1.1.1 2>/dev/null | grep -oP 'src \K\S+')
echo "Portail dépenses : http://${IP:-127.0.0.1}:8000/depense"
# Limites d'envoi relevées pour tester le téléversement de la vidéo du kiosque.
PHP_CLI_SERVER_WORKERS=4 exec php \
  -d upload_max_filesize=600M -d post_max_size=610M -d memory_limit=512M -d max_input_time=900 \
  -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
