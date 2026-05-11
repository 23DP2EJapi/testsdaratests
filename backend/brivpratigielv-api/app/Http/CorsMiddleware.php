<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    private function originMatchesRailwayHost(string $origin): bool
    {
        $host = parse_url($origin, PHP_URL_HOST);

        return is_string($host) && ($host === 'up.railway.app' || str_ends_with($host, '.up.railway.app'));
    }

    private function normalizeRegexPattern(string $pattern): string
    {
        $pattern = trim($pattern);
        if ($pattern === '') {
            return '';
        }

        $firstChar = $pattern[0];
        $lastChar = substr($pattern, -1);
        $hasDelimiters = !ctype_alnum($firstChar) && $firstChar === $lastChar;

        return $hasDelimiters ? $pattern : '#'.$pattern.'#';
    }

    private function originMatchesPatterns(string $origin, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $normalizedPattern = $this->normalizeRegexPattern($pattern);
            if ($normalizedPattern !== '' && @preg_match($normalizedPattern, $origin) === 1) {
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
        $defaultOrigins = 'https://brivpratigie.up.railway.app,https://frontend-tests-production.up.railway.app,https://testsdaratests.vercel.app,http://localhost:8080,http://localhost:5173';
        $defaultOriginPatterns = '#^https://.*\.up\.railway\.app$#';

        $originsEnv = trim((string) env('CORS_ALLOWED_ORIGINS', $defaultOrigins));
        $patternsEnv = trim((string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''));
        if ($patternsEnv === '') {
            $patternsEnv = $defaultOriginPatterns;
        }

        $allowedOrigins = array_values(array_filter(array_map(
            'trim',
            explode(',', $originsEnv)
        )));
        $allowedOriginPatterns = array_values(array_filter(array_map(
            'trim',
            explode(',', $patternsEnv)
        )));

        $origin = $request->header('Origin');
        $isAllowedByOrigin = $origin && in_array($origin, $allowedOrigins, true);
        $isAllowedByPattern = $origin && $this->originMatchesPatterns($origin, $allowedOriginPatterns);
        $isAllowedRailwayHost = $origin && $this->originMatchesRailwayHost($origin);

        // Check if the request origin is in our allowed origins
        if ($isAllowedByOrigin || $isAllowedByPattern || $isAllowedRailwayHost) {
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
