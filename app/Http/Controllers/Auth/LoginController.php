<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Foundation\Auth\AuthenticatesUsers;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
        $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        $phone = $request->input('phone');
        
        // Store the phone number in session
        Session::put('login_phone_number', $phone);
        
        // Generate and send OTP
        $otp = $this->otpService->generateOtp($phone);
        $this->otpService->sendOtp($phone, $otp);

        return back()->with('status', 'Verification code sent!');
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
            return back()->withErrors(['phone' => 'Phone number not found.']);
        }

        if ($this->otpService->verifyOtp($phone, $otp)) {
            // Find user by phone number
            $user = \App\Models\User::where('phone', $phone)->first();

            if (!$user) {
                return back()->withErrors(['phone' => 'No account found with this phone number.']);
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
