<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable CORS for all routes according to config/cors.php
        $middleware->append(HandleCors::class);

        // Token-based auth via Sanctum personal access tokens; no SPA stateful middleware needed
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
