<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->bearerToken();

         if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check if the token is valid
        $tokenModel = PersonalAccessToken::findToken($token);

         if (!$tokenModel || ($tokenModel->expires_at && $tokenModel->expires_at->isPast())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
