<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SecurityKey extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'security_keys';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key_encrypted',
        'generated_at',
        'expires_at',
        'is_active',
        'generated_by',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Génère une nouvelle clé de sécurité.
     *
     * @param string|null $userId ID de l'utilisateur générant la clé
     * @return array
     */
    public static function generateNew($userId = null): array
    {
        // Désactiver toutes les clés actives
        self::where('is_active', true)->update(['is_active' => false]);

        // Générer une nouvelle clé aléatoire de 32 caractères
        $key = Str::random(32);

        // Créer une nouvelle entrée
        $securityKey = self::create([
            'key_encrypted' => Crypt::encryptString($key),
            'generated_at' => now(),
            'expires_at' => now()->addDays(7), // Valide pour une semaine
            'is_active' => true,
            'generated_by' => $userId,
        ]);

        return [
            'key' => $key,
            'expires_at' => $securityKey->expires_at
        ];
    }

    /**
     * Récupère la clé active actuelle ou null si aucune n'est disponible.
     *
     * @return array|null
     */
    public static function getActiveKey(): ?array
    {
        $activeKey = self::where('is_active', true)
                        ->where('expires_at', '>', now())
                        ->first();

        if (!$activeKey) {
            return null;
        }

        return [
            'key' => Crypt::decryptString($activeKey->key_encrypted),
            'expires_at' => $activeKey->expires_at
        ];
    }

    /**
     * Vérifie si une clé est valide.
     *
     * @param string $key
     * @return bool
     */
    public static function isValid(string $key): bool
    {
        $activeKey = self::where('is_active', true)
                        ->where('expires_at', '>', now())
                        ->first();

        if (!$activeKey) {
            return false;
        }

        return $key === Crypt::decryptString($activeKey->key_encrypted);
    }
}
