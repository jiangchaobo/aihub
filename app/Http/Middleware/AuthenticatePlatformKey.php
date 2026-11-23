<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePlatformKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (!$plain) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $key = ApiKey::where('key_hash', hash('sha256', $plain))
            ->whereNull('revoked_at')
            ->first();

        if (!$key || ($key->expires_at && $key->expires_at->isPast())) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $key->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('platform_user', $key->user);

        return $next($request);
    }
}
