<?php

$defaultOrigins = 'http://localhost:4200,http://127.0.0.1:4200';
$fromEnv = array_filter(array_map(
    trim(...),
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', $defaultOrigins))
));
$frontend = rtrim((string) env('FRONTEND_URL', ''), '/');
$knownFrontends = [
    'https://web-two-coral-f9ej5f5eaz.vercel.app',
    'https://web-delgadocovarrubiasluismarios-projects.vercel.app',
];

$allowedOrigins = array_values(array_unique(array_filter([
    ...$fromEnv,
    $frontend !== '' ? $frontend : null,
    ...$knownFrontends,
])));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
