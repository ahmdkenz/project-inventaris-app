<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

Route::apiResource('products', ProductController::class);

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('register', [AuthController::class, 'register']);