<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des passerelles de paiement
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les configurations pour les différentes passerelles
    | de paiement utilisées dans l'application.
    |
    */

    // Configuration Paystack
    'paystack' => [
        'base_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
        'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL', 'work@genius.ci'),
        'active' => env('PAYSTACK_ACTIVE', true),
        'methods' => [
            'card' => [
                'active' => true,
                'title' => 'Carte bancaire',
                'description' => 'Paiement sécurisé par carte bancaire',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 100, // Montant minimum en FCFA
            ],
            'mobile_money' => [
                'active' => true,
                'title' => 'Mobile Money',
                'description' => 'Paiement via Orange Money, MTN Money, etc.',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 100, // Montant minimum en FCFA
            ],
        ],
    ],

    // Configuration Stripe
    'stripe' => [
        'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'XOF'),
        'active' => env('STRIPE_ACTIVE', true),
        'methods' => [
            'card' => [
                'active' => true,
                'title' => 'Carte bancaire internationale',
                'description' => 'Visa, Mastercard, American Express',
                'fees' => 2.9, // Frais en pourcentage
                'min_amount' => 500, // Montant minimum en FCFA
            ],
        ],
    ],

    // Configuration des paiements manuels
    'manuel' => [
        'active' => env('MANUEL_ACTIVE', true),
        'banque' => env('MANUEL_BANQUE', 'Banque d\'Abidjan (BDA)'),
        'iban' => env('MANUEL_IBAN', 'CI20101001111143298381 36'),
        'bic' => env('MANUEL_BIC', 'BDACCIAB'),
        'beneficiaire' => env('MANUEL_BENEFICIAIRE', 'GENIUS GROUPS SAS'),
        'adresse' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
        'ordre' => env('MANUEL_ORDRE', 'GENIUS GROUPS SAS'),
        'horaires' => env('MANUEL_HORAIRES', 'Du lundi au vendredi de 9h à 17h'),
        'email' => env('MANUEL_EMAIL', 'work@genius.ci'),
        'telephone' => env('MANUEL_TELEPHONE', '+225 27 22 25 26 28'),
        'methods' => [
            'bank_transfer' => [
                'active' => true,
                'title' => 'Virement bancaire',
                'description' => 'Paiement par virement bancaire',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 1000, // Montant minimum en FCFA
                'instructions' => 'Veuillez effectuer un virement bancaire avec la référence de votre commande.',
            ],
            'check' => [
                'active' => true,
                'title' => 'Chèque',
                'description' => 'Paiement par chèque',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 1000, // Montant minimum en FCFA
                'adresse_ligne1' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne2' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne3' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'instructions' => 'Veuillez envoyer votre chèque à l\'ordre de GENIUS GROUPS SAS à l\'adresse indiquée.',
            ],
            'cash' => [
                'active' => true,
                'title' => 'Espèces',
                'description' => 'Paiement en espèces à nos bureaux',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 100, // Montant minimum en FCFA
                'adresse_ligne1' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne2' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne3' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'horaires' => env('MANUEL_HORAIRES', 'Du lundi au vendredi de 9h à 17h'),
                'instructions' => 'Veuillez vous présenter à nos bureaux aux horaires d\'ouverture pour effectuer votre paiement.',
            ],
            'especes' => [
                'active' => true,
                'title' => 'Espèces',
                'description' => 'Paiement en espèces à nos bureaux ou via Wave',
                'fees' => 0, // Frais en pourcentage
                'min_amount' => 100, // Montant minimum en FCFA
                'adresse_ligne1' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne2' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'adresse_ligne3' => env('MANUEL_ADRESSE', 'GENIUS GROUPS SAS, 298 Riviera Bonoumin, Cocody Abidjan, Côte d\'Ivoire'),
                'horaires' => env('MANUEL_HORAIRES', 'Du lundi au vendredi de 9h à 17h'),
                'wave_link' => env('MANUEL_WAVE_LINK', 'https://pay.wave.com/m/M_CivAV8HL-7pF/c/ci/'),
                'instructions' => 'Veuillez vous présenter à nos bureaux aux horaires d\'ouverture pour effectuer votre paiement ou utilisez Wave pour un paiement instantané.',
            ],
        ],
    ],

    // Configuration générale
    'default_currency' => env('DEFAULT_CURRENCY', 'XOF'),
    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'paystack'),
    
    // Délai d'expiration des paiements en minutes
    'expiration_delay' => env('PAYMENT_EXPIRATION_DELAY', 60),
    
    // Configuration des notifications
    'notifications' => [
        'email' => [
            'payment_success' => env('NOTIFY_EMAIL_PAYMENT_SUCCESS', true),
            'payment_failed' => env('NOTIFY_EMAIL_PAYMENT_FAILED', true),
            'payment_pending' => env('NOTIFY_EMAIL_PAYMENT_PENDING', true),
        ],
        'sms' => [
            'payment_success' => env('NOTIFY_SMS_PAYMENT_SUCCESS', false),
            'payment_failed' => env('NOTIFY_SMS_PAYMENT_FAILED', false),
            'payment_pending' => env('NOTIFY_SMS_PAYMENT_PENDING', false),
        ],
    ],
    
    // Informations de support et contact
    'support' => [
        'whatsapp' => env('SUPPORT_WHATSAPP', 'https://wa.me/message/JQEND7QOA3QQG1'),
        'telephone' => env('SUPPORT_TELEPHONE', '+225 27 22 25 26 28'),
        'email' => env('SUPPORT_EMAIL', 'support@genius.ci'),
        'horaires' => env('SUPPORT_HORAIRES', 'Du lundi au vendredi de 9h à 17h'),
    ],
];
