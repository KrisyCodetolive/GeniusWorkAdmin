<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Foundation\Auth\AuthenticatesUsers;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';
    /**
     * The OTP service instance.
     *
     * @var \App\Services\OtpService
     */
    protected $otpService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\OtpService  $otpService
     * @return void
     */
    public function __construct(OtpService $otpService)
    {
        $this->middleware('guest')->except('logout');
        $this->otpService = $otpService;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('app.auth.login');
    }

    /**
     * Show the phone login form.
     *
     * @return \Illuminate\View\View
     */
    public function showPhoneLoginForm()
    {
        return view('app.auth.phone-login');
    }

    /**
     * Send OTP to the user's phone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendOtp(Request $request)
    {
        Log::info('Sending OTP to phone: ' . $request->input('phone'));
        $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        $phone = $request->input('phone');
        
        // Rechercher l'utilisateur par numéro de téléphone
        $user = \App\Models\User::where('phone', $phone)->first();
        
        if (!$user) {
            Log::warning('Tentative de connexion avec un numéro de téléphone non enregistré: ' . $phone);
            return back()->withErrors(['phone' => 'Aucun compte trouvé avec ce numéro de téléphone.']);
        }
        
        // Store the phone number in session
        Session::put('login_phone_number', $phone);
        Session::put('login_user_id', $user->id);
            
        try {
            // Generate and send OTP using user ID instead of phone number
            $otp = $this->otpService->generateOtp($user->id);
            $this->otpService->sendOtp($phone, $otp);
            
            Log::info('OTP sent to phone: ' . $phone . ' for user: ' . $user->id);
            
            // Rediriger avec les paramètres pour afficher l'étape 2
            return back()->with([
                'status' => 'Code de vérification envoyé !',
                'phone' => $phone,
                'show_otp_step' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'OTP: ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Erreur lors de l\'envoi du code de vérification. Veuillez réessayer.']);
        }
    }

    /**
     * Verify OTP code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyOtp(Request $request)
    {
        Log::info('Verifying OTP: ' . json_encode($request->all()));
        
        $request->validate([
            'otp' => 'required|string|size:6',
            'phone' => 'required|string|max:15',
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $userId = Session::get('login_user_id');

        if (!$userId) {
            Log::error('Session utilisateur perdue lors de la vérification OTP');
            return back()->withErrors(['phone' => 'Session expirée. Veuillez recommencer le processus de connexion.']);
        }

        try {
            $result = $this->otpService->verifyOtp($userId, $otp);
            
            if ($result['valid']) {
                // Récupérer l'utilisateur par son ID
                $user = \App\Models\User::find($userId);

                if (!$user) {
                    Log::warning('Utilisateur introuvable avec ID: ' . $userId);
                    return back()->withErrors(['phone' => 'Compte utilisateur introuvable.']);
                }

                // Vérifier que le numéro de téléphone correspond
                if ($user->phone !== $phone) {
                    Log::warning('Numéro de téléphone ne correspond pas à l\'utilisateur: ' . $userId);
                    return back()->withErrors(['phone' => 'Informations de connexion invalides.']);
                }

                // Login the user
                Auth::login($user, $request->filled('remember'));

                // Mark phone as verified
                Session::put('phone_verified', true);
                
                Log::info('User logged in successfully with OTP: ' . $user->id);
                return redirect()->intended($this->redirectPath());
            }
            
            Log::warning('Invalid OTP provided for phone: ' . $phone);
            return back()->withErrors(['otp' => 'Le code de vérification est invalide.'])->with([
                'phone' => $phone,
                'show_otp_step' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
            return back()->withErrors(['otp' => 'Erreur lors de la vérification du code. Veuillez réessayer.'])->with([
                'phone' => $phone,
                'show_otp_step' => true
            ]);
        }
    }

    /**
     * Login with OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $phone = Session::get('login_phone_number');
        $otp = $request->input('otp');

        if (!$phone) {
            return back()->withErrors(['phone' => 'Numéro de téléphone non trouvé.']);
        }

        $result = $this->otpService->verifyOtp($phone, $otp);
        
        if ($result['valid']) {
            // Find user by phone number
            $user = \App\Models\User::where('phone', $phone)->first();

            if (!$user) {
                return back()->withErrors(['phone' => 'Aucun compte trouvé avec ce numéro de téléphone.']);
            }

            // Login the user
            Auth::login($user, $request->filled('remember'));

            // Mark phone as verified
            Session::put('phone_verified', true);

            return $this->sendLoginResponse($request);
        }

        return back()->withErrors(['otp' => 'The verification code is invalid.']);
    }
}
