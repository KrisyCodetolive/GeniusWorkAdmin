<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PhoneVerification
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
        // Check if the user has a verified phone number
        if (!Session::has('phone_verified') || !Session::get('phone_verified')) {
            return redirect()->route('phone.verification.notice');
        }

        return $next($request);
    }
}
