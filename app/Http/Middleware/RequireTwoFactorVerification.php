<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
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
