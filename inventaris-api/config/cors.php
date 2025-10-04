<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Allow specific frontend origins
    // For development, allow all origins - change in production
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Not using cookies, so set to false
    'supports_credentials' => false,
];