<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employeur;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WebAuthnService
{
    /**
     * Génère les options pour l'enregistrement WebAuthn
     *
     * @param User $user
     * @return array
     */
    public function generateRegistrationOptions(User $user): array
    {
        try {
            // Générer un challenge unique
            $challenge = $this->generateChallenge();
            
            // Encoder le challenge en base64url avant de le stocker
            $encodedChallenge = base64url_encode($challenge);
            
            // Stocker le challenge encodé en cache pour validation ultérieure
            Cache::put("webauthn_challenge_{$user->id}", $encodedChallenge, now()->addMinutes(5));
            
            $options = [
                'challenge' => base64url_encode($challenge),
                'rp' => [
                    'name' => config('app.name', 'GeniusWork'),
                    'id' => parse_url(config('app.url'), PHP_URL_HOST)
                ],
                'user' => [
                    'id' => base64url_encode($user->id),
                    'name' => $user->email,
                    'displayName' => $user->name ?? $user->email
                ],
                'pubKeyCredParams' => [
                    [
                        'type' => 'public-key',
                        'alg' => -7 // ES256
                    ],
                    [
                        'type' => 'public-key',
                        'alg' => -257 // RS256
                    ]
                ],
                'authenticatorSelection' => [
                    'authenticatorAttachment' => 'platform',
                    'userVerification' => 'required',
                    'residentKey' => 'preferred'
                ],
                'timeout' => 60000,
                'attestation' => 'none'
            ];
            
            Log::info('Options d\'enregistrement WebAuthn générées', [
                'user_id' => $user->id,
                'challenge_length' => strlen($challenge)
            ]);
            
            return $options;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des options d\'enregistrement WebAuthn', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Vérifie l'enregistrement WebAuthn
     *
     * @param array $response
     * @param User $user
     * @return bool
     */
    public function verifyRegistration(array $response, User $user): bool
    {
        try {
            // Récupérer le challenge stocké
            $storedChallenge = Cache::get("webauthn_challenge_{$user->id}");
            
            if (!$storedChallenge) {
                Log::warning('Challenge WebAuthn non trouvé ou expiré', ['user_id' => $user->id]);
                return false;
            }
            
            // Décoder le challenge stocké en base64url
            $storedChallenge = base64url_decode($storedChallenge);
            
            // Supprimer le challenge du cache
            Cache::forget("webauthn_challenge_{$user->id}");
            
            // Vérification basique (à améliorer avec une vraie librairie WebAuthn)
            if (!isset($response['id']) || !isset($response['rawId']) || !isset($response['response'])) {
                Log::warning('Réponse WebAuthn incomplète', ['user_id' => $user->id]);
                return false;
            }
            
            // Stocker les informations du credential
            $credentialId = $response['id'];
            $publicKey = $response['response']['publicKey'] ?? null;
            
            // Sauvegarder le credential pour l'utilisateur
            $this->storeCredential($user, $credentialId, $publicKey);
            
            Log::info('Enregistrement WebAuthn vérifié avec succès', [
                'user_id' => $user->id,
                'credential_id' => substr($credentialId, 0, 10) . '...'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'enregistrement WebAuthn', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    /**
     * Génère les options pour l'authentification WebAuthn
     *
     * @param string $email
     * @return array
     */
    public function generateAuthenticationOptions(string $email): array
    {
        try {
            // Trouver l'utilisateur par email
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }
            
            // Générer un challenge unique
            $challenge = $this->generateChallenge();
            
            // Encoder le challenge en base64url avant de le stocker dans le cache
            $encodedChallenge = base64url_encode($challenge);
            
            // Stocker le challenge encodé en cache
            Cache::put("webauthn_auth_challenge_{$email}", [
                'challenge' => $encodedChallenge,
                'user_id' => $user->id
            ], now()->addMinutes(5));
            
            // Récupérer les credentials existants de l'utilisateur
            $allowCredentials = $this->getUserCredentials($user);
            
            $options = [
                'challenge' => base64url_encode($challenge),
                'timeout' => 60000,
                'rpId' => parse_url(config('app.url'), PHP_URL_HOST),
                'allowCredentials' => $allowCredentials,
                'userVerification' => 'required'
            ];
            
            Log::info('Options d\'authentification WebAuthn générées', [
                'email' => $email,
                'user_id' => $user->id,
                'credentials_count' => count($allowCredentials)
            ]);
            
            return $options;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des options d\'authentification WebAuthn', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            throw $e;
        }
    }

    /**
     * Vérifie l'authentification WebAuthn
     *
     * @param array $response
     * @param string $email
     * @return User|null
     */
    public function verifyAuthentication(array $response, string $email): ?User
    {
        try {
            // Récupérer les données du challenge
            $challengeData = Cache::get("webauthn_auth_challenge_{$email}");
            
            if (!$challengeData) {
                Log::warning('Challenge d\'authentification WebAuthn non trouvé ou expiré', ['email' => $email]);
                return null;
            }
            
            // Décoder le challenge stocké en base64url
            if (isset($challengeData['challenge'])) {
                $challengeData['challenge'] = base64url_decode($challengeData['challenge']);
            }
            
            // Supprimer le challenge du cache
            Cache::forget("webauthn_auth_challenge_{$email}");
            
            $user = User::find($challengeData['user_id']);
            
            if (!$user) {
                Log::warning('Utilisateur non trouvé lors de l\'authentification WebAuthn', ['email' => $email]);
                return null;
            }
            
            // Vérification basique (à améliorer avec une vraie librairie WebAuthn)
            if (!isset($response['id']) || !isset($response['rawId']) || !isset($response['response'])) {
                Log::warning('Réponse d\'authentification WebAuthn incomplète', ['user_id' => $user->id]);
                return null;
            }
            
            // Vérifier que le credential appartient à l'utilisateur
            $credentialId = $response['id'];
            if (!$this->verifyUserCredential($user, $credentialId)) {
                Log::warning('Credential WebAuthn non valide pour l\'utilisateur', [
                    'user_id' => $user->id,
                    'credential_id' => substr($credentialId, 0, 10) . '...'
                ]);
                return null;
            }
            
            Log::info('Authentification WebAuthn réussie', [
                'user_id' => $user->id,
                'email' => $email
            ]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'authentification WebAuthn', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return null;
        }
    }

    /**
     * Trouve un employeur par email et vérifie son authentification WebAuthn
     *
     * @param string $email
     * @param array $webauthnResponse
     * @return Employeur|null
     */
    public function authenticateEmployeur(string $email, array $webauthnResponse): ?Employeur
    {
        try {
            // D'abord, authentifier l'utilisateur via WebAuthn
            $user = $this->verifyAuthentication($webauthnResponse, $email);
            
            if (!$user) {
                return null;
            }
            
            // Trouver l'employeur associé à cet utilisateur
            $employeur = Employeur::where('email', $email)->first();
            
            if (!$employeur) {
                Log::warning('Employeur non trouvé pour l\'email authentifié', ['email' => $email]);
                return null;
            }
            
            // Vérifier que l'employeur est actif
            if ($employeur->statut !== 'actif') {
                Log::warning('Tentative d\'authentification d\'un employeur inactif', [
                    'employeur_id' => $employeur->id,
                    'statut' => $employeur->statut
                ]);
                return null;
            }
            
            Log::info('Employeur authentifié avec succès via WebAuthn', [
                'employeur_id' => $employeur->id,
                'email' => $email
            ]);
            
            return $employeur;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'authentification de l\'employeur', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return null;
        }
    }

    /**
     * Génère un challenge cryptographique sécurisé
     *
     * @return string
     */
    private function generateChallenge(): string
    {
        return random_bytes(32);
    }

    /**
     * Stocke un credential WebAuthn pour un utilisateur
     *
     * @param User $user
     * @param string $credentialId
     * @param string|null $publicKey
     * @return void
     */
    private function storeCredential(User $user, string $credentialId, ?string $publicKey): void
    {
        // Stocker dans la table webauthn_credentials ou dans les métadonnées utilisateur
        $credentials = $user->webauthn_credentials ?? [];
        
        $credentials[] = [
            'id' => $credentialId,
            'public_key' => $publicKey,
            'created_at' => now()->toISOString(),
            'last_used' => null
        ];
        
        $user->update(['webauthn_credentials' => $credentials]);
    }

    /**
     * Récupère les credentials WebAuthn d'un utilisateur
     *
     * @param User $user
     * @return array
     */
    private function getUserCredentials(User $user): array
    {
        $credentials = $user->webauthn_credentials ?? [];
        
        return array_map(function ($credential) {
            return [
                'id' => $credential['id'],
                'type' => 'public-key',
                'transports' => ['internal', 'hybrid']
            ];
        }, $credentials);
    }

    /**
     * Vérifie qu'un credential appartient à un utilisateur
     *
     * @param User $user
     * @param string $credentialId
     * @return bool
     */
    private function verifyUserCredential(User $user, string $credentialId): bool
    {
        $credentials = $user->webauthn_credentials ?? [];
        
        foreach ($credentials as $credential) {
            if ($credential['id'] === $credentialId) {
                // Mettre à jour la date de dernière utilisation
                $this->updateCredentialLastUsed($user, $credentialId);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Met à jour la date de dernière utilisation d'un credential
     *
     * @param User $user
     * @param string $credentialId
     * @return void
     */
    private function updateCredentialLastUsed(User $user, string $credentialId): void
    {
        $credentials = $user->webauthn_credentials ?? [];
        
        foreach ($credentials as &$credential) {
            if ($credential['id'] === $credentialId) {
                $credential['last_used'] = now()->toISOString();
                break;
            }
        }
        
        $user->update(['webauthn_credentials' => $credentials]);
    }

    /**
     * Supprime un credential WebAuthn d'un utilisateur
     *
     * @param User $user
     * @param string $credentialId
     * @return bool
     */
    public function removeCredential(User $user, string $credentialId): bool
    {
        try {
            $credentials = $user->webauthn_credentials ?? [];
            
            $credentials = array_filter($credentials, function ($credential) use ($credentialId) {
                return $credential['id'] !== $credentialId;
            });
            
            $user->update(['webauthn_credentials' => array_values($credentials)]);
            
            Log::info('Credential WebAuthn supprimé', [
                'user_id' => $user->id,
                'credential_id' => substr($credentialId, 0, 10) . '...'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du credential WebAuthn', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur a des credentials WebAuthn enregistrés
     *
     * @param User $user
     * @return bool
     */
    public function hasCredentials(User $user): bool
    {
        $credentials = $user->webauthn_credentials ?? [];
        return count($credentials) > 0;
    }
}

/**
 * Fonction helper pour l'encodage base64url
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

/**
 * Fonction helper pour le décodage base64url
 */
if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
