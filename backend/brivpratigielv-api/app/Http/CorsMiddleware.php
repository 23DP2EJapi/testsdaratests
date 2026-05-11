<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    private function originMatchesPatterns(string $origin, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern !== '' && @preg_match($pattern, $origin) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'https://frontend-tests-production.up.railway.app,https://testsdaratests.vercel.app,http://localhost:8080,http://localhost:5173'))
        )));
        $allowedOriginPatterns = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', '#^https://.*\.up\.railway\.app$#'))
        )));

        $origin = $request->header('Origin');
        $isAllowedByOrigin = $origin && in_array($origin, $allowedOrigins, true);
        $isAllowedByPattern = $origin && $this->originMatchesPatterns($origin, $allowedOriginPatterns);

        // Check if the request origin is in our allowed origins
        if ($isAllowedByOrigin || $isAllowedByPattern) {
            $allowOrigin = $origin;
        } else {
            $allowOrigin = null;
        }

        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->when($allowOrigin !== null, function ($response) use ($allowOrigin) {
                    return $response
                        ->header('Access-Control-Allow-Origin', $allowOrigin)
                        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                        ->header('Access-Control-Allow-Credentials', 'true');
                });
        }

        // Get the response
        $response = $next($request);

        // Add CORS headers to all responses if origin is allowed
        if ($allowOrigin !== null) {
            $response->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
