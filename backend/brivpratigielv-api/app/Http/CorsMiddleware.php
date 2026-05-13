<?php

namespace App\Http;
#comentars
use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Check if origin is allowed.
     */
    private function isAllowedOrigin(?string $origin): bool
    {
        if (! $origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (! is_string($host)) {
            return false;
        }

        // Allow all Railway deployments
        if (str_ends_with($host, '.up.railway.app')) {
            return true;
        }

        // Allow local development
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return true;
        }

        return false;
    }

    /**
     * Handle incoming request.
     */
    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('Origin');

        $isAllowed = $this->isAllowedOrigin($origin);

        // Handle preflight request (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $isAllowed ? $origin : '')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'false');
        }

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $response = response()->json(['message' => $e->getMessage()], 500);
        }

        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        return $response;
    }
}