<?php

return [
    // Rôle de cette instance : "cloud" (en ligne), "local" (serveur de l'entreprise) ou vide (synchronisation désactivée)
    'role' => env('SYNC_ROLE'),

    // URL de l'autre instance (ex. https://app.geniuswork.com). Vide = aucun envoi vers l'autre instance.
    'remote_url' => rtrim((string) env('SYNC_REMOTE_URL', ''), '/'),

    // Secret partagé par les deux instances pour signer l'envoi des changements (HMAC SHA-256)
    'secret' => env('SYNC_SECRET'),

    // Entreprise dont les changements (pointages, demandes, statuts) sont envoyés à l'autre instance
    'entreprise_id' => env('SYNC_ENTREPRISE_ID'),

    // IP autorisées à appeler les routes de synchronisation (séparées par des virgules, vide = toutes)
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('SYNC_ALLOWED_IPS', '')))),

    // Écart maximum (secondes) entre l'horloge de l'émetteur et celle du récepteur
    'tolerance' => 300,

    // Lignes renvoyées par page lors de la récupération
    'limite' => 500,

    // Capacité du jeton obtenu en se connectant avec un compte SuperAdmin du backoffice
    'ability' => 'sync:lire',

    /*
     * Tables jamais copiées : techniques, propres à chaque instance ou contenant des secrets de session.
     * Un nom terminé par * exclut toutes les tables qui commencent ainsi.
     */
    'tables_exclues' => [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'otp_codes',
        // Chaque instance crée ses propres notifications internes (voir notifications_cloud)
        'notifications',
        'telescope_*',
        'pulse_*',
        'sync_*',
    ],

    // Tables modifiables des deux côtés : chaque changement est envoyé à l'autre instance
    'tables_bidirectionnelles' => [
        'supplementaires',
        'presences',
        'solde_conges',
        'conges',
        'permutations',
        'demandes_depense',
        'validations_depense',
        'justificatifs_depense',
    ],

    /*
     * Notifications des demandes envoyées une seule fois par e-mail : par le cloud, même pour une action faite en local.
     * L'instance locale ne garde que leur canal interne ("database"), pour ne pas envoyer chaque e-mail deux fois.
     */
    'notifications_cloud' => [
        \App\Notifications\CongeStatusNotification::class,
        \App\Notifications\DepenseNotification::class,
    ],

    /*
     * Rattachement des tables bidirectionnelles à leur entreprise, pour n'envoyer et n'accepter
     * que les lignes de l'entreprise synchronisée :
     *   'colonne'                       la colonne contient l'id de l'entreprise
     *   ['colonne' => 'table_parente']  l'entreprise est celle de la ligne parente
     */
    'rattachements' => [
        'employeurs' => 'entreprise_id',
        'supplementaires' => ['employeur_id' => 'employeurs'],
        'presences' => ['employeur_id' => 'employeurs'],
        'solde_conges' => ['employeur_id' => 'employeurs'],
        'conges' => ['employeur_id' => 'employeurs'],
        'permutations' => ['employeur_1_id' => 'employeurs'],
        'demandes_depense' => 'entreprise_id',
        'validations_depense' => ['demande_depense_id' => 'demandes_depense'],
        'justificatifs_depense' => ['demande_depense_id' => 'demandes_depense'],
    ],
];
