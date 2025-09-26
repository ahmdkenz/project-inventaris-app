<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes (tidak perlu middleware)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
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
    Route::post('/stocks/adjust', [StockController::class, 'adjust']);
    Route::get('/stocks/{productId}/history', [StockController::class, 'history']);
    Route::get('/stocks-low-alerts', [StockController::class, 'lowStockAlerts']);
    Route::post('/stocks/bulk-adjust', [StockController::class, 'bulkAdjust']);
    
    // Transactions
    Route::get('/transactions/recent', [App\Http\Controllers\TransactionController::class, 'recent']);
    Route::get('/transactions/product/{productId}', [App\Http\Controllers\TransactionController::class, 'byProduct']);
    
    // Reports
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/stock', [ReportController::class, 'stockReport']);
    Route::get('/reports/financial', [ReportController::class, 'financialReport']);
    Route::get('/reports/export', [ReportController::class, 'exportReport']);
    Route::post('/reports/custom', [ReportController::class, 'generateCustomReport']);
    
    // User Management
    Route::apiResource('users', UserController::class);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
    
    // Settings and System
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index']);
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update']);
    Route::post('/settings/reset', [App\Http\Controllers\SettingController::class, 'reset']);
    Route::get('/system-info', [App\Http\Controllers\SettingController::class, 'systemInfo']);
    Route::post('/system/backup', [App\Http\Controllers\SettingController::class, 'createBackup']);
    Route::get('/system/backup/latest', [App\Http\Controllers\SettingController::class, 'downloadBackup']);
});