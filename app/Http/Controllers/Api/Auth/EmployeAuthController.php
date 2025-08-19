<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employeur;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeAuthController extends Controller
{
    protected $otpService;
    
    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\OtpService  $otpService
     * @return void
     */
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    
    /**
     * Vérifie la compatibilité de l'appareil avec le compte de l'employé
     * 
     * @param \App\Models\Employeur $employe
     * @param string $deviceModel
     * @param string $deviceId
     * @return array
     */
    protected function checkDeviceCompatibility(Employeur $employe, string $deviceModel, string $deviceId): array
    {
        // Récupérer la configuration actuelle
        $config = $employe->configuration ?? [];
        
        // Si aucun appareil n'est enregistré, autoriser celui-ci
        if (empty($config['device_model'])) {
            return ['status' => 'success'];
        }
        
        // Si l'appareil est différent de celui enregistré
        if ($config['device_model'] !== $deviceModel) {
            // Vérifier si l'ID de l'appareil correspond (même modèle mais ID différent)
            if (!empty($config['device_id']) && $config['device_id'] === $deviceId) {
                return ['status' => 'success'];
            }
            
            // Appareil différent, refuser la connexion
            return [
                'status' => 'error',
                'code' => 'device_mismatch',
                'message' => 'Cet appareil est différent de celui habituellement utilisé pour ce compte. Veuillez contacter le support pour réinitialiser votre appareil.',
                'registered_device' => $config['device_model']
            ];
        }
        
        return ['status' => 'success'];
    }
    
    /**
     * Enregistre les informations de l'appareil dans la configuration de l'employé
     * 
     * @param \App\Models\Employeur $employe
     * @param string $deviceModel
     * @param string $deviceId
     * @return void
     */
    protected function saveDeviceInfo(Employeur $employe, string $deviceModel, string $deviceId): void
    {
        // Récupérer la configuration actuelle
        $config = $employe->configuration ?? [];
        
        // Mettre à jour les informations de l'appareil
        $config['device_model'] = $deviceModel;
        $config['device_id'] = $deviceId;
        $config['last_device_login'] = now()->toIso8601String();
        
        // Sauvegarder la configuration
        $employe->configuration = $config;
        $employe->save();
        
        Log::info('Device info updated for employee', [
            'employee_id' => $employe->id,
            'device_model' => $deviceModel,
            'device_id' => $deviceId
        ]);
    }
    /**
     * Authentifie un employé avec son QR code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'device_model' => 'required|string',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données d\'authentification invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rechercher l'employeur par QR code
        $employe = Employeur::where('qr_code_secret', $request->qr_code)
                           ->where('qr_code_active', true)
                           ->where(function ($query) {
                               $query->whereNull('qr_code_expires_at')
                                     ->orWhere('qr_code_expires_at', '>', now());
                           })
                           ->first();

        if (!$employe) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR code invalide ou expiré'
            ], 401);
        }

        // Vérifier si l'employé est actif
        if ($employe->statut !== 'actif') {
            return response()->json([
                'status' => 'error',
                'message' => 'Compte employé inactif'
            ], 403);
        }
        
        // Vérifier la compatibilité de l'appareil
        $deviceCheckResult = $this->checkDeviceCompatibility($employe, $request->device_model, $request->device_id);
        if ($deviceCheckResult['status'] === 'error') {
            return response()->json($deviceCheckResult, 403);
        }

        // Récupérer ou créer un utilisateur pour cet employé
        $user = $this->getOrCreateUserForEmploye($employe);
        
        // Générer un code OTP
        $otpCode = $this->otpService->generateOtp($user->id);
        
        // Déterminer le numéro de téléphone à utiliser
        $telephone = $user->telephone ?: $employe->telephone;


        
        // Formater le numéro de téléphone (supprimer les espaces et ajouter le préfixe + si nécessaire)
        if ($telephone) {
            // Supprimer tous les caractères non numériques sauf le +
            $telephone = preg_replace('/[^0-9+]/', '', $telephone);
            
            $telephone = '+225' . $telephone;

            // Envoyer le code par SMS
            $this->otpService->sendOtp($telephone, $otpCode);

            $messageSMS = 'Un code de vérification a été envoyé à votre téléphone';

        } else {

            Log::warning("Impossible d'envoyer le code OTP: aucun numéro de téléphone disponible", [
                'user_id' => $user->id,
                'employe_id' => $employe->id
            ]);

            $messageSMS = 'Impossible d\'envoyer le code de vérification: aucun numéro de téléphone disponible';
        }
        
        // Mettre à jour la dernière tentative de connexion
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);
        
        // Enregistrer les informations de l'appareil dans la configuration de l'employé
        $this->saveDeviceInfo($employe, $request->device_model, $request->device_id);
        
        return response()->json([
            'status' => 'success',
            'requires_otp' => true,
            'user_id' => $user->id,
            'employe' => [
                'id' => $employe->id,
                'nom' => $employe->nom,
                'prenom' => $employe->prenom,
                'telephone' => $telephone,
                'code_employe' => $employe->code_employe,
            ],
            'message' => $messageSMS,
        ]);

        // Préparer les données de l'employé à retourner
        $employeData = [
            'id' => $employe->id,
            'nom' => $employe->nom,
            'prenom' => $employe->prenom,
            'email' => $employe->email,
            'telephone' => $employe->telephone,
            'code_employe' => $employe->code_employe,
            'matricule' => $employe->matricule,
            'photo' => $employe->photo,
            'poste' => $employe->poste,
            'entreprise' => [
                'id' => $employe->entreprise->id,
                'nom' => $employe->entreprise->nom,
            ]
        ];

        // Ajouter les informations du département si disponible
        if ($employe->departement) {
            $employeData['departement'] = [
                'id' => $employe->departement->id,
                'nom' => $employe->departement->nom,
            ];
        }

        // Ajouter les informations de la filiale si disponible
        if ($employe->filiale) {
            $employeData['filiale'] = [
                'id' => $employe->filiale->id,
                'nom' => $employe->filiale->nom,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Authentification réussie',
            'token' => $token,
            'employe' => $employeData
        ]);
    }

    /**
     * Déconnecte un employé
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Vérifie un code OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'otp_code' => 'required|string|size:6',
            'device_model' => 'required|string',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        // Vérifier le code OTP
        $verification = $this->otpService->verifyOtp($user->id, $request->otp_code);

        if (!$verification['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => $verification['message'],
            ], 401);
        }

        // Authentification réussie, générer un token
        $token = $user->createToken('mobile-app')->plainTextToken;
        $employe = $user->employeur;

        // Mettre à jour la dernière connexion et les informations de l'appareil
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);
        
        // Enregistrer les informations de l'appareil dans la configuration de l'employé
        $this->saveDeviceInfo($employe, $request->device_model, $request->device_id);

        // Préparer les données de l'employé à retourner
        $employeData = [
            'id' => $employe->id,
            'nom' => $employe->nom,
            'prenom' => $employe->prenom,
            'email' => $employe->email,
            'telephone' => $employe->telephone,
            'code_employe' => $employe->code_employe,
            'matricule' => $employe->matricule,
            'photo' => $employe->photo,
            'poste' => $employe->poste,
            'entreprise' => [
                'id' => $employe->entreprise->id,
                'nom' => $employe->entreprise->nom,
            ]
        ];

        // Ajouter les informations du département si disponible
        if ($employe->departement) {
            $employeData['departement'] = [
                'id' => $employe->departement->id,
                'nom' => $employe->departement->nom,
            ];
        }

        // Ajouter les informations de la filiale si disponible
        if ($employe->filiale) {
            $employeData['filiale'] = [
                'id' => $employe->filiale->id,
                'nom' => $employe->filiale->nom,
            ];
        }

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'employe' => $employeData,
            'message' => 'Authentification réussie',
        ]);
    }

    /**
     * Renvoie un code OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'device_model' => 'nullable|string',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        // Vérifier si un code a été envoyé récemment (limiter les abus)
        if ($this->otpService->isThrottled($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Veuillez attendre avant de demander un nouveau code',
            ], 429);
        }

        // Générer un nouveau code OTP
        $otpCode = $this->otpService->generateOtp($user->id);
        
        // Récupérer le numéro de téléphone à utiliser
        $phoneNumber = $user->telephone;
        if (empty($phoneNumber) && $user->employeur) {
            $phoneNumber = $user->employeur->telephone;
        }
        
        if (empty($phoneNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun numéro de téléphone disponible pour envoyer le code',
            ], 400);
        }
        
        // Formater le numéro de téléphone (supprimer les espaces et ajouter le préfixe + si nécessaire)
        // Supprimer tous les caractères non numériques sauf le +
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // S'assurer que le numéro commence par +
        if (!str_starts_with($phoneNumber, '+')) {
            // Si le numéro commence par 00, remplacer par +
            if (str_starts_with($phoneNumber, '00')) {
                $phoneNumber = '+' . substr($phoneNumber, 2);
            } else {
                // Sinon, ajouter le préfixe +225 (Côte d'Ivoire) par défaut
                if (str_starts_with($phoneNumber, '0')) {
                    $phoneNumber = '+225' . substr($phoneNumber, 1);
                } else {
                    $phoneNumber = '+225' . $phoneNumber;
                }
            }
        }
        
        // Envoyer le code par SMS
        $sent = $this->otpService->sendOtp($phoneNumber, $otpCode);
        
        if (!$sent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de l\'envoi du SMS. Veuillez réessayer plus tard.',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Un nouveau code de vérification a été envoyé',
        ]);
    }
    
    /**
     * Demande un code OTP pour l'authentification par numéro de téléphone
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestOtp(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:8',
            'device_model' => 'nullable|string',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Numéro de téléphone invalide',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Formater le numéro de téléphone
        $phoneNumber = $request->phone_number;
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        $phoneNumber = '+225' . $phoneNumber;

        // Rechercher l'employé par numéro de téléphone
        $employe = Employeur::where('telephone', 'like', '%' . substr($phoneNumber, -9) . '%')
                           ->where('statut', 'actif')
                           ->first();

        if (!$employe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun employé trouvé avec ce numéro de téléphone'
            ], 404);
        }
        
        // Vérifier la compatibilité de l'appareil
        $deviceCheckResult = $this->checkDeviceCompatibility($employe, $request->device_model, $request->device_id);
        if ($deviceCheckResult['status'] === 'error') {
            return response()->json($deviceCheckResult, 403);
        }

        // Récupérer ou créer un utilisateur pour cet employé
        $user = $this->getOrCreateUserForEmploye($employe);
        
        // Vérifier si un code a été envoyé récemment (limiter les abus)
        if ($this->otpService->isThrottled($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Veuillez attendre avant de demander un nouveau code',
            ], 429);
        }
        
        // Générer un code OTP
        $otpCode = $this->otpService->generateOtp($user->id);
        
        // Envoyer le code par SMS
        $sent = $this->otpService->sendOtp($phoneNumber, $otpCode);
        
        if (!$sent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de l\'envoi du SMS. Veuillez réessayer plus tard.',
            ], 500);
        }
        
        // Mettre à jour la dernière tentative de connexion
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);
        
        // Enregistrer les informations de l'appareil dans la configuration de l'employé
        $this->saveDeviceInfo($employe, $request->device_model, $request->device_id);
        
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'employe' => [
                'id' => $employe->id,
                'nom' => $employe->nom,
                'prenom' => $employe->prenom,
                'telephone' => $phoneNumber,
            ],
            'message' => 'Un code de vérification a été envoyé à votre téléphone',
        ]);
    }

    /**
     * Vérifie si le token est valide
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        // Si la requête arrive jusqu'ici, le middleware auth:sanctum a déjà validé le token
        $user = $request->user();
        
        // Vérifier si l'utilisateur est lié à un employeur
        if (!$user->employeur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non associé à un employé'
            ], 401);
        }

        // Vérifier si l'employeur est toujours actif
        $employe = $user->employeur;
        if ($employe->statut !== 'actif') {
            // Révoquer tous les tokens
            $user->tokens()->delete();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Compte employé inactif'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token valide',
            'employe' => [
                'id' => $employe->id,
                'nom' => $employe->nom,
                'prenom' => $employe->prenom
            ]
        ]);
    }

    /**
     * Récupère ou crée un utilisateur pour un employé
     *
     * @param  \App\Models\Employeur  $employe
     * @return \App\Models\User
     */
    protected function getOrCreateUserForEmploye(Employeur $employe)
    {
        // Vérifier si l'employé a déjà un utilisateur associé
        if ($employe->user) {
            return $employe->user;
        }

        // Créer un nouvel utilisateur pour l'employé
        $user = new User();
        $user->name = $employe->nom_complet;
        $user->email = $employe->email ?? $this->generateUniqueEmail($employe);
        $user->password = Hash::make(Str::random(16)); // Mot de passe aléatoire
        $user->role = 'employeur';
        $user->statut = 'actif';
        $user->employeur_id = $employe->id;
        $user->entreprise_id = $employe->entreprise_id;
        $user->save();

        // Assigner le rôle employeur
        $user->assignRole('employeur');

        return $user;
    }

    /**
     * Génère un email unique pour un employé
     *
     * @param  \App\Models\Employeur  $employe
     * @return string
     */
    protected function generateUniqueEmail(Employeur $employe)
    {
        $baseEmail = strtolower(
            preg_replace('/[^a-zA-Z0-9]/', '', $employe->prenom) . 
            '.' . 
            preg_replace('/[^a-zA-Z0-9]/', '', $employe->nom)
        );

        // Ajouter le domaine de l'entreprise si disponible
        $entreprise = $employe->entreprise;
        $domain = $entreprise ? 
            strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $entreprise->nom)) . '.com' : 
            'gwork.com';

        $email = $baseEmail . '@' . $domain;
        $counter = 1;

        // Vérifier si l'email existe déjà
        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }
}
