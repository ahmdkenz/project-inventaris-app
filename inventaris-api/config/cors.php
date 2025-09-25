<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Allow specific frontend origins
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Not using cookies, so set to false
    'supports_credentials' => false,
];