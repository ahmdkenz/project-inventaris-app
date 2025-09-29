<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:3001,127.0.0.1,127.0.0.1:8000',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null,
    
    // Use the custom PersonalAccessToken model
    'model' => App\Models\PersonalAccessToken::class,

    'middleware' => [
        // Middleware yang tidak tersedia di Laravel 11 telah dihapus
    ],
];