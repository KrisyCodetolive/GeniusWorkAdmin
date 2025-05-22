<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use App\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected $otpService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OtpService $otpService)
    {
        $this->middleware('guest');
        $this->otpService = $otpService;
    }

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app.auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Display the phone registration view.
     *
     * @return \Illuminate\View\View
     */
    public function createWithPhone()
    {
        return view('app.auth.register-phone');
    }

    /**
     * Send OTP for phone registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{1,15}$/', 'unique:users,telephone'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $phone = $request->phone;
        
        // Generate and send OTP
        $otp = $this->otpService->generateOtp($phone);
        $this->otpService->sendOtp($phone, $otp);

        // Store phone and registration data in session
        Session::put('registration_phone', $phone);
        Session::put('registration_data', $request->only(['name']));

        return back()->with('status', __('Un code de vérification a été envoyé à votre numéro de téléphone.'));
    }

    /**
     * Verify OTP and complete phone registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        $phone = Session::get('registration_phone');
        $registrationData = Session::get('registration_data');
        
        if (!$phone || !$registrationData) {
            return redirect()->route('register')->withErrors([
                'phone' => __('Votre session a expiré. Veuillez réessayer.'),
            ]);
        }

        // Verify OTP
        if (!$this->otpService->verifyOtp($phone, $request->otp)) {
            return back()->withErrors([
                'otp' => __('Le code de vérification est invalide ou a expiré.'),
            ]);
        }

        // Create user
        $user = User::create([
            'name' => $registrationData['name'],
            'telephone' => $phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        
        Session::forget(['registration_phone', 'registration_data']);

        return redirect(RouteServiceProvider::HOME);
    }
}
