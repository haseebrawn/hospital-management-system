<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
     {
        $token = $request->bearerToken();
        if ($token) {
            $access = PersonalAccessToken::findToken($token);
            if ($access && $access->expires_at && now()->greaterThan($access->expires_at)) {
                // optionally revoke
                $access->delete();
                return response()->json(['message'=>'Token expired.'], 401);
            }
        }
        return $next($request);
    }
}
