<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PhoneVerificationController extends Controller
{
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
        $this->middleware('auth');
        $this->otpService = $otpService;
    }

    /**
     * Show the phone verification notice.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('app.auth.verify-phone');
    }

    /**
     * Send a verification code to the user's phone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        $phone = $request->input('phone');
        
        // Store the phone number in session
        Session::put('phone_number', $phone);
        
        // Generate and send OTP
        $otp = $this->otpService->generateOtp($phone);
        $this->otpService->sendOtp($phone, $otp);

        return back()->with('status', 'Verification code sent!');
    }

    /**
     * Verify the phone number with the provided OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $phone = Session::get('phone_number');
        $otp = $request->input('otp');

        if (!$phone) {
            return back()->withErrors(['phone' => 'Phone number not found.']);
        }

        if ($this->otpService->verifyOtp($phone, $otp)) {
            // Mark phone as verified
            Session::put('phone_verified', true);
            
            // Update user's phone number in database if needed
            // $request->user()->update(['phone' => $phone, 'phone_verified_at' => now()]);

            return redirect()->route('dashboard')->with('status', 'Phone verified successfully!');
        }

        return back()->withErrors(['otp' => 'The verification code is invalid.']);
    }
}
