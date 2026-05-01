<?php

return [
    'paths' => ['api/*'],
    'origins' => ['http://localhost:8080', 'http://localhost:5173'],
    'methods' => ['*'],
    'headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
