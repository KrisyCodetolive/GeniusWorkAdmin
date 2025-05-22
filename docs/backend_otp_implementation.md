# Implémentation Backend de l'Authentification OTP

Ce document décrit l'implémentation côté serveur de l'authentification à deux facteurs avec OTP (One-Time Password) pour l'application GENIUS WORK.

## Table des matières

1. [Structure de la base de données](#structure-de-la-base-de-données)
2. [Routes API](#routes-api)
3. [Controllers](#controllers)
4. [Services](#services)
5. [Middleware](#middleware)
6. [Configuration](#configuration)
7. [Tests](#tests)

## Structure de la base de données

### Migration pour la table OTP

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpCodesTable extends Migration
{
    public function up()
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_codes');
    }
}
```

### Modèle OTP

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'verified'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## Routes API

Ajoutez ces routes dans votre fichier `routes/api.php` :

```php
<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification existantes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/verify', [AuthController::class, 'verifyToken'])->middleware('auth:sanctum');

// Nouvelles routes pour l'OTP
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);
```

## Controllers

### Modification du AuthController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $otpService;
    protected $smsService;

    public function __construct(OtpService $otpService, SmsService $smsService)
    {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
    }

    /**
     * Authentifie un utilisateur avec son QR code
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Recherche de l'employé par QR code
        $employe = Employe::where('qr_code', $request->qr_code)->first();

        if (!$employe) {
            return response()->json([
                'status' => 'error',
                'message' => 'QR code invalide ou employé non trouvé',
            ], 401);
        }

        $user = $employe->user;

        // Vérifier si l'authentification à deux facteurs est activée pour cet utilisateur
        if ($user->two_factor_enabled) {
            // Générer un code OTP
            $otpCode = $this->otpService->generateOtp($user->id);
            
            // Envoyer le code par SMS
            $this->smsService->sendOtp($user->telephone, $otpCode);
            
            return response()->json([
                'status' => 'success',
                'requires_otp' => true,
                'user_id' => $user->id,
                'employe' => [
                    'id' => $employe->id,
                    'nom' => $employe->nom,
                    'prenom' => $employe->prenom,
                    'telephone' => $user->telephone,
                    // Autres informations de l'employé nécessaires
                ],
                'message' => 'Un code de vérification a été envoyé à votre téléphone',
            ]);
        }

        // Si l'authentification à deux facteurs n'est pas activée, connecter directement
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'employe' => $employe,
        ]);
    }

    /**
     * Vérifie un code OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'otp_code' => 'required|string|size:6',
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
        $employe = $user->employe;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'employe' => $employe,
            'message' => 'Authentification réussie',
        ]);
    }

    /**
     * Renvoie un code OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
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
        
        // Envoyer le code par SMS
        $this->smsService->sendOtp($user->telephone, $otpCode);

        return response()->json([
            'status' => 'success',
            'message' => 'Un nouveau code de vérification a été envoyé',
        ]);
    }

    // Autres méthodes existantes (logout, verifyToken, etc.)
}
```

## Services

### OtpService

```php
<?php

namespace App\Services;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Génère un code OTP pour un utilisateur
     */
    public function generateOtp(string $userId): string
    {
        // Invalider les codes précédents
        OtpCode::where('user_id', $userId)
            ->where('verified', false)
            ->update(['verified' => true]);

        // Générer un nouveau code à 6 chiffres
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // Définir la durée de validité (10 minutes)
        $expiresAt = Carbon::now()->addMinutes(10);
        
        // Enregistrer le code
        OtpCode::create([
            'user_id' => $userId,
            'code' => $code,
            'expires_at' => $expiresAt,
            'verified' => false,
        ]);

        // Enregistrer le timestamp pour limiter les demandes
        Cache::put("otp_last_sent_{$userId}", Carbon::now()->timestamp, 60);
        
        return $code;
    }

    /**
     * Vérifie un code OTP
     */
    public function verifyOtp(string $userId, string $code): array
    {
        $otpRecord = OtpCode::where('user_id', $userId)
            ->where('code', $code)
            ->where('verified', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return [
                'valid' => false,
                'message' => 'Code OTP invalide',
            ];
        }

        if ($otpRecord->isExpired()) {
            return [
                'valid' => false,
                'message' => 'Code OTP expiré',
            ];
        }

        // Marquer le code comme vérifié
        $otpRecord->update(['verified' => true]);

        return [
            'valid' => true,
            'message' => 'Code OTP valide',
        ];
    }

    /**
     * Vérifie si les demandes de code sont limitées
     */
    public function isThrottled(string $userId): bool
    {
        $lastSent = Cache::get("otp_last_sent_{$userId}");
        
        if (!$lastSent) {
            return false;
        }
        
        // Limiter à une demande par minute
        return (Carbon::now()->timestamp - $lastSent) < 60;
    }
}
```

### SmsService

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $apiKey;
    protected $apiSecret;
    protected $senderName;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiSecret = config('services.sms.api_secret');
        $this->senderName = config('services.sms.sender_name');
        $this->baseUrl = config('services.sms.base_url');
    }

    /**
     * Envoie un code OTP par SMS
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        try {
            // Formater le message
            $message = "Votre code de vérification GENIUS WORK est: {$otpCode}. Il est valable pendant 10 minutes.";
            
            // Appel à l'API SMS (exemple avec une API générique)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/send', [
                'to' => $phoneNumber,
                'from' => $this->senderName,
                'message' => $message,
            ]);
            
            if ($response->successful()) {
                Log::info("SMS envoyé avec succès à {$phoneNumber}");
                return true;
            } else {
                Log::error("Échec de l'envoi du SMS: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi du SMS: " . $e->getMessage());
            return false;
        }
    }
}
```

## Middleware

### Middleware pour l'authentification à deux facteurs

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTwoFactorVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && !$user->two_factor_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentification à deux facteurs requise',
                'requires_otp' => true,
            ], 403);
        }

        return $next($request);
    }
}
```

## Configuration

### Configuration SMS

Ajoutez cette configuration dans votre fichier `config/services.php` :

```php
'sms' => [
    'api_key' => env('SMS_API_KEY'),
    'api_secret' => env('SMS_API_SECRET'),
    'sender_name' => env('SMS_SENDER_NAME', 'GENIUS WORK'),
    'base_url' => env('SMS_API_URL'),
],
```

Puis ajoutez ces variables dans votre fichier `.env` :

```
SMS_API_KEY=votre_api_key
SMS_API_SECRET=votre_api_secret
SMS_SENDER_NAME=GENIUS WORK
SMS_API_URL=https://api.votre-fournisseur-sms.com
```

## Tests

### Test unitaire pour OtpService

```php
<?php

namespace Tests\Unit;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = new OtpService();
    }

    public function test_generate_otp_creates_valid_code()
    {
        $user = User::factory()->create();
        
        $code = $this->otpService->generateOtp($user->id);
        
        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));
        
        $otpRecord = OtpCode::where('user_id', $user->id)->first();
        $this->assertNotNull($otpRecord);
        $this->assertEquals($code, $otpRecord->code);
        $this->assertFalse($otpRecord->verified);
        $this->assertTrue($otpRecord->expires_at->isFuture());
    }

    public function test_verify_otp_validates_correct_code()
    {
        $user = User::factory()->create();
        $code = $this->otpService->generateOtp($user->id);
        
        $result = $this->otpService->verifyOtp($user->id, $code);
        
        $this->assertTrue($result['valid']);
        
        $otpRecord = OtpCode::where('user_id', $user->id)->first();
        $this->assertTrue($otpRecord->verified);
    }

    public function test_verify_otp_rejects_incorrect_code()
    {
        $user = User::factory()->create();
        $this->otpService->generateOtp($user->id);
        
        $result = $this->otpService->verifyOtp($user->id, '000000');
        
        $this->assertFalse($result['valid']);
    }

    public function test_verify_otp_rejects_expired_code()
    {
        $user = User::factory()->create();
        $code = $this->otpService->generateOtp($user->id);
        
        // Modifier la date d'expiration pour la rendre passée
        OtpCode::where('user_id', $user->id)
            ->update(['expires_at' => Carbon::now()->subMinutes(1)]);
        
        $result = $this->otpService->verifyOtp($user->id, $code);
        
        $this->assertFalse($result['valid']);
    }

    public function test_throttling_prevents_frequent_requests()
    {
        $user = User::factory()->create();
        
        // Première demande
        $this->otpService->generateOtp($user->id);
        
        // Vérifier que la limitation est active
        $this->assertTrue($this->otpService->isThrottled($user->id));
        
        // Simuler le passage du temps
        Cache::put("otp_last_sent_{$user->id}", Carbon::now()->subMinutes(2)->timestamp, 60);
        
        // Vérifier que la limitation n'est plus active
        $this->assertFalse($this->otpService->isThrottled($user->id));
    }
}
```

### Test d'intégration pour les routes OTP

```php
<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_otp_required_for_two_factor_enabled_users()
    {
        // Créer un utilisateur avec 2FA activé
        $user = User::factory()->create([
            'two_factor_enabled' => true,
            'telephone' => '+2250101020304',
        ]);
        
        $employe = Employe::factory()->create([
            'user_id' => $user->id,
            'qr_code' => 'test_qr_code',
        ]);

        // Mock le service OTP pour éviter l'envoi réel de SMS
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('generateOtp')->andReturn('123456');
        });

        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'test_qr_code',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'requires_otp' => true,
                'user_id' => $user->id,
            ]);
    }

    public function test_verify_otp_authenticates_user_with_valid_code()
    {
        $user = User::factory()->create();
        
        // Mock le service OTP
        $mockOtpService = $this->mock(OtpService::class);
        $mockOtpService->shouldReceive('verifyOtp')
            ->with($user->id, '123456')
            ->andReturn(['valid' => true, 'message' => 'Code OTP valide']);

        $response = $this->postJson('/api/auth/verify-otp', [
            'user_id' => $user->id,
            'otp_code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'token' => true, // Juste vérifier la présence du token
            ]);
    }

    public function test_verify_otp_rejects_invalid_code()
    {
        $user = User::factory()->create();
        
        // Mock le service OTP
        $mockOtpService = $this->mock(OtpService::class);
        $mockOtpService->shouldReceive('verifyOtp')
            ->with($user->id, '123456')
            ->andReturn(['valid' => false, 'message' => 'Code OTP invalide']);

        $response = $this->postJson('/api/auth/verify-otp', [
            'user_id' => $user->id,
            'otp_code' => '123456',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Code OTP invalide',
            ]);
    }

    public function test_resend_otp_throttles_frequent_requests()
    {
        $user = User::factory()->create();
        
        // Mock le service OTP
        $mockOtpService = $this->mock(OtpService::class);
        $mockOtpService->shouldReceive('isThrottled')
            ->with($user->id)
            ->andReturn(true);

        $response = $this->postJson('/api/auth/resend-otp', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
                'message' => 'Veuillez attendre avant de demander un nouveau code',
            ]);
    }
}
```

## Mise en œuvre

Pour mettre en œuvre cette fonctionnalité, suivez ces étapes :

1. Créez la migration pour la table `otp_codes`
2. Créez le modèle `OtpCode`
3. Ajoutez les nouvelles routes dans `routes/api.php`
4. Créez ou modifiez le contrôleur `AuthController`
5. Créez les services `OtpService` et `SmsService`
6. Ajoutez la configuration SMS dans `config/services.php`
7. Créez les tests unitaires et d'intégration
8. Exécutez les migrations et testez l'API

## Notes importantes

1. **Sécurité** : Assurez-vous que les codes OTP sont stockés de manière sécurisée et qu'ils expirent après un délai raisonnable.
2. **Limitation des demandes** : Implémentez une limitation pour éviter les abus (throttling).
3. **Fournisseur SMS** : Adaptez le `SmsService` à votre fournisseur SMS spécifique.
4. **Tests** : Couvrez tous les scénarios possibles dans vos tests.
5. **Logs** : Enregistrez les événements importants pour faciliter le débogage.

Cette implémentation est compatible avec Laravel 8+ et utilise Sanctum pour l'authentification par token.
