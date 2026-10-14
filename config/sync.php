<?php

return [
    // IP autorisées à lire les changements (séparées par des virgules).
    // Vide = aucune IP autorisée.
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('SYNC_ALLOWED_IPS', '')))),
];
