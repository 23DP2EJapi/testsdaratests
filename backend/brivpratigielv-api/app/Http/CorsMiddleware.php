<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Determine whether the given origin should receive CORS headers.
     *
     * Allows any *.up.railway.app subdomain (covers all Railway deployments)
     * and any localhost origin (covers local development on any port).
     */
    private function isAllowedOrigin(string $origin): bool
    {
        $host = parse_url($origin, PHP_URL_HOST);

        if (! is_string($host)) {
            return false;
        }

        // Allow any Railway subdomain
        if (str_ends_with($host, '.up.railway.app')) {
            return true;
        }

        // Allow localhost for development
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return true;
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
        $origin = $request->header('Origin');
        $allowOrigin = ($origin && $this->isAllowedOrigin($origin)) ? $origin : null;

        // Handle preflight OPTIONS requests — respond immediately with CORS headers.
        if ($request->isMethod('OPTIONS')) {
            $allowHeaders = $request->header('Access-Control-Request-Headers')
                ?? 'Content-Type, Authorization, X-Requested-With';

            $response = response('', 200);

            if ($allowOrigin !== null) {
                $response
                    ->header('Access-Control-Allow-Origin', $allowOrigin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', $allowHeaders)
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        }

        // Pass the request down the pipeline, then attach CORS headers to the response.
        $response = $next($request);

        if ($allowOrigin !== null) {
            $response
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
