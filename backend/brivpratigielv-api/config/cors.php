<?php

return [

    'paths' => ['api/*', 'listings*', 'favorites*', 'messages*', 'login', 'logout', 'register', 'user', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'https://frontend-tests-production.up.railway.app,https://testsdaratests.vercel.app,http://localhost:8080,http://localhost:5173'))
    ))),
    'allowed_origins_patterns' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', '#^https://.*\.up\.railway\.app$#'))
    ))),
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];


