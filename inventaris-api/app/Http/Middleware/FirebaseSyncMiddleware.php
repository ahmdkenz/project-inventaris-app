<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FirebaseSyncMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Proses request
        $response = $next($request);
        
        // Cek jika perlu sinkronisasi ke Firebase
        $shouldSync = $this->shouldSyncToFirebase($request);
        
        if ($shouldSync) {
            // Lakukan sinkronisasi secara asinkron (setelah response selesai)
            $this->dispatchSyncJob($request);
        }
        
        return $response;
    }
    
    /**
     * Tentukan apakah request saat ini perlu disinkronkan ke Firebase
     * 
     * @param Request $request
     * @return bool
     */
    private function shouldSyncToFirebase(Request $request): bool
    {
        // Hanya sinkronkan jika ada perubahan data (POST, PUT, PATCH, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }
        
        // Tentukan endpoint mana yang perlu disinkronkan
        $path = $request->path();
        
        // Daftar endpoint yang perlu disinkronkan
        $syncPaths = [
            'api/products',
            'api/suppliers',
            'api/purchase-orders',
            'api/sales-orders',
            'api/stocks/adjust',
        ];
        
        // Cek jika path ada dalam daftar
        foreach ($syncPaths as $syncPath) {
            if (str_starts_with($path, $syncPath)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Jalankan job sinkronisasi
     * 
     * @param Request $request
     * @return void
     */
    private function dispatchSyncJob(Request $request): void
    {
        try {
            // Tentukan tabel mana yang perlu disinkronkan
            $path = $request->path();
            $table = null;
            
            if (str_contains($path, 'products')) {
                $table = 'products';
            } elseif (str_contains($path, 'suppliers')) {
                $table = 'suppliers';
            } elseif (str_contains($path, 'purchase-orders')) {
                $table = 'purchase_orders';
            } elseif (str_contains($path, 'sales-orders')) {
                $table = 'sales_orders';
            } elseif (str_contains($path, 'stocks')) {
                $table = 'stocks';
                // Juga sinkronkan transactions jika stock berubah
                \Illuminate\Support\Facades\Artisan::queue('app:sync-to-firebase', [
                    '--table' => 'transactions'
                ]);
            }
            
            if ($table) {
                // Dispatch sync job
                \Illuminate\Support\Facades\Artisan::queue('app:sync-to-firebase', [
                    '--table' => $table
                ]);
                Log::info("Firebase sync job dispatched for table: $table");
            }
        } catch (\Exception $e) {
            Log::error("Error dispatching Firebase sync job: " . $e->getMessage());
        }
    }
}