<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class TokenAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken(); // Get token from Authorization header

        if (!$token) {
            abort(401); // Return only 401 without JSON message
        }

        // Validate the token
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            abort(401); // Return only 401 without JSON message
        }

        // Authenticate user
        auth()->guard('sanctum')->setUser($accessToken->tokenable);

        return $next($request);
    }
}