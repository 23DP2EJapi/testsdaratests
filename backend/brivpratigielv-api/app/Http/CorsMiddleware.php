<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define allowed origins
        $allowedOrigins = [
            'http://localhost:8080',
            'http://localhost:5173',
        ];

        $origin = $request->header('Origin');

        // Check if the request origin is in our allowed origins
        if (in_array($origin, $allowedOrigins)) {
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
