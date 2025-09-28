<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebAuthnController extends Controller
{
    /**
     * Afficher la page de gestion des appareils WebAuthn
     */
    public function index()
    {
        $user = Auth::user();
        $credentials = $user->webAuthnCredentials()->get();
        
        return view('app.webPointage.webauthn.index', [
            'credentials' => $credentials
        ]);
    }
    
    /**
     * Générer les options d'enregistrement pour un nouvel appareil
     */
    public function generateRegistrationOptions(Request $request)
    {
        // For testing purposes, allow unauthenticated access
        if (!Auth::check()) {
            // Create a mock user for testing
            $userId = '123456789';
            $userName = 'test@example.com';
            $displayName = 'Test User';
            $maxDevices = 3;
        } else {
            $user = Auth::user();
            
            // Vérifier si l'utilisateur a atteint le nombre maximum d'appareils
            $methodePointage = $user->entreprise->methodePointages()
                ->where('code', 'like', 'WEB-%')
                ->first();
                
            if (!$methodePointage) {
                return response()->json([
                    'error' => 'Méthode de pointage WebAuthn non configurée pour votre entreprise'
                ], 400);
            }
            
            $config = json_decode($methodePointage->configuration);
            $maxDevices = $config->max_devices_per_user ?? 3;
            
            if ($user->webAuthnCredentials()->count() >= $maxDevices) {
                return response()->json([
                    'error' => 'Nombre maximum d\'appareils atteint'
                ], 400);
            }
            
            $userId = $user->id;
            $userName = $user->email;
            $displayName = $user->name;
        }
        
        // Générer les options d'enregistrement
        try {
            // Simuler la génération d'options d'enregistrement
            // Dans une implémentation réelle, vous utiliseriez une bibliothèque WebAuthn
            $challenge = base64_encode(random_bytes(32));
            
            // Stocker le challenge en session pour la vérification ultérieure
            session(['webauthn_challenge' => $challenge]);
            
            return response()->json([
                'publicKey' => [
                    'challenge' => $challenge,
                    'rp' => [
                        'name' => config('app.name'),
                        'id' => parse_url(config('app.url'), PHP_URL_HOST)
                    ],
                    'user' => [
                        'id' => base64_encode($userId),
                        'name' => $userName,
                        'displayName' => $displayName
                    ],
                    'pubKeyCredParams' => [
                        ['type' => 'public-key', 'alg' => -7], // ES256
                        ['type' => 'public-key', 'alg' => -257] // RS256
                    ],
                    'timeout' => 60000,
                    'attestation' => 'direct',
                    'authenticatorSelection' => [
                        'authenticatorAttachment' => $request->input('type', 'platform'),
                        'userVerification' => 'preferred',
                        'requireResidentKey' => false
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des options d\'enregistrement WebAuthn: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la génération des options'], 500);
        }
    }
    
    /**
     * Vérifier et enregistrer une nouvelle information d'identification
     */
    public function register(Request $request)
    {
        $user = Auth::user();
        
        // Valider les données de la requête
        $request->validate([
            'credential_id' => 'required|string',
            'public_key' => 'required|string',
            'counter' => 'required|integer',
            'type' => 'required|string|in:platform,cross-platform',
            'name' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:255',
        ]);
        
        // Vérifier que le challenge correspond
        $challenge = session('webauthn_challenge');
        if (!$challenge) {
            return response()->json(['error' => 'Challenge invalide ou expiré'], 400);
        }
        
        // Vérifier que l'ID d'information d'identification n'est pas déjà utilisé
        if (WebAuthnCredential::where('credential_id', $request->credential_id)->exists()) {
            return response()->json(['error' => 'Cet appareil est déjà enregistré'], 400);
        }
        
        try {
            // Dans une implémentation réelle, vous vérifieriez la réponse d'attestation ici
            // Simuler une vérification réussie
            
            // Créer une nouvelle information d'identification
            $credential = new WebAuthnCredential([
                'user_id' => $user->id,
                'credential_id' => $request->credential_id,
                'public_key' => $request->public_key,
                'counter' => $request->counter,
                'name' => $request->name,
                'type' => $request->type,
                'device_type' => $request->device_type ?? 'unknown',
                'is_active' => true,
                'last_used_at' => now()
            ]);
            
            $credential->save();
            
            // Supprimer le challenge de la session
            session()->forget('webauthn_challenge');
            
            return response()->json([
                'success' => true,
                'message' => 'Appareil enregistré avec succès',
                'credential' => $credential
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement WebAuthn: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'enregistrement'], 500);
        }
    }
    
    /**
     * Générer les options d'authentification
     */
    public function generateAuthenticationOptions(Request $request)
    {
        $user = Auth::user();
        
        // Récupérer les informations d'identification de l'utilisateur
        $credentials = $user->webAuthnCredentials()->active()->get();
        
        if ($credentials->isEmpty()) {
            return response()->json([
                'error' => 'Aucun appareil enregistré pour cet utilisateur'
            ], 400);
        }
        
        try {
            // Générer un nouveau challenge
            $challenge = base64_encode(random_bytes(32));
            
            // Stocker le challenge en session pour la vérification ultérieure
            session(['webauthn_auth_challenge' => $challenge]);
            
            // Préparer les informations d'identification autorisées
            $allowCredentials = $credentials->map(function ($credential) {
                return [
                    'type' => 'public-key',
                    'id' => $credential->credential_id,
                    'transports' => ['internal', 'usb', 'ble', 'nfc']
                ];
            })->toArray();
            
            return response()->json([
                'publicKey' => [
                    'challenge' => $challenge,
                    'timeout' => 60000,
                    'rpId' => parse_url(config('app.url'), PHP_URL_HOST),
                    'allowCredentials' => $allowCredentials,
                    'userVerification' => 'preferred'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des options d\'authentification WebAuthn: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la génération des options'], 500);
        }
    }
    
    /**
     * Vérifier l'authentification
     */
    public function authenticate(Request $request)
    {
        // Valider les données de la requête
        $request->validate([
            'credential_id' => 'required|string',
            'authenticator_data' => 'required|string',
            'signature' => 'required|string',
            'client_data_json' => 'required|string',
        ]);
        
        // Vérifier que le challenge correspond
        $challenge = session('webauthn_auth_challenge');
        if (!$challenge) {
            return response()->json(['error' => 'Challenge invalide ou expiré'], 400);
        }
        
        try {
            // Récupérer l'information d'identification
            $credential = WebAuthnCredential::where('credential_id', $request->credential_id)
                ->active()
                ->first();
                
            if (!$credential) {
                return response()->json(['error' => 'Information d\'identification non trouvée ou inactive'], 400);
            }
            
            // Dans une implémentation réelle, vous vérifieriez la signature ici
            // Simuler une vérification réussie
            
            // Mettre à jour le compteur et la date de dernière utilisation
            $credential->markAsUsed($request->counter ?? $credential->counter + 1);
            
            // Supprimer le challenge de la session
            session()->forget('webauthn_auth_challenge');
            
            return response()->json([
                'success' => true,
                'message' => 'Authentification réussie'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'authentification WebAuthn: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'authentification'], 500);
        }
    }
    
    /**
     * Renommer une information d'identification
     */
    public function rename(Request $request, $id)
    {
        $user = Auth::user();
        
        // Valider les données de la requête
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        // Récupérer l'information d'identification
        $credential = $user->webAuthnCredentials()->findOrFail($id);
        
        // Mettre à jour le nom
        $credential->update(['name' => $request->name]);
        
        return response()->json([
            'success' => true,
            'message' => 'Appareil renommé avec succès',
            'credential' => $credential
        ]);
    }
    
    /**
     * Supprimer une information d'identification
     */
    public function delete($id)
    {
        $user = Auth::user();
        
        // Récupérer l'information d'identification
        $credential = $user->webAuthnCredentials()->findOrFail($id);
        
        // Supprimer l'information d'identification
        $credential->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Appareil supprimé avec succès'
        ]);
    }
    
    /**
     * Vérifier si l'utilisateur a des informations d'identification WebAuthn
     */
    public function checkCredentials(Request $request)
    {
        // For testing purposes, allow unauthenticated access
        if (!Auth::check()) {
            return response()->json([
                'hasCredentials' => false,
                'count' => 0,
                'message' => 'User not authenticated'
            ]);
        }
        
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a des informations d'identification actives
        $hasCredentials = $user->webAuthnCredentials()->active()->exists();
        
        return response()->json([
            'hasCredentials' => $hasCredentials,
            'count' => $user->webAuthnCredentials()->active()->count()
        ]);
    }
}
