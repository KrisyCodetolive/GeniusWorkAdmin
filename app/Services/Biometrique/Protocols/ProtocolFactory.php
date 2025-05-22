<?php

namespace App\Services\Biometrique\Protocols;

use App\Models\AppareilBiometrique;
use App\Services\Biometrique\Protocols\Implementations\AnvizProtocol;
use App\Services\Biometrique\Protocols\Implementations\GenericHttpProtocol;
use App\Services\Biometrique\Protocols\Implementations\HikVisionProtocol;
use App\Services\Biometrique\Protocols\Implementations\ZKTecoProtocol;
use App\Services\Biometrique\Protocols\Interfaces\BiometriqueProtocolInterface;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Factory pour créer les protocoles de communication avec les appareils biométriques
 */
class ProtocolFactory
{
    /**
     * Crée une instance de protocole pour un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return BiometriqueProtocolInterface|null
     */
    public function createProtocol(AppareilBiometrique $appareil): ?BiometriqueProtocolInterface
    {
        try {
            switch (strtolower($appareil->fabricant)) {
                case 'zkteco':
                    return new ZKTecoProtocol($appareil);
                case 'hikvision':
                    return new HikVisionProtocol($appareil);
                case 'anviz':
                    return new AnvizProtocol($appareil);
                case 'generic_http':
                case 'http':
                    return new GenericHttpProtocol($appareil);
                default:
                    Log::error("Fabricant d'appareil biométrique non supporté", [
                        'fabricant' => $appareil->fabricant,
                        'appareil_id' => $appareil->id
                    ]);
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du protocole biométrique", [
                'fabricant' => $appareil->fabricant,
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
