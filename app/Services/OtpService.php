<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * The length of the OTP code.
     *
     * @var int
     */
    protected $length = 6;

    /**
     * The expiration time of the OTP in minutes.
     *
     * @var int
     */
    protected $expiresInMinutes = 10;
    
    /**
     * The SMS service.
     *
     * @var \App\Services\SmsService
     */
    protected $smsService;

    /**
     * Constructor.
     *
     * @param  \App\Services\SmsService  $smsService
     * @return void
     */
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    
    /**
     * Generate a new OTP for the given user ID.
     *
     * @param  string  $userId
     * @return string
     */
    public function generateOtp(string $userId): string
    {
        // Invalider les codes précédents
        OtpCode::where('user_id', $userId)
            ->where('verified', false)
            ->update(['verified' => true]);

        // Generate a random numeric OTP
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // Définir la durée de validité
        $expiresAt = Carbon::now()->addMinutes($this->expiresInMinutes);
        
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
     * Verify if the provided OTP matches the stored one.
     *
     * @param  string  $userId
     * @param  string  $code
     * @return array
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
        
        // Marquer l'utilisateur comme vérifié pour cette session
        $user = User::find($userId);
        if ($user) {
            $user->update(['two_factor_verified' => true]);
        }

        return [
            'valid' => true,
            'message' => 'Code OTP valide',
        ];
    }

    /**
     * Send the OTP to the user.
     *
     * @param  string  $phoneNumber
     * @param  string  $otpCode
     * @return bool
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        // Déléguer l'envoi au service SMS
        return $this->smsService->sendOtp($phoneNumber, $otpCode);
    }

    /**
     * Vérifie si les demandes de code sont limitées
     * 
     * @param  string  $userId
     * @return bool
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
