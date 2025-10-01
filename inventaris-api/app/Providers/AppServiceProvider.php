<?php

namespace App\Providers;

use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\StockService;
use App\Services\SupplierService;
use App\Services\ReportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StockService::class, function ($app) {
            return new StockService();
        });
        
        $this->app->singleton(SupplierService::class, function ($app) {
            return new SupplierService();
        });
        
        $this->app->singleton(OrderService::class, function ($app) {
            $orderService = new OrderService($app->make(StockService::class));
            $orderService->setSupplierService($app->make(SupplierService::class));
            return $orderService;
        });
        
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService();
        });
        
        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
