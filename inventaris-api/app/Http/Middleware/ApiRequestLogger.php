<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log the request
        $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Don't log passwords or sensitive data
        $inputs = $request->except(['password', 'password_confirmation']);
        if (!empty($inputs)) {
            $requestData['inputs'] = $inputs;
        }

        // Log authenticated user if available
        if ($request->user()) {
            $requestData['user_id'] = $request->user()->id;
            $requestData['user_email'] = $request->user()->email;
        }

        Log::info('API Request', $requestData);

        // Process the request
        $response = $next($request);

        // Log the response
        $responseData = [
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('Content-Type'),
            'time_ms' => round((microtime(true) - LARAVEL_START) * 1000, 2),
        ];

        Log::info('API Response', $responseData);

        return $response;
    }
}