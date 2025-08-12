<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SMSServiceFactory
{
    /**
     * Crée une instance du service SMS par défaut ou spécifié
     *
     * @param string|null $provider Fournisseur SMS à utiliser (null pour utiliser le fournisseur par défaut)
     * @return OrangeSMSService|MTNSMSService Instance du service SMS
     */
    public static function create($provider = null)
    {
        // Si aucun fournisseur n'est spécifié, utiliser le fournisseur par défaut
        if ($provider === null) {
            $provider = Config::get('sms.default_provider', 'orange');
        }
        
        Log::channel('sms')->info('Création du service SMS', [
            'provider' => $provider
        ]);
        
        // Créer l'instance du service SMS approprié
        switch ($provider) {
            case 'mtn':
                return app(MTNSMSService::class);
            
            case 'orange':
            default:
                return app(OrangeSMSService::class);
        }
    }
    
    /**
     * Crée une instance du service de bienvenue SMS par défaut ou spécifié
     *
     * @param string|null $provider Fournisseur SMS à utiliser (null pour utiliser le fournisseur par défaut)
     * @return SMSServiceInterface Instance du service de bienvenue SMS
     */
    public static function createWelcomeService($provider = null)
    {
        // Si aucun fournisseur n'est spécifié, utiliser le fournisseur par défaut
        if ($provider === null) {
            $provider = Config::get('sms.default_provider', 'orange');
        }
        
        Log::channel('sms')->info('Création du service de bienvenue SMS', [
            'provider' => $provider
        ]);
        
        // Créer l'instance du service de bienvenue SMS approprié
        switch ($provider) {
            case 'mtn':
                return app(MTNWelcomeSMSService::class);
            
            case 'orange':
            default:
                return app(WelcomeSMSService::class);
        }
    }
}
