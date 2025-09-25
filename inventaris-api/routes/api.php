<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Auth routes (tidak perlu middleware)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('/products-categories', [ProductController::class, 'categories']);
    Route::post('/products-bulk-update', [ProductController::class, 'bulkUpdate']);
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activities', [DashboardController::class, 'recentActivities']);
    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
    
    // Stock Management
    Route::apiResource('stocks', StockController::class)->except(['store', 'destroy']);
    Route::post('/stocks/{stock}/adjust', [StockController::class, 'adjust']);
    Route::get('/stocks-alerts', [StockController::class, 'alerts']);
    Route::get('/stocks-movements', [StockController::class, 'movements']);
    
    // Reports
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/stock', [ReportController::class, 'stockReport']);
    Route::get('/reports/financial', [ReportController::class, 'financialReport']);
    Route::get('/reports/export', [ReportController::class, 'exportReport']);
    Route::post('/reports/custom', [ReportController::class, 'generateCustomReport']);
});