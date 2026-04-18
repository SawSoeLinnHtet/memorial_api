<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainKey = $request->header('X-API-Key') ?: $request->bearerToken();

        if (! $plainKey) {
            return response()->json(['message' => 'API key is required.'], Response::HTTP_UNAUTHORIZED);
        }

        $frontendApiKey = config('services.frontend_api_key');

        if ($frontendApiKey && hash_equals($frontendApiKey, $plainKey)) {
            return $next($request);
        }

        $apiKey = ApiKey::query()
            ->where('key_hash', hash('sha256', $plainKey))
            ->first();

        if (! $apiKey) {
            return response()->json(['message' => 'Invalid API key.'], Response::HTTP_UNAUTHORIZED);
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
